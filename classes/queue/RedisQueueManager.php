<?php
/* ============================================================================
 * Title: RedisQueueManager
 * Manager class for the Redis Queue
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016, Maiyannah Bishop
 *
 * Derived from code copyright various sources:
 * o GNU Social (C) 2013-2016, Free Software Foundation, Inc
 * o StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
 * License:
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * <https://www.gnu.org/licenses/agpl.html>
 * ----------------------------------------------------------------------------
 * About:
 * Simple-minded queue manager for storing items in the database
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Neil E. Hodges <47hasbegun@gmail.com>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * File Copyright:
 * o 2016 Neil E. Hodges
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */
 
// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

require_once(INSTALLDIR . '/classes/queue/RedisQueue.php');

// ----------------------------------------------------------------------------
// Class: TransportItem
class TransportItem {
   public $transport;
   public $item;
   function __construct($transport, $item) {
      $this->transport = $transport;
      $this->item = $item;
   }
}

// ----------------------------------------------------------------------------
// Class: RedisQueueManager
class RedisQueueManager extends QueueManager {
   protected $socket_location = null;
   protected $host = null;
   protected $port = null;
   protected $namespace;
   protected $expiration;
   protected $connection = null;

   function __construct() {
      parent::__construct();

      $this->socket_location = common_config('queue', 'redis_socket_location');
      if (!$this->socket_location) {
         $this->host        = common_config('queue', 'redis_host');
         $this->port        = common_config('queue', 'redis_port');
         if (!$this->host || !$this->port)
            throw new UnexpectedValueException('Unable to find any valid servers.');
      }

        $this->namespace       = common_config('queue', 'redis_namespace');
      if (!$this->namespace)
         throw new UnexpectedValueException('Invalid Redis namespace.');

        $this->retries         = intval(common_config('queue', 'redis_retries'));
      if ($this->retries < 1)
         throw new UnexpectedValueException('At least one retry is allowed');

      # Config is in seconds, but Redis wants milliseconds.
        $this->expiration      = intval(common_config('queue', 'redis_expiration')) * 1000;
      if ($this->expiration < 3600000)
         throw new UnexpectedValueException('Expiration must be at least an hour (3600 seconds)');

   }

   function __destruct() {
      $this->_disconnect();
   }

   protected function _connect($force = false) {
      if ($force && $this->connection !== null)
         $this->_disconnect();

      if ($this->connection === null) {
            $this->_log(LOG_INFO, 'Connecting to Redis');
         if ($this->socket_location)
            $this->connection = RedisQueue::fromUnix($this->socket_location, $this->namespace, $this->expiration);
         else
            $this->connection = RedisQueue::fromTCP($this->host, $this->port, $this->namespace, $this->expiration);
      }
   }

   protected function _disconnect() {
      if ($this->connection !== null) {
         $this->_log(LOG_INFO, 'Disconnecting from Redis');
         try {
            $this->connection->close(true);
         } catch (Exception $e) {
            $this->_log(LOG_WARNING, "Failed to disconnect from Redis: {$e->getMessage()}");
         } finally {
            $this->connection = null;
         }
      }
   }

    public function enqueue($object, $queue) {
      # It has been observed that most uses of enqueue() involve destroying the object immediately
      # 
      $item = new TransportItem($queue, $this->encode($object));
        $rep = $this->logrep($object);
        $this->_log(LOG_DEBUG, 'Enqueuing '._ve($rep).' for transport '._ve($queue));
      try {
         $this->_connect();
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to connect to Redis: {$e->getMessage()}; discarding item");
         return false;
      }

      try {
         $this->_log(LOG_DEBUG, "Sending item to Redis");
         $this->connection->put($item);
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to put item to Redis: {$e->getMessage()}; discarding item");
         $this->_disconnect();
         return false;
      }

        $this->stats('enqueued', $queue);
      return true;
    }

