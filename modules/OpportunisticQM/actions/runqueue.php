<?php
/* ============================================================================
 * Title: RunQueue
 * Definition for an action to run the queue
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2017, Maiyannah Bishop
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
 * Definition for an action to run the queue
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

if (!defined('GNUSOCIAL')) { exit(1); }

// -------------------------------------------------------------------------
// Class: RunqueueAction
//
// Variables:
// o $qm - QueueManager instance
class RunqueueAction extends Action
{
    protected $qm = null;

    // --------------------------------------------------------------
    // Function: prepare
    // For initializing members of the class.
    //
    // Parameters:
    // o array $args - Arguments array
    //
    // Returns:
    // o boolean true
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $args = array();

        foreach (array('qmkey') as $key) {
            if ($this->arg($key) !== null) {
                $args[$key] = $this->arg($key);
            }
        }

        try {
            $this->qm = new OpportunisticQueueManager($args);
        } catch (RunQueueBadKeyException $e) {
            return false;
        }

        header('Content-type: text/plain; charset=utf-8');

        return true;
    }

    // --------------------------------------------------------------
    // Function: handle
    // Queue handler method
    protected function handle() {
        // We don't need any of the parent functionality from parent::handle() here.

        // runQueue is a loop that works until limits have passed or there is no more work
        if ($this->qm->runQueue() === true) {
            // We don't have any more work
            $this->text('0');
        } else {
            // There were still items left in queue when we aborted
            $this->text('1');
        }
    }
}
