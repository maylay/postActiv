<?php
/* ============================================================================
 * Title: SMS Queue Handler
 * Queue handler for pushing new notices to local subscribers using SMS.
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
 * Queue handler for pushing new notices to local subscribers using SMS.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
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
// Class: SmsQueueHandler
// Queue handler for pushing new notices to local subscribers using SMS.
class SmsQueueHandler extends QueueHandler
{
   // -------------------------------------------------------------------------
   // Function: transport
   //
   // Returns:
   // o string "sms"
   function transport()
   {
      return 'sms';
   }

   // -------------------------------------------------------------------------
   // Function: handle
   // 
   // Parameters:
   // o Notice $notice
   //
   // Returns:
   // o Result of the broadcast (success true/false)
   function handle($notice)
   {
      require_once(INSTALLDIR.'/lib/mail.php');
      return mail_broadcast_notice_sms($notice);
   }
}

// END OF FILE
// ============================================================================
?>