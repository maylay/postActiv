#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: BackupUser
 * Export a postActiv user history to a file
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
 * Export a postActiv user history to a file
 *
 *     exportactivitystream.php [options]
 *
 *     -i --id       ID of user to export
 *     -n --nickname nickname of the user to export
 *     -j --json     Output JSON (default Atom)
 *     -a --after    Only activities after the given date
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Brion Vibber <brion@pobox.com>
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

$shortoptions = 'i:n:f:a:j';
$longoptions = array('id=', 'nickname=', 'file=', 'after=', 'json');

$helptext = <<<END_OF_EXPORTACTIVITYSTREAM_HELP
exportactivitystream.php [options]
Export a postActiv user history to a file

  -i --id       ID of user to export
  -n --nickname nickname of the user to export
  -j --json     Output JSON (default Atom)
  -a --after    Only activities after the given date

END_OF_EXPORTACTIVITYSTREAM_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

try {
    $user = getUser();
    if (have_option('a', 'after')) {
        $afterStr = get_option_value('a', 'after');
        $after = strtotime($afterStr);
        $actstr = new UserActivityStream($user, true, UserActivityStream::OUTPUT_RAW, $after);
    } else {
        $actstr = new UserActivityStream($user, true, UserActivityStream::OUTPUT_RAW);
    }
    if (have_option('j', 'json')) {
        $actstr->writeJSON(STDOUT);
    } else {
        print $actstr->getString();
    }
} catch (Exception $e) {
    print $e->getMessage()."\n";
    exit(1);
}

// END OF FILE
// ============================================================================
?>