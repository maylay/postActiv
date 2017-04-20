<?php
/* ============================================================================
 * Title: CronishPlugin
 * postActiv cronish plugin, to imitate cron actions
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
 * Plugin that emulates cron actions by performing them on pageload.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('GNUSOCIAL')) { exit(1); }

// ----------------------------------------------------------------------------
// Class: CronishPlugin
// Base class for the Cronish plugin
class CronishPlugin extends Plugin {

    // ------------------------------------------------------------------------
    // Function: onCronMinutely
    // Print to log that a near-minutely cron job is being performed.
    public function onCronMinutely()
    {
        common_debug('CRON: Running near-minutely cron job!');
    }

    // ------------------------------------------------------------------------
    // Function: onCronHourly
    // Print to log that a near-hourly cron job is being performed.
    public function onCronHourly()
    {
        common_debug('CRON: Running near-hourly cron job!');
    }

    // ------------------------------------------------------------------------
    // Function: onCronDaily
    // Print to log that a near-daily cron job is being performed.
    public function onCronDaily()
    {
        common_debug('CRON: Running near-daily cron job!');
    }

    // ------------------------------------------------------------------------
    // Function: onCronWeekly
    // Print to log that a near-weekly cron job is being performed.
    public function onCronWeekly()
    {
        common_debug('CRON: Running near-weekly cron job!');
    }

    // ------------------------------------------------------------------------
    // Function: onEndActionExecute
    // When the page has finished rendering, let's do some cron jobs
    // if we have the time.
    //  
    // Parameters:
    // o Action $action - Action to execute
    //
    // Returns:
    // o bool true
    public function onEndActionExecute(Action $action)
    {
        $cron = new Cronish(); 
        $cron->callTimedEvents();

        return true;
    }

    // ------------------------------------------------------------------------
    // Function: onPluginVersion
    // Modify a versions array to provide the plugin version info.
    //
    // Parameters:
    // o array $versions - versions array to modify
    //
    // Returns:
    // o bool true
    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'Cronish',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'http://www.gnu.org/software/social/',
                            'description' =>
                            // TRANS: Plugin description.
                            _m('Cronish plugin that executes events on a near-minutely/hour/day/week basis.'));
        return true;
    }
}
// END OF FILE
// ============================================================================
