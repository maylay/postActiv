<?php
/* ============================================================================
 * Title: Opportunistic Queue Manager
 * GNU social queue-manager-on-visit class
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 * GNU social queue-manager-on-visit class
 *
 * Will run events for a certain time, or until finished.
 *
 * Configure remote key if wanted with $config['opportunisticqm']['qmkey'] and
 * use with /main/runqueue?qmkey=abc123
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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

// ----------------------------------------------------------------------
// Class: OpportunisticQueueManager
// Database-based queue that runs on pageload
//
// Defines:
// o MAXEXECTIME - typically just used for the /main/cron action, only used if php.ini max_execution_time is 0
//
// Variables:
// o $qmkey                 - Queue key
// o $max_execution_time    - maximum time in seconds for execution
// o $max_dececution_margin - margin to PHP's max_execution_time
// o $max_queue_items       - maximum number of queue items
// o $started_at            - time execution started
// o $handled_items         - number of items handled
// o $verbosity             - log verbosity level
class OpportunisticQueueManager extends DBQueueManager
{
    protected $qmkey = false;
    protected $max_execution_time = null;
    protected $max_execution_margin = null; 
    protected $max_queue_items = null;

    protected $started_at = null;
    protected $handled_items = 0;

    protected $verbosity = null;

    const MAXEXECTIME = 20; 

    // --------------------------------------------------------------
    // Function: __construct
    // Class constructor
    //
    // Parameters:
    // o array $args - Arguments array
    //
    // Returns:
    // o boolean on success
    public function __construct(array $args=array()) {
        foreach (get_class_vars(get_class($this)) as $key=>$val) {
            if (array_key_exists($key, $args)) {
                $this->$key = $args[$key];
            }
        }
        $this->verifyKey();

        if ($this->started_at === null) {
            $this->started_at = time();
        }

        if ($this->max_execution_time === null) {
            $this->max_execution_time = ini_get('max_execution_time') ?: self::MAXEXECTIME;
        }

        if ($this->max_execution_margin === null) {
            $this->max_execution_margin = common_config('http', 'connect_timeout') + 1;   // think PHP's max exec time, minus this value to have time for timeouts etc.
        }

        return parent::__construct();
    }

    // --------------------------------------------------------------
    // Function: verifyKey
    // Verify that the key matches what was set in the configuration
    //
    // Returns:
    // o boolean true if key matches
    //
    // Error state:
    // Will throw a RunQueueBadKeyException if the key doesn't match
    protected function verifyKey()
    {
        if ($this->qmkey !== common_config('opportunisticqm', 'qmkey')) {
            throw new RunQueueBadKeyException($this->qmkey);
        }
    }

    // --------------------------------------------------------------
    // Function: canContinue
    // Determine whether we can continue processing items in the queue.
    //
    // Returns:
    // o boolean true if we can continue, false if we should wait instead
    public function canContinue()
    {
        $time_passed = time() - $this->started_at;

        // Only continue if limit values are sane
        if ($time_passed <= 0 && (!is_null($this->max_queue_items) && $this->max_queue_items <= 0)) {
            return false;
        }
        // If too much time has passed, stop
        if ($time_passed >= $this->max_execution_time || $time_passed > ini_get('max_execution_time') - $this->max_execution_margin) {
            return false;
        }
        // If we have a max-item-limit, check if it has been passed
        if (!is_null($this->max_queue_items) && $this->handled_items >= $this->max_queue_items) {
            return false;
        }

        return true;
    }

   // -------------------------------------------------------------------------
   // Function: poll
   // Run a polling cycle during idle processing in the input loop.
   //
   // Returns:
   // o boolean true if we should poll again for more data immediately
    public function poll()
    {
        $this->handled_items++;
        if (!parent::poll()) {
            throw new RunQueueOutOfWorkException();
        }
        return true;
    }

    // ------------------------------------------------------------------------
    // Function: noHandlerFound
    // OpportunisticQM shouldn't discard items it can't handle, we're
    // only here to take care of what we _can_ handle!
    //
    // Parameters:
    // o Queue_item $qi - a queue item
    // o mixed $rep
    protected function noHandlerFound(Queue_item $qi, $rep=null) {
        $this->_log(LOG_WARNING, "[{$qi->transport}:item {$qi->id}] Releasing claim for queue item without a handler");
        $this->_fail($qi, true);    // true here means "releaseOnly", so no error statistics since it's not an _error_
    }

    
    // -------------------------------------------------------------------------
    // Function: _fail
    // Free our claimed queue item for later reprocessing in case of
    // temporary failure.
    //
    // Parameters:
    // o QueueItem $qi - a queue item
    // o boolean $releaseOnly - whether to not report an error on failure
    protected function _fail(Queue_item $qi, $releaseOnly=false)
    {
        parent::_fail($qi, $releaseOnly);
        $this->_log(LOG_DEBUG, "[{$qi->transport}:item {$qi->id}] Ignoring this transport for the rest of this execution");
        $this->ignoreTransport($qi->transport);
    }

    // --------------------------------------------------------------
    // Function: runQueue
    // Takes care of running through the queue items, returning when
    // the limits setup in __construct are met.
    //
    // Returns:
    // o true on workqueue finished, false if there are still items in the queue
    //
    public function runQueue()
    {
        while ($this->canContinue()) {
            try {
                $this->poll();
            } catch (RunQueueOutOfWorkException $e) {
                return true;
            }
        }
        if ($this->handled_items > 0) {
            common_debug('Opportunistic queue manager passed execution time/item handling limit without being out of work.');
        } elseif ($this->verbosity > 1) {
            common_debug('Opportunistic queue manager did not have time to start on this action (max: '.$this->max_execution_time.' exceeded: '.abs(time()-$this->started_at).').');
        }
        return false;
    }
}
