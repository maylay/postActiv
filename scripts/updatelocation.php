#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: UpdateLocation
 * Set the location for a profile
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
 * Set the location for a profile
 *
 *     usage: php updatelocation.php [options]
 *     -i --id       ID of user to update
 *     -n --nickname nickname of the user to update
 *     -f --force    force update even if user already has a location
 *     -a --all      update all
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Bhuvan Krishna <bhuvan@swecha.net>
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */
 
// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'i:n:af';
$longoptions = array('id=', 'nickname=', 'all', 'force');

$helptext = <<<END_OF_UPDATELOCATION_HELP
updatelocation.php [options]
set the location for a profile

  -i --id       ID of user to update
  -n --nickname nickname of the user to update
  -f --force    force update even if user already has a location
  -a --all      update all

END_OF_UPDATELOCATION_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

try {
   $user = null;

   if (have_option('i', 'id')) {
      $id = get_option_value('i', 'id');
      $user = User::getKV('id', $id);
      if (empty($user)) {
         throw new Exception("Can't find user with id '$id'.");
      }
      updateLocation($user);
   } else if (have_option('n', 'nickname')) {
      $nickname = get_option_value('n', 'nickname');
      $user = User::getKV('nickname', $nickname);
      if (empty($user)) {
         throw new Exception("Can't find user with nickname '$nickname'");
      }
      updateLocation($user);
   } else if (have_option('a', 'all')) {
      $user = new User();
      if ($user->find()) {
         while ($user->fetch()) {
            updateLocation($user);
         }
      }
   } else {
      show_help();
      exit(1);
   }
} catch (Exception $e) {
   print $e->getMessage()."\n";
   exit(1);
}

function updateLocation($user) {
    $profile = $user->getProfile();

   if (empty($profile)) {
      throw new Exception("User has no profile: " . $user->nickname);
   }
   if (empty($profile->location)) {
      if (have_option('v', 'verbose')) {
         print "No location string for '".$user->nickname."'\n";
      }
      return;
   }
   if (!empty($profile->location_id) && !have_option('f', 'force')) {
      if (have_option('v', 'verbose')) {
         print "Location ID already set for '".$user->nickname."'\n";
      }
      return;
   }

   $loc = Location::fromName($profile->location);

   if (empty($loc)) {
      if (have_option('v', 'verbose')) {
            print "No structured location for string '".$profile->location."' for user '".$user->nickname."'\n";
      }
      return;
   } else {
      $orig = clone($profile);
      $profile->lat         = $loc->lat;
      $profile->lon         = $loc->lon;
      $profile->location_id = $loc->location_id;
      $profile->location_ns = $loc->location_ns;
      $result = $profile->update($orig);

      if (!$result) {
         common_log_db_error($profile, 'UPDATE', __FILE__);
      }

      if (!have_option('q', 'quiet')) {
         print "Location ID " . $profile->location_id . " set for user " . $user->nickname . "\n";
      }
   }

   $profile->free();
   unset($loc);
   unset($profile);
   return;
}

// END OF FILE
// ============================================================================
?>
