#!/usr/bin/env php
<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
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
 * @category  Scripts
 * @package   postActiv
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2015-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   GNU Affero General Public License http://www.gnu.org/licenses/
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));
define('POSTACTIV', true);

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
?>