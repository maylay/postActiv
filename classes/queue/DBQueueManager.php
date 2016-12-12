<?php
/* ============================================================================
 * Title: Database Queue Manager
 * Simple-minded queue manager for storing items in the database
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
 * o Evan Prodromou
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Brion Vibber <brion@pobox.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }


// ----------------------------------------------------------------------------
// Class: DBQueueManager
class DBQueueManager extends QueueManager
{
   // -------------------------------------------------------------------------
   // Function: enqueue
   // Saves an object reference into the queue item table.
   //
   // @return boolean true on success
   // @throws ServerException on failure
   public function enqueue($object, $queue) {
      $qi = new Queue_item();

      $qi->frame     = $this->encode($object);
      $qi->transport = $queue;
      $qi->created   = common_sql_now();
      $result        = $qi->insert();

      if ($result === false) {
         common_log_db_error($qi, 'INSERT', __FILE__);
         throw new ServerException('DB error inserting queue item');
      }

      $this->stats('enqueued', $queue);

      return true;
   }


   // -------------------------------------------------------------------------
   // Return: pollInterval
   // Poll every 10 seconds for new events during idle periods.
   // We'll look in more often when there's data available.
   //
   // Returns:
   // o int seconds
   public function pollInterval() {
      return 10;
   }


   // -------------------------------------------------------------------------
   // Function: poll
   // Run a polling cycle during idle processing in the input loop.
   // @return boolean true if we should poll again for more data immediately
   public function poll() {
      //$this->_log(LOG_DEBUG, 'Checking for notices...');
      $qi = Queue_item::top($this->activeQueues(), $this->getIgnoredTransports());
      if (!$qi instanceof Queue_item) {
         //$this->_log(LOG_DEBUG, 'No notices waiting; idling.');
         return false;
      }
      try {
         $item = $this->decode($qi->frame);
      } catch (Exception $e) {
         $this->_log(LOG_INFO, "[{$qi->transport}] Discarding: "._ve($e->getMessage()));
         $this->_done($qi);
         return true;
      }
      $rep = $this->logrep($item);
      $this->_log(LOG_DEBUG, 'Got '._ve($rep).' for transport '._ve($qi->transport));
      try {
         $handler = $this->getHandler($qi->transport);
         $result = $handler->handle($item);
      } catch (NoQueueHandlerException $e) {
         $this->noHandlerFound($qi, $rep);
         return true;
      } catch (NoResultException $e) {
         $this->_log(LOG_ERR, "[{$qi->transport}:$rep] ".get_class($e).' thrown ('._ve($e->getMessage()).'), ignoring queue_item '._ve($qi->getID()));
         $result = true;
      } catch (AlreadyFulfilledException $e) {
         $this->_log(LOG_ERR, "[{$qi->transport}:$rep] ".get_class($e).' thrown ('._ve($e->getMessage()).'), ignoring queue_item '._ve($qi->getID()));
         $result = true;
      } catch (Exception $e) {
         $this->_log(LOG_ERR, "[{$qi->transport}:$rep] Exception (".get_class($e).') thrown: '._ve($e->getMessage()));
         $result = false;
      }
      if ($result) {
         $this->_log(LOG_INFO, "[{$qi->transport}:$rep] Successfully handled item");
         $this->_done($qi);
      } else {
         $this->_log(LOG_INFO, "[{$qi->transport}:$rep] Failed to handle item");
         $this->_fail($qi);
      }
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: noHandlerFound
   // What to do if no handler was found. For example, the OpportunisticQM
   // should avoid deleting items just because it can't reach XMPP queues etc.
   protected function noHandlerFound(Queue_item $qi, $rep=null) {
      $this->_log(LOG_INFO, "[{$qi->transport}:{$rep}] No handler for queue {$qi->transport}; discarding.");
      $this->_done($qi);
   }


   // --------------------------------------------------------------------------
   // Function: _done
   // Delete our claimed item from the queue after successful processing.
   //
   // @param QueueItem $qi
   protected function _done(Queue_item $qi) {
      if (empty($qi->claimed)) {
         $this->_log(LOG_WARNING, "Reluctantly releasing unclaimed queue item {$qi->id} from {$qi->transport}");
      }
      $qi->delete();
      $this->stats('handled', $qi->transport);
   }


   // -------------------------------------------------------------------------
   // Function: _fail
   // Free our claimed queue item for later reprocessing in case of
   // temporary failure.
   //
   // @param QueueItem $qi
   protected function _fail(Queue_item $qi, $releaseOnly=false) {
      if (empty($qi->claimed)) {
         $this->_log(LOG_WARNING, "[{$qi->transport}:item {$qi->id}] Ignoring failure for unclaimed queue item");
      } else {
         $qi->releaseClaim();
      }
      if (!$releaseOnly) {
         $this->stats('error', $qi->transport);
      }
   }
}

// END OF FILE
// ============================================================================
?>