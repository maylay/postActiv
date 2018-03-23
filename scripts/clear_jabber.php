#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: Clear_jabber
 * Remove the XMPP address associated with a user
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
 * Remove the XMPP address associated with a user
 *
 *     clear_jabber.php [options]
 *     Deletes a user's confirmed Jabber/XMPP address from the database.
 *
 *     -i --id       ID of the user
 *     -n --nickname nickname of the user
 *     --all      all users with confirmed Jabber addresses
 *     --dry-run  Don't actually delete info.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Brion Vibber <brion@pobox.com>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

require_once INSTALLDIR.'/scripts/commandline.inc';
define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'i::n::y';
$longoptions = array('id=', 'nickname=', 'yes', 'all', 'dry-run');

$helptext = <<<END_OF_DELETEUSER_HELP
clear_jabber.php [options]
Deletes a user's confirmed Jabber/XMPP address from the database.

  -i --id       ID of the user
  -n --nickname nickname of the user
     --all      all users with confirmed Jabber addresses
     --dry-run  Don't actually delete info.

END_OF_DELETEUSER_HELP;


// ----------------------------------------------------------------------------
// Function: clear_jabber
function clear_jabber($id)
{
   $user = User::getKV('id', $id);
   if ($user && $user->jabber) {
      echo "clearing user $id's user.jabber, was: $user->jabber";
      if (have_option('dry-run')) {
         echo " (SKIPPING)";
      } else {
         $original = clone($user);
         $user->jabber = null;
         try {
            $user->updateWithKeys($original);
         } catch (Exception $e) {
            echo "WARNING: user update failed (setting jabber to null): ".$e->getMessage()."\n";
         }
      }
      echo "\n";
   } else if (!$user) {
      echo "Missing user for $id\n";
   } else {
      echo "Cleared jabber already for $id\n";
   }
}


// ----------------------------------------------------------------------------
// Script main procedure follows

if (have_option('i', 'id')) {
   $id = get_option_value('i', 'id');
   $user = User::getKV('id', $id);
   if (empty($user)) {
      print "Can't find user with ID $id\n";
      exit(1);
   }
} else if (have_option('n', 'nickname')) {
   $nickname = get_option_value('n', 'nickname');
   $user = User::getKV('nickname', $nickname);
   if (empty($user)) {
      print "Can't find user with nickname '$nickname'\n";
      exit(1);
   }
} else if (have_option('all')) {
   $user = new User();
   $user->whereAdd("jabber != ''");
   $user->find(true);
   if ($user->N == 0) {
      print "No users with registered Jabber addresses in database.\n";
      exit(1);
   }
} else {
   print "You must provide either an ID or a nickname.\n";
   print "\n";
   print $helptext;
   exit(1);
}

do {
    clear_jabber($user->id);
} while ($user->fetch());

print "DONE.\n";

// END OF FILE
// ============================================================================
?>