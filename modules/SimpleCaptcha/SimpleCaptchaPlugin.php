<?php
/* ============================================================================
 * Title: SimpleCaptchaPlugin
 * Plugin that implements a rudimentary captcha interface
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
 * Plugin that implements a rudimentary captcha interface
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Normandy <kuroe@openmailbox.org>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('GNUSOCIAL')) { exit(1); }

class SimpleCaptchaPlugin extends Plugin
{
    // ------------------------------------------------------------------------
    // Function: initialize
    // Initializes the plugin.
    public function initialize()
    {
        // This probably needs some work. For example with IPv6 you can easily generate new IPs...
        $client_ip = common_client_ip();
        $this->client_ip = $client_ip[0] ?: $client_ip[1];   // [0] is proxy, [1] should be the real IP
    }

    // ------------------------------------------------------------------------
    // Function: onEndRegistrationFormData
    // Display the capcha form on the registration page.
    //
    // Parameters:
    // o Action $action - form action
    //
    // Returns:
    // o bool true on success
    public function onEndRegistrationFormData(Action $action)
    {
        $action->elementStart('li');
        // TRANS: Field label.
        $action->input('simplecaptcha', _m('Captcha'), null,
                        // TRANS: The instruction box for our simple captcha plugin
                        sprintf(_m('Copy this to the textbox: "%s"'), $this->getCaptchaText()),
                        // TRANS: Placeholder in the text box
                        /* name=id */ null, /* required */ true, ['placeholder'=>_m('Prove that you are sentient.')]);
        $action->elementEnd('li');
        return true;
    }

    // ------------------------------------------------------------------------
    // Function: getCaptchaText
    // Use the site's name as the captcha text.
    //
    // Returns:
    // o string containing the site's name
    protected function getCaptchaText()
    {
        return common_config('site', 'name');
    }

    // ------------------------------------------------------------------------
    // Function: onStartRegistrationTry
    // If entered text doesn't match the captcha text, record it in the log.
    //
    // Parameters:
    // o Action $action - form action
    //
    // Returns:
    // o bool true
    public function onStartRegistrationTry(Action $action)
    {
        if ($action->arg('simplecaptcha') !== $this->getCaptchaText()) {
            common_log(LOG_INFO, 'Stopped non-sentient registration of nickname '._ve($action->trimmed('nickname')).' from IP: '._ve($this->client_ip));
            throw new ClientException(_m('Captcha does not match!'));
        }
        return true;
    }

    // ------------------------------------------------------------------------
    // Function: onPluginVersion
    // Modify a versions array to contain the version info of
    // the plugin.
    //
    // Parameters:
    // o array $versions - an array to contain the version info
    //
    // Returns:
    // o boolean hook value
    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'Simple Captcha',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'https://gnu.io/social',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('A simple captcha to get rid of spambots.'));

        return true;
    }
}
// END OF FILE
// ============================================================================
