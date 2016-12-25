<?php
/* ============================================================================
 * Title: Redis Queue
 * Brand shiny new queue handler using Redis
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

// ----------------------------------------------------------------------------
// Class: HostPort
// Contains information about the connection used to connect to Redis if we're
// using a TCP connection.
//
// Variables:
// o host: IP address of host
// o port: port of host we're connecting to
class HostPort {
   public $host;
   public $port;
   function __construct($host, $port) {
      $this->host = $host;
      $this->port = $port;
   }
}


// ----------------------------------------------------------------------------
// Class: RedisLock
// A class to abstract a redis lock
//
// Defines:
// o REDLOCK_UNLOCK - redis script to remove a lock
class RedisLock {
   protected $redis;
   const REDLOCK_UNLOCK = '
      if redis.call("GET", KEYS[1]) == ARGV[1] then
         return redis.call("DEL", KEYS[1])
      else
         return 0
      end';

   // -------------------------------------------------------------------------
   // Function: __construct
   // Class constructor
   //
   // Parameters:
   // o redis - redis object we are a redis lock for
   // o name - a name to assign the lock
   function __construct($redis, $name) {
      $this->redis = $redis;
      $this->name = $name;
      $this->nonce = null;
   }

   // -------------------------------------------------------------------------
   // Function: lock
   // Set a redis lock with given expiration and timeout
   //
   // Parameters:
   // o expiration
   // o timeout (default 1)
   //
   // Error State:
   // o if timeout <= 0 an UnexpectedValueException is raised
   function lock($expiration, $timeout = 1) {
      if ($timeout <= 0)
         throw new UnexpectedValueException('Timeout must be greater than zero.');

      $end_time = time() + $timeout;
      $this->nonce = uniqid();
      while(time() < $end_time) {
         $result = $this->redis->set($this->name, $this->nonce, ['NX', 'PX' => $expiration]);
         if ($result)
            return true;
         usleep(50000); // 0.05 second sleep so it's not a hard spin.
      }
      return false;
   }

   // -------------------------------------------------------------------------
   // Function: unlock
   // Remove a redis lock if one exists
   function unlock() {
      return $this->redis->eval(self::REDLOCK_UNLOCK, [$this->name, $this->nonce], 1);
   }
}


// ----------------------------------------------------------------------------
// Class: RedisTimeout
// Exception for when we time out waiting for Redis
//
// Variables:
// o processing_id_count - what it says on the tin
class RedisTimeout extends Exception {
   public $processing_id_count;

   // -------------------------------------------------------------------------
   // Function: __construct
   // Exception class constructor
   public function __construct($processing_id_count) {
      Exception::__construct("Timed out waiting for Redis.  PIDC: $processing_id_count");
      $this->processing_id_count = $processing_id_count;
   }
}


// ----------------------------------------------------------------------------
// Class: RedisContainer
// Object to contain the queue item being passed to Redis
//
// Variables:
// o item    - thing we're containing
// o created - timestamp of when the item was created
class RedisContainer {
   # Only used for storing the item within Redis itself.
   public $item;
   public $created;
   
   // -------------------------------------------------------------------------
   // Function: __construct
   // Constructor for the class
   //
   // Parameters:
   // item    - thing we're tossing in a container
   // created - time we created the container (if null, sets to now)
   public function __construct($item, $created = null) {
      $this->item = $item;
      if ($created === null)
         $this->created = new DateTimeImmutable('now', new DateTimeZone('UTC'));
      else
         $this->created = $created;
   }
}


// ----------------------------------------------------------------------------
// Class: RedisQueueItem
// Class representation of a queue item as needed for Redis
//
// Variables:
// id                  - uid of the item
// tries               - how many times we've attempted to process the item
// created             - creation timestamp
// item                - actual item the queueitem represents (notice posted, etc)
// processing_id_count - pidc for Redis
class RedisQueueItem {
   public $id;
   public $tries;
   public $created;
   public $item;
   public $processing_id_count;
   
   // -------------------------------------------------------------------------
   // Function: __construct
   // Class constructor
   public function __construct($id, $tries, $created, $item, $processing_id_count = 0) {
      $this->id = $id;
      $this->tries = $tries;
      $this->created = $created;
      $this->item = $item;
      $this->processing_id_count = 0;
   }
   
   // -------------------------------------------------------------------------
   // Function: age
   // How old is this queue item?
   public function age($now = null) {
      if ($now === null)
         $now = new Datetime('now', new DateTimeZone('UTC'));
      return $now->diff($this->created);
   }
}


// ----------------------------------------------------------------------------
// Class: RedisQueue
// Abstraction for the actual Redis queue
//
// Defines:
// o LOCK_TIMEOUT       - 3 seconds
// o LOCK_EXPIRATION    - 1 minute
// o PROCESSING_TIMEOUT - 5 minutes
// o REDIS_SYNC         - Redis script to sync queue items
// 
// Variables:
// o redis - object for the Redis connection
class RedisQueue {
   const LOCK_TIMEOUT = 3; // 3 seconds
   const LOCK_EXPIRATION = 60 * 1000; // 1 minute
   const PROCESSING_TIMEOUT = 300; # 5 minutes
   const REDIS_SYNC = '
      local flush_count = 0
      local to_repush = {}
      while true
      do
         local item_id = redis.call("RPOP", KEYS[1])
         if not item_id then
            break
         end

         if redis.call("SISMEMBER", KEYS[2], item_id) > 0 then
            -- Completed items
            redis.call("SREM", KEYS[2], item_id)
            flush_count = flush_count + 1

         elseif redis.call("EXISTS", ARGV[1] .. "." .. item_id .. ".processing") == 0 then
            -- Items that have been explicitcly marked incomplete
            redis.call("LPUSH", KEYS[4], item_id)

         else
            -- Items still being processed
            table.insert(to_repush, item_id)
         end
      end

      -- Push items that have not been completed back to item_ids
      for key, value in ipairs(to_repush) do
         redis.log(redis.LOG_VERBOSE, "Repushing: " .. value)
         redis.call("LPUSH", KEYS[1], value)
      end

      -- Remove completed item IDs that have been orphaned
      local completed_ids = redis.call("SMEMBERS", KEYS[2])
      for key, item_id in ipairs(completed_ids) do
         if redis.call("EXISTS", ARGV[1] .. "." .. item_id) == 0 then
            redis.log(redis.LOG_VERBOSE, "Removing orphan: " .. item_id)
            redis.call("SREM", KEYS[2], item_id)
         end
      end

      return flush_count';
   protected $redis;

   // -------------------------------------------------------------------------
   // Function: __construct
   // Class constructor
   //
   // Parameters:
   // o address    - location of the Redis we're connecting to, either a string
   //                containing the Unix socket location, or a HostPort object w/
   //                TCP connection information
   // o namespace  - namespace for queue items
   // o expiration - Expiration of the queue items
   //
   // Error State:
   // o Failing to connect to Redis at the given address will raise an exception
   function __construct($address, $namespace, $expiration) {
      $this->namespace = $namespace;
      $this->key_regex = sprintf('/^%s\./', addslashes($namespace));
      $this->redis = new Redis();
      if($address instanceof HostPort) {
         if (!$this->redis->connect($address->host, $address->port))
            throw new RuntimeException("Failed to connect to Redis at $address->host:$address->port.");
      } else {
         if (!$this->redis->connect($address))
            throw new RuntimeException("Failed to connect to Redis at $address");
      }
      $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
      $this->redis->setOption(Redis::OPT_PREFIX, $namespace . '.');
      $this->expiration = $expiration;
      $this->sync();
   }

   // -------------------------------------------------------------------------
   // Function: close
   // Close the Redis connection
   //
   // Parameters:
   // o sync - whether or not to sync the queue before closing the connection
   // (default false)
   public function close($sync = false) {
      if ($this->redis !== null) {
         if ($sync)
            $this->sync();
         $this->redis->close();
         $this->redis = null;
      }
   }

   // -------------------------------------------------------------------------
   // Function: sync
   // Sync the Redis queue using the script
   //
   // Parameters:
   // o disk - also save to disk?  t/f
   //
   // Error State:
   // o will throw an exception if the connection has been closed
   public function sync($disk = false) {
      if ($this->redis === null)
         throw new LogicException("Connection has already been closed");
      $result = $this->redis->eval(self::REDIS_SYNC, ['processing_ids', 'completed_ids', 'incomplete_ids', 'item_ids', $this->namespace], 4);
      $error = $this->redis->getLastError();

      if ($error !== null)
         throw new LogicException(strval($error));

      if ($disk)
         $this->redis->save();
      return ($result > 0);
   }

   // -------------------------------------------------------------------------
   // Function: fromTCP
   // Create a Redis connection and associated queue from a TCP connection
   //
   // Parameters:
   // o host       - URI of the Redis host
   // o port       - TCP port on host to connect to
   // o namespace  - namespace of this queue's items
   // o expiration - expiration of queue items
   public static function fromTCP($host, $port, $namespace, $expiration) {
      $hp = new HostPort($host, $port);
      return new RedisQueue($hp, $namespace, $expiration);
   }

   // -------------------------------------------------------------------------
   // Function: fromUnix
   // Create a Redis connection and associated queue from a Unix socket
   //
   // Parameters:
   // o location   - location of the Unix socket
   // o namespace  - namespace of this queue's items
   // o expiration - expiration of queue items
   public static function fromUnix($location, $namespace, $expiration) {
      return new RedisQueue($location, $namespace, $expiration);
   }

   // -------------------------------------------------------------------------
   // Function: nextIdLock
   // Create a Redis lock
   protected function nextIdLock() {
      return new RedisLock($this->redis, 'next_id_lock');
   }

   // --------------------------------------------------------------------------
   // Function: nextId
   // Find the next queue item's ID (locking it while we process)
   // 
   // Error State:
   // o will throw an exception if the connection has been closed
   protected function nextId() {
      if ($this->redis === null)
         throw new LogicException("Connection has already been closed");

      $lock = $this->nextIdLock();
      if (!$lock->lock(self::LOCK_EXPIRATION, self::LOCK_TIMEOUT))
         throw new RuntimeException("Failed to acquire next_id lock");

      $next_id = null;
      try {
         $next_id = $this->redis->get('next_id');
         if ($next_id === false)
            $next_id = 0;

         $next_id = gmp_init($next_id, 16);
         $next_id = gmp_add($next_id, 1);
         $next_id = gmp_strval($next_id, 16);

         $this->redis->set('next_id', $next_id);
         
      } finally {
         if (!$lock->unlock())
            throw new RuntimeException("Failed to release next_id lock");
      }
      return $next_id;
   }

   // -------------------------------------------------------------------------
   // Function: put
   // Add an item to the queue
   //
   // Parameter:
   // o item - item to be added
   //
   // Error State:
   // o If the function fails to add an item to the queue, it will rais an
   // exception
   public function put($item) {
      $item = serialize(new RedisContainer($item));
      $item_id = $this->nextId();
      $item_id = "items.$item_id";

      $result = $this->redis->multi()
         ->set($item_id, $item)
         ->set("$item_id.tries", 0)
         ->expire($item_id, $this->expiration)
         ->lPush('item_ids', $item_id)
         ->exec();

      if ($result[2] === false)
         throw new RuntimeException('Failed to add item');

      return $item_id;
   }

   // -------------------------------------------------------------------------
   // Function: get
   // Retrieve an item from the queue
   //
   // Parameters:
   // o timeout - how long to wait for retrieval before considering retrieval
   //             to have failed
   // o tries   - how many times have we tried to retrieve this item? (default 0)
   //
   // Error States:
   // o Will throw an exception if the connection has been closed
   // o If processing time exceeds $timeout, it will throw a timeout exception
   public function get($timeout, $tries = 0) {
      if ($this->redis === null)
         throw new LogicException("Connection has already been closed");

      $processing_id_count = 0;

      for($i = 0; ($tries == 0 || $i < $tries); $i++) {
         # Can't use a multi() here because the timeout is ignored.
         $item_id = $this->redis->bRPopLPush('item_ids', 'processing_ids', $timeout);
         $processing_id_count = $this->redis->lLen('processing_ids');

         if(!$item_id)
            break;
         
         $result = $this->redis->multi()
            ->sIsMember('completed_ids', $item_id)
            ->get("$item_id.tries") # We want the value before incrementing
            ->get($item_id)
            ->incr("$item_id.tries")
            ->exec();

         if ($result[0] > 0) {
            # Since this item has been completed, we can just delete it here
            # and let the next sync() call purge it later.
            $this->redis->multi()
               ->delete($item_id)
               ->delete("$item_id.tries")
               ->delete("$item_id.processing")
               ->exec();

         } else if (!$result[2]) {
            # This item has expired, so we can't do anything with it.
            $this->redis->multi()
               ->sAdd('completed_ids', $item_id)
               ->delete($item_id)
               ->delete("$item_id.tries")
               ->delete("$item_id.processing")
               ->exec();

         } else {
            # Got something we can use
            $tries = intval($result[1]);
            $item = unserialize($result[2]);

            $this->redis->set("$item_id.processing", 1, ['EX' => self::PROCESSING_TIMEOUT]);

            return new RedisQueueItem($item_id, $tries, $item->created, $item->item, $processing_id_count);
         }
      }
      throw new RedisTimeout($processing_id_count);
   }

   // -------------------------------------------------------------------------
   // Function: markComplete
   // Mark a queue item as having been completed
   //
   // Parameter:
   // item_id - id of the item to mark
   //
   // Error State:
   // o will throw an exception if the connection has been closed
   public function markComplete($item_id) {
      if ($this->redis === null)
         throw new LogicException("Connection has already been closed");

      $this->redis->multi()
         ->sAdd('completed_ids', $item_id)
         ->delete($item_id)
         ->delete("$item_id.tries")
         ->delete("$item_id.processing")
         ->exec();

      return true;
   }

   // -------------------------------------------------------------------------
   // Function: markIncomplete
   // Mark a queue item as having not been yet completed.
   //
   // Parameter:
   // item_id - id of the item to mark
   //
   // Error State:
   // o will throw an exception if the connection has been closed
   public function markIncomplete($item_id) {
      if ($this->redis === null)
         throw new LogicException("Connection has already been closed");
      $this->redis->delete("$item_id.processing");
      return true;
   }

   // -------------------------------------------------------------------------
   // Function: scrub
   // Cleanup the Redis queue
   //
   // Used for when we're testing the queue, not meant for use in production
   //
   // Error State:
   // o will throw an exception if the connection has been closed
   public function scrub() {
      if ($this->redis === null)
         throw new LogicException("Connection has already been closed");
      $inkeys = $this->redis->keys('*');
      $outkeys = [];
      foreach($inkeys as $inkey)
         array_push($outkeys, preg_replace($this->key_regex, '', $inkey));
      return $this->redis->delete($outkeys);
   }

   // -------------------------------------------------------------------------
   // Function: itemsBeingProcessed
   // Returns how many items are currently being processed.  This isn't a 
   // precise count, but rather a metric used for determining when to call sync()
   //
   // Error State:
   // o will throw an exception if the connection has been closed
   public function itemsBeingProcessed() {
      if ($this->redis === null)
         throw new LogicException("Connection has already been closed");
      return $this->redis->lLen('processing_ids');
   }
}
// END OF FILE
// ============================================================================
?>
