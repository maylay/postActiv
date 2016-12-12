<?php
/* ============================================================================
 * Title: QueueHandler
 * Base class for queue handlers.
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
 * Base class for queue handlers.
 *
 * As of 0.9, queue handlers are short-lived for items as they are
 * dequeued by a QueueManager running in an IoMaster in a daemon
 * such as queuedaemon.php.
 *
 * Extensions requiring long-running maintenance or polling should
 * register an IoManager.
 *
 * Subclasses must override at least the following methods:
 * - transport
 * - handle
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

// ----------------------------------------------------------------------------
// Function: QueueHandler
// Base class for queue handlers.
//
// As of 0.9, queue handlers are short-lived for items as they are
// dequeued by a QueueManager running in an IoMaster in a daemon
// such as queuedaemon.php.
//
// Extensions requiring long-running maintenance or polling should
// register an IoManager.
//
// Subclasses must override at least the following methods:
// - transport
// - handle
class QueueHandler
{
   // -------------------------------------------------------------------------
   // Function: handle
   // Here's the meat of your queue handler -- you're handed a Notice
   // or other object, which you may do as you will with.
   //
   // If this function indicates failure, a warning will be logged
   // and the item is placed back in the queue to be re-run.
   //
   // Parameters:
   // o mixed $object
   //
   // Returns:
   // o boolean true on success, false on failure
    function handle($object)
    {
        return true;
    }
}
?>