<?php
/* ============================================================================
 * Title: ImSenderQueueHandler
 * Common superclass for all IM sending queue handlers.
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
 * Common superclass for all IM sending queue handlers.
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
// Class: ImSenderQueueHandler
class ImSenderQueueHandler extends QueueHandler
{
   // -------------------------------------------------------------------------
   // Function: __construct
   function __construct($plugin)
   {
      $this->plugin = $plugin;
   }

   // -------------------------------------------------------------------------
   // Function: handle
   // Handle outgoing IM data to be sent from the bot to a user
   //
   // Parameters:
   // o object $data
   //
   // Returns:
   // o boolean success
    function handle($data)
    {
        return $this->plugin->imManager->send_raw_message($data);
    }
}

// END OF FILE
// ============================================================================
?>