<?php
/* ============================================================================
 * Title: cronish
 * postActiv cron-on-visit class
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
 * Uses page visits as a timer for cron jobs.  Most effective when there's
 * sufficient traffic to the site.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

// ----------------------------------------------------------------------------
// Class: Cronish
// Cron-on-visit class
class Cronish
{
    // ------------------------------------------------------------------------
    // Function: callTimedEvents
    // Will call events as close as it gets to one hour. Event handlers
    // which use this MUST be as quick as possible, maybe only adding a
    // queue item to be handled later or something. Otherwise execution
    // will timeout for PHP - or at least cause unnecessary delays for
    // the unlucky user who visits the site exactly at one of these events.
    public function callTimedEvents()
    {
        $timers = array('minutely' => 60,   // this is NOT guaranteed to run every minute (only on busy sites)
                        'hourly' => 3600,
                        'daily'  => 86400,
                        'weekly' => 604800);

        foreach($timers as $name=>$interval) {
            $run = false;

            $lastrun = new Config();
            $lastrun->section = 'cron';
            $lastrun->setting = 'last_' . $name;
            $found = $lastrun->find(true);

            if (!$found) {
                $lastrun->value = time();
                if ($lastrun->insert() === false) {
                    common_log(LOG_WARNING, "Could not save 'cron' setting '{$name}'");
                    continue;
                }
                $run = true;
            } elseif ($lastrun->value < time() - $interval) {
                $orig    = clone($lastrun);
                $lastrun->value = time();
                $lastrun->update($orig);
                $run = true;
            }

            if ($run === true) {
                // such as CronHourly, CronDaily, CronWeekly
                Event::handle('Cron' . ucfirst($name));
            }
        }
    }
}
