#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: Clean_file_table
 * Deletes all local files where the filename cannot be found in the filesystem
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
 * Deletes all local files where the filename cannot be found in the filesystem.
 *
 *     clean_file_table.php [options]
 *     Deletes all local files where the filename cannot be found in the filesystem.
 *
 *     -y --yes      do not wait for confirmation
 *
 *     Will print '.' for each file, except for deleted ones which are marked as 'x'.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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

$shortoptions = 'y';
$longoptions = array('yes');

$helptext = <<<END_OF_HELP
clean_file_table.php [options]
Deletes all local files where the filename cannot be found in the filesystem.

  -y --yes      do not wait for confirmation

Will print '.' for each file, except for deleted ones which are marked as 'x'.

END_OF_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

if (!have_option('y', 'yes')) {
   print "About to delete local file entries where the file cannot be found. Are you sure? [y/N] ";
   $response = fgets(STDIN);
   if (strtolower(trim($response)) != 'y') {
      print "Aborting.\n";
      exit(0);
   }
}

print "Deleting";
$file = new File();
$file->whereAdd('filename IS NOT NULL');        // local files
$file->whereAdd('filehash IS NULL', 'AND');     // without filehash value
if ($file->find()) {
   while ($file->fetch()) {
      try {
         $file->getPath();
         print '.';
      } catch (FileNotFoundException $e) {
         $file->delete();
         print 'x';
      }
   }
}
print "\nDONE.\n";

// END OF FILE
// ============================================================================
?>