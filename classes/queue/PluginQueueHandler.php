<?php
/* ============================================================================
 * Title: Plugin Queue Handler
 * Queue handler for letting plugins handle stuff.
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
 * Queue handler for letting plugins handle stuff.
 *
 * The plugin queue handler accepts notices over the "plugin" queue
 * and simply passes them through the "HandleQueuedNotice" event.
 *
 * This gives plugins a chance to do background processing without
 * actually registering their own queue and ensuring that things
 * are queued into it.
 *
 * Fancier plugins may wish to instead hook the 'GetQueueHandlerClass'
 * event with their own class, in which case they must ensure that
 * their notices get enqueued when they need them.
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com> 
 * o Evan Prodromou
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
// Function: PluginQueueHandler
// Queue handler for letting plugins handle stuff.
//
// The plugin queue handler accepts notices over the "plugin" queue
// and simply passes them through the "HandleQueuedNotice" event.
//
// This gives plugins a chance to do background processing without
// actually registering their own queue and ensuring that things
// are queued into it.
//
// Fancier plugins may wish to instead hook the 'GetQueueHandlerClass'
// event with their own class, in which case they must ensure that
// their notices get enqueued when they need them.
class PluginQueueHandler extends QueueHandler
{
   // -------------------------------------------------------------------------
   // Function: transport
   function transport()
   {
      return 'plugin';
   }

   // -------------------------------------------------------------------------
   // Function: handle
   function handle($notice)
   {
      try {
         Event::handle('HandleQueuedNotice', array(&$notice));
      } catch (NoProfileException $unp) {
         // We can't do anything about this, so just skip
         return true;
      }
      return true;
   }
}

// END OF FILE
// ============================================================================
?>