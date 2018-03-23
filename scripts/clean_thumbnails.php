#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: Clean_thumbnails
 * Delete thumbnails to allow regeneration
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
 * Delete thumbnails to allow regeneration
 *
 *     clean_thumbnails.php [options]
 *     Deletes all local thumbnails so they can be regenerated. Also deletes
 *     if the original File object does not exist, even for remote entries.
 *
 *     -y --yes      do not wait for confirmation
 *
 *     Will print '.' for deleted local files and 'x' where File entry was
 *     missing. If the script seems to stop, it is processing correct
 *     File_thumbnail entries.
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
clean_thumbnails.php [options]
Deletes all local thumbnails so they can be regenerated. Also deletes
if the original File object does not exist, even for remote entries.

  -y --yes      do not wait for confirmation

Will print '.' for deleted local files and 'x' where File entry was missing.
If the script seems to stop, it is processing correct File_thumbnail entries.

END_OF_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

if (!have_option('y', 'yes')) {
   print "About to delete locally generated thumbnails to allow regeneration. Are you sure? [y/N] ";
   $response = fgets(STDIN);
   if (strtolower(trim($response)) != 'y') {
      print "Aborting.\n";
      exit(0);
   }
}

print "Deleting";
$thumbs = new File_thumbnail();
$thumbs->find();
while ($thumbs->fetch()) {
   try {
      $file = $thumbs->getFile();
      if ($file->isLocal()) {
         // only delete properly linked thumbnails if they're local
         $thumbs->delete();
         print '.';
      }
   } catch (NoResultException $e) {
      // No File object for thumbnail, let's delete the thumbnail entry
      $thumbs->delete();
      print 'x';
   }
}
print "\nDONE.\n";

// END OF FILE
// ============================================================================
?>