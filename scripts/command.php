#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: Command
 * postActiv command-line processor for testing/development/maintenance purposes
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
 * postActiv command-line processor for testing/development/maintenance purposes
 *
 * Perform commands on behalf of a user, such as sub, unsub, join, drop
 *     usage: php command.php [options] [command line]
 *       -i --id       ID of the user
 *       -n --nickname nickname of the user
 *       -o --owner    use the site owner
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Brion Vibber <brion@pobox.com>
 *  o Evan Prodromou
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

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'i:n:o';
$longoptions = array('id=', 'nickname=', 'owner');

$helptext = <<<END_OF_USERROLE_HELP
command.php [options] [command line]
Perform commands on behalf of a user, such as sub, unsub, join, drop

  -i --id       ID of the user
  -n --nickname nickname of the user
  -o --owner    use the site owner

END_OF_USERROLE_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

function interpretCommand($user, $body)
{
   $inter = new CommandInterpreter();
   $chan = new CLIChannel();
   $cmd = $inter->handle_command($user, $body);
   if ($cmd) {
      $cmd->execute($chan);
      return true;
   } else {
      $chan->error($user, "Not a valid command. Try 'help'?");
      return false;
   }
}

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
} else if (have_option('o', 'owner')) {
   try {
      $user = User::siteOwner();
   } catch (ServerException $e) {
      print "Site has no owner.\n";
      exit(1);
   }
} else {
   print "You must provide either an ID or a nickname.\n\n";
   print $helptext;
   exit(1);
}

// @todo refactor the interactive console in console.php and use
// that to optionally make an interactive test console here too.
// Would be good to help people test commands when XMPP or email
// isn't available locally.
interpretCommand($user, implode(' ', $args));

// END OF FILE
// ============================================================================
?>