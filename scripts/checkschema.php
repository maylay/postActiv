#!/usr/bin/env php
<?php
/* ============================================================================
 * Title: CheckSchema
 * Perform a database integrity check
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
 * Performs a database integrity check
 *
 *     php checkschema.php [options]
 *     Gives plugins a chance to update the database schema.
 *
 *     -x --extensions=     Comma-separated list of plugins to load before checking
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Craig Andrews <candrews@integralblue.com>
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

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'x::';
$longoptions = array('extensions=');

$helptext = <<<END_OF_CHECKSCHEMA_HELP
php checkschema.php [options]
Gives plugins a chance to update the database schema.

    -x --extensions=     Comma-separated list of plugins to load before checking


END_OF_CHECKSCHEMA_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';


// ----------------------------------------------------------------------------
// Function tableDefs
function tableDefs()
{
	$schema = array();
	require INSTALLDIR.'/db/core.php';
	return $schema;
}


// ----------------------------------------------------------------------------
// Main script entry point

$schema = Schema::get();
$schemaUpdater = new SchemaUpdater($schema);
foreach (tableDefs() as $table => $def) {
	$schemaUpdater->register($table, $def);
}
$schemaUpdater->checkSchema();

if (have_option('x', 'extensions')) {
    $ext = trim(get_option_value('x', 'extensions'));
    $exts = explode(',', $ext);
    foreach ($exts as $plugin) {
        try {
            addPlugin($plugin);
        } catch (Exception $e) {
            print $e->getMessage()."\n";
            exit(1);
        }
    }
}

try {
	Event::handle('BeforePluginCheckSchema');
} catch (Exception $e) {
	print 'Caught exception during BeforePluginCheckSchema: ' . $e->getMessage() . "\n";
}
Event::handle('CheckSchema');

// END OF FILE
// ============================================================================
?>
