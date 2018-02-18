<?php
/* ============================================================================
 * Title: IM Queue Manager
 * Simple-minded queue manager for storing items from IMs
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
 * IKM background connection manager for IM-using queue handlers,
 * allowing them to send outgoing messages on the right connection.
 *
 * In a multi-site queuedaemon.php run, one connection will be instantiated
 * for each site being handled by the current process that has IM enabled.
 *
 * Implementations that extend this class will likely want to:
 * 1) override start() with their connection process.
 * 2) override handleInput() with what to do when data is waiting on
 *    one of the sockets
 * 3) override idle($timeout) to do keepalives (if necessary)
 * 4) implement send_raw_message() to send raw data that ImPlugin::enqueueOutgoingRaw
 *      enqueued
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Craig Andrews <candrews@integralblue.com>
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
// Class: ImManager
// IKM background connection manager for IM-using queue handlers,
// allowing them to send outgoing messages on the right connection.
//
// In a multi-site queuedaemon.php run, one connection will be instantiated
// for each site being handled by the current process that has IM enabled.
//
// Implementations that extend this class will likely want to:
// 1) override start() with their connection process.
// 2) override handleInput() with what to do when data is waiting on
//    one of the sockets
// 3) override idle($timeout) to do keepalives (if necessary)
// 4) implement send_raw_message() to send raw data that ImPlugin::enqueueOutgoingRaw
//      enqueued
abstract class ImManager extends IoManager
{
   // -------------------------------------------------------------------------
   // Function: send_raw_message
   abstract function send_raw_message($data);

   // -------------------------------------------------------------------------
   // Function: __construct
   function __construct($imPlugin)
   {
      $this->plugin = $imPlugin;
      $this->plugin->imManager = $this;
   }

   // -------------------------------------------------------------------------
   // Function: get
   // Fetch the singleton manager for the current site.
   // @return mixed ImManager, or false if unneeded
   public static function get() {
      throw new Exception('ImManager should be created using it\'s constructor, not the static get method');
   }
}

// END OF FILE
// ============================================================================
?>