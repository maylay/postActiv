#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: AllSites
 * List all the sites in a multi-site postActiv install
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
 * List all the sites in a multi-site postActiv install
 *
 *     USAGE: allsites.php [OPTIONS]
 *
 *     -t --tagged=tagname  List only sites with this tag
 *     -w --not-tagged=tagname List only sites without this tag
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */
 
// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

// Abort if called from a web server

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 't:w:';
$longoptions = array('tagged=', 'not-tagged=');

$helptext = <<<ENDOFHELP
allsites.php - list all sites configured for multi-site use
USAGE: allsites.php [OPTIONS]

-t --tagged=tagname  List only sites with this tag
-w --not-tagged=tagname List only sites without this tag

ENDOFHELP;

require_once INSTALLDIR.'/scripts/commandline.inc';


// ----------------------------------------------------------------------------
// Function: print_all_sites
function print_all_sites() {

    $sn = new Status_network();

    if ($sn->find()) {
        while ($sn->fetch()) {
            print "$sn->nickname\n";
        }
    }
    return;
}


// ----------------------------------------------------------------------------
// Function: print_tagged_sites
function print_tagged_sites($tag) {

   $sn = new Status_network();
   $sn->query('select status_network.nickname '.
              'from status_network join status_network_tag '.
              'on status_network.site_id = status_network_tag.site_id '.
              'where status_network_tag.tag = "' . $tag . '"');

   while ($sn->fetch()) {
        print "$sn->nickname\n";
   }
   return;
}


// ----------------------------------------------------------------------------
// Function: print_untagged_sites
function print_untagged_sites($tag) {
   $sn = new Status_network();
   $sn->query('select status_network.nickname '.
              'from status_network '.
              'where not exists '.
              '(select tag from status_network_tag '.
              'where site_id = status_network.site_id '.
              'and tag = "'.$tag.'")');

   while ($sn->fetch()) {
      print "$sn->nickname\n";
   }
   return;
}

if (have_option('t', 'tagged')) {
   $tag = get_option_value('t', 'tagged');
   print_tagged_sites($tag);
} else if (have_option('w', 'not-tagged')) {
   $tag = get_option_value('w', 'not-tagged');
   print_untagged_sites($tag);
} else {
   print_all_sites();
}

// END OF FILE
// ============================================================================
?>