<?php
/* ============================================================================
 * Title: LogFilterPlugin
 * Plugin that allows log messages to be filtered by severity.
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
 * This plugin will allow you to filter different severities of log message or
 * that match certain PCRE tests from the log file.
 *
 * Example to disable all debug messages and those containing 'About to push':
 * addPlugin('LogFilter', array(
 *    'priority' => array(LOG_DEBUG => false),
 *    'regex' => array('/About to push/' => false)
 * ));
 *
 * PHP version:
 * Tested with 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Brion Vibber <brion@pobox.com>
 *  o Siebrand Mazeland <s.mazeland@xs4all.nl>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o chimo <chimo@chromic.org>
 *  o Maiyannah Bishop <maiynnah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

// ----------------------------------------------------------------------------
// Class: LogFilterPlugin
// Main plugin class for LogFilter
//
// Variables:
// o $default - Set to false to require opting things in
// o $priority - override by priority: array(LOG_ERR => true, LOG_DEBUG => false)
// o $regex -  override by regex match of message: array('/twitter/i' => false)
//
// TODO: add an admin panel
class LogFilterPlugin extends Plugin
{
    public $default = true;     // Set to false to require opting things in
    public $priority = array(); // override by priority: array(LOG_ERR => true, LOG_DEBUG => false)
    public $regex = array();    // override by regex match of message: array('/twitter/i' => false)

    // ------------------------------------------------------------------------
    // Function: onPluginVersion
    // Modify a versions array to provide the plugin version info.
    //
    // Parameters:
    // o array $versions - versions array to modify
    //
    // Returns:
    // o boolean true
    function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'LogFilter',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Brion Vibber',
                            'homepage' => 'https://git.gnu.io/gnu/gnu-social/tree/master/plugins/LogFilter',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Provides server-side setting to filter log output by type or keyword.'));

        return true;
    }

    // ------------------------------------------------------------------------
    // Function: onStartLog
    // Hook for the StartLog event in common_log().
    // If a message doesn't pass our filters, we'll abort it.
    //
    // Parameters:
    // o string $priority - log message priority
    // o string $msg - the log message itself
    // o string $filename - location of log file
    //
    // Returns:
    // o boolean hook result code
    function onStartLog(&$priority, &$msg, &$filename)
    {
        if ($this->filter($priority, $msg)) {
            // Let it through
            return true;
        } else {
            // Abort -- this line will go to /dev/null :)
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Function: filter
    // Do the filtering...
    //
    // Paramters:
    // o string $priority - log message priority
    // o string $msg - the log message itself
    //
    // Returns:
    // o boolean true to let the log message be processed
    function filter($priority, $msg)
    {
        $state = $this->default;
        if (array_key_exists($priority, $this->priority)) {
            $state = $this->priority[$priority];
        }
        foreach ($this->regex as $regex => $override) {
            if (preg_match($regex, $msg)) {
                $state = $override;
            }
        }
        return $state;
    }
}

// END OF FILE
// =============================================================================