   protected function checkSync($processing_id_count) {
      if ($processing_id_count > 0) {
         $this->_log(LOG_DEBUG, "Syncing database");
         try {
            $this->_connect();
         } catch (Exception $e) {
            $this->_log(LOG_ERR, "Failed to connect to Redis: {$e->getMessage()}; sync queue");
            return false;
         }
         try {
            return $this->connection->sync();
         } catch (Exception $e) {
            $this->_log(LOG_ERR, "Failed to sync queue: {$e->getMessage()}");
            $this->_disconnect();
            return false;
         }
      }
   }

    public function pollInterval() {
        return 10;
    }

    public function poll() {
      try {
         $this->_connect();
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to connect to Redis: {$e->getMessage()}; unable to get item");
         return false;
      }

        try {
         $queue_item = $this->connection->get(2, 10);
      } catch (RedisTimeout $e) {
         $this->_log(LOG_DEBUG, "No queue items were available");
         $this->checkSync($e->processing_id_count);
         return true;
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to get item from Redis: {$e->getMessage()}");
         $this->_disconnect();
         return false;
      }

      $transport = $queue_item->item->transport;
        try {
         $item = $this->decode($queue_item->item->item);
      } catch (Exception $e) {
            $this->_log(LOG_WARNING, "[$transport] Discarding bad frame: "._ve($e->getMessage()));
         $this->_done($queue_item, $transport);
         return true;
      }

        $rep = $this->logrep($item);
        $this->_log(LOG_DEBUG, 'Got '._ve($rep).' for transport '._ve($transport));
        try {
            $handler = $this->getHandler($transport);
            $result = $handler->handle($item);

        } catch (NoQueueHandlerException $e) {
         $this->_log(LOG_WARNING, "[$transport:{$rep}] No handler for queue $transport; discarding.");
         return $this->_done($queue_item, $transport);

        } catch (NoResultException $e) {
            $this->_log(LOG_ERR, "[$transport:$rep] ".get_class($e).' thrown ('._ve($e->getMessage()).'), ignoring queue_item '._ve($queue_item->id));
            $result = true;

        } catch (AlreadyFulfilledException $e) {
            $this->_log(LOG_ERR, "[$transport:$rep] ".get_class($e).' thrown ('._ve($e->getMessage()).'), ignoring queue_item '._ve($queue_item->id));
            $result = true;

        } catch (Exception $e) {
            $this->_log(LOG_ERR, "[$transport:$rep] Exception (".get_class($e).') thrown: '._ve($e->getMessage()));
            $result = false;
        }

        if ($result) {
            $this->_log(LOG_INFO, "[$transport:$rep] Successfully handled item");
         $this->_done($queue_item, $transport);

        } else {
            $this->_log(LOG_INFO, "[$transport:$rep] Failed to handle item");
         $this->_fail($queue_item, $transport);
        }
        return true;
    }

    protected function _done($queue_item, $transport) {
      try {
         $this->_connect();
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to connect to Redis: {$e->getMessage()}; unable to mark item complete");
         return false;
      }

      try {
         return $this->connection->markComplete($queue_item->id);
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to mark item done: {$e->getMessage()}");
         $this->_disconnect();
            $this->stats('error', $transport);
         return false;
      }

        $this->stats('handled', $transport);
      return true;
    }

    protected function _fail($queue_item, $transport) {
      try {
         $this->_connect();
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to connect to Redis: {$e->getMessage()}; unable to mark item failed");
         return false;
      }

      try {
         if ($queue_item->tries >= $this->retries) {
            $this->_log(LOG_WARN, "Discarding item $queue_item->id after $queue_item->tries out of allowed $this->retries.");
            return $this->connection->markComplete($queue_item->id);
         } else {
            return $this->connection->markIncomplete($queue_item->id);
         }
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "Failed to mark item failed: {$e->getMessage()}");
         $this->_disconnect();
            $this->stats('error', $transport);
         return false;
      }

        $this->stats('error', $transport);
      return true;
    }
}

// END OF FILE
// ============================================================================
?>
