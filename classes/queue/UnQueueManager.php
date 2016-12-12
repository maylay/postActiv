<?php
/* ============================================================================
 * Title: Un-Queue Manager
 * A queue manager interface for just doing things immediately
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
 * A queue manager interface for just doing things immediately
 *
 * Dummy queue storage manager: instead of saving events for later,
 * we just process them immediately. This is only suitable for events
 * that can be processed quickly and don't need polling or long-running
 * connections to another server such as XMPP.
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Adrian Lang <mail@adrianlang.de>
 * o Marcel van der Boom <marcel@hsdev.com>
 * o Zach Copley
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
// Class: UnQueueManager
// Class for when we're telling the queue something is handled immediately.
class UnQueueManager extends QueueManager
{
   // -------------------------------------------------------------------------
   // Function: enqueue
   // Dummy queue storage manager: instead of saving events for later,
   // we just process them immediately. This is only suitable for events
   // that can be processed quickly and don't need polling or long-running
   // connections to another server such as XMPP.
   //
   // Parameter:
   // o Notice $object    this specific manager just handles Notice objects anyway
   // o string $queue
   function enqueue($object, $transport) {
      try {
         $handler = $this->getHandler($transport);
         $handler->handle($object);
      } catch (NoQueueHandlerException $e) {
         if (Event::handle('UnqueueHandleNotice', array(&$object, $transport))) {
            throw new ServerException("UnQueueManager: Unknown queue transport: $transport");
         }
      }
   }
}

// END OF FILE
// ============================================================================
?>