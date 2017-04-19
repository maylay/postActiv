<?php
/* ============================================================================
 * Title: OpportunisticQMPlugin
 * Plugin that execute queue actions upon page load
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
 * GNU social queue-manager-on-visit class
 *
 * Will run events for a certain time, or until finished.
 *
 * PHP version:
 * Tested with PHP 7 
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

// ----------------------------------------------------------------------------
// Class: OpportunisticQMPlugin
// Main OpportunisitcQM plugin class
//
// Variables:
// o $qmkey           - Queue key
// o $secs_per_action - total seconds to run script per action
// o $rel_to_pageload - relative to pageload or queue start
// o $verbosity       - log verbosity level
class OpportunisticQMPlugin extends Plugin {
    public $qmkey = false;
    public $secs_per_action = 1;    
    public $rel_to_pageload = true;  
    public $verbosity = 1;

    // ------------------------------------------------------------
    // Function: onRouterInitialized
    // Define routes for particular functions.
    //
    // Parameter:
    // o URLMapper $m - a URLMapper instance
    //
    // Returns:
    // o boolean hook value
    public function onRouterInitialized($m)
    {
        $m->connect('main/runqueue', array('action' => 'runqueue'));
    }

    // --------------------------------------------------------------
    // Function: onEndActionExecute
    // When the page has finished rendering, let's do some cron jobs
    // if we have the time.
    //
    // Parameter:
    // o Action $action - action to execute
    //
    // Returns:
    // o boolean hook value
    public function onEndActionExecute(Action $action)
    {
        if ($action instanceof RunqueueAction) {
            return true;
        }

        global $_startTime;

        $args = array(
                    'qmkey' => common_config('opportunisticqm', 'qmkey'),
                    'max_execution_time' => $this->secs_per_action,
                    'started_at'      => $this->rel_to_pageload ? $_startTime : null,
                    'verbosity'          => $this->verbosity,
                );
        $qm = new OpportunisticQueueManager($args); 
        $qm->runQueue();
        return true;
    }

    // -----------------------------------------------------------
    // Function: onPluginVersion
    // Modify a versions array to contain the version info of
    // the plugin.
    //
    // Parameter:
    // o array $versions - an array to contain the version info
    //
    // Returns:
    // o boolean hook value
    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'OpportunisticQM',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'http://www.gnu.org/software/social/',
                            'description' =>
                            // TRANS: Plugin description.
                            _m('Opportunistic queue manager plugin for background processing.'));
        return true;
    }
}
