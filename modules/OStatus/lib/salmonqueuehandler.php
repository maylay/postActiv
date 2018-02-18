<?php
/* ============================================================================
 * Title: SalmonQueueHandler
 * Salmon queue handler class
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
 * Salmon queue handler class
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Gina Haeussge <osd@foosel.net>
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Brion Vibber <brion@pobox.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o James Walker <walkah@walkah.net>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

// ============================================================================
// Class: SalmonQueueHandler
// Queue handler to send a Salmon notification in the background.
class SalmonQueueHandler extends QueueHandler {

   // -------------------------------------------------------------------------
   // Function: transpost
   // Function to identify the transport method
   function transport() {
      return 'salmon';
   }


   // -------------------------------------------------------------------------
   // Function: handle
   // Verify the salmon's integrity, make sure the target is not from a banned
   // instance, then send.  Failure information is sent to the log.
   //
   // Returns:
   // o boolean True if successful
   // o boolean False if not
   function handle($data) {
      // Make sure the salmon is valid
      assert(is_array($data));
      assert(is_string($data['salmonuri']));
      assert(is_string($data['entry']));

      // Get the actor and target
      $actor = Profile::getByID($data['actor']);
      $target = Profile::getByID($data['target']);

      try {
         // Make sure the neither actor nor target are on banned instances
         if ($this->isBannedInstance($actor,$target)) {
            common_log(LOG_INFO, "Salmon originating from or destined to a blocked instance, discarding.");
            return false;
         }
      } catch (\TypeError $e) {
         common_log(LOG_INFO, "Unable to find profile for actor or target for banned instance lookup in SalmonQueue." .
            "  PHP said: " . $e . " (This is expected when an incoming salmon's instance is blocked, so we block by default here.)");
         return false;
      }
      // Send the salmon
      Salmon::post($data['salmonuri'], $data['entry'], $actor, $target);

      // @fixme detect failure and attempt to resend
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: isBannedInstance
   // Check if the originator or destination actor is on a banned instance
   function isBannedInstance(Profile $originator, Profile $destination) {
      // Get list of banned instances
		$configphpsettings = common_config('site','sanctions') ?: array();
		foreach($configphpsettings as $configphpsetting=>$value) {
			$settings[$configphpsetting] = $value;
		}
		$bans = $settings['banned_instances'];
      common_debug("Banned instances currently: " . json_encode($bans));

      // Transliterate to the profile URL
      $originator  = $originator->profileurl;
      $destination = $destination->profileurl;

      // Return whether this feed from a banned instance
      $is_banned = false;
      foreach ($bans as $banned_instance) {
         if (strpos($originator, $banned_instance) | strpos($destination, $banned_instance)) {
            $is_banned = true;
            break;
         }
      }
      return $is_banned;
   }
}

// END OF FILE
// ============================================================================
?>