<?php
/* ============================================================================
 * Title: AntiBrutePlugin
 * delay + log multiple fail logins
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
 * Plugin that mitigates brute-force attacks by delaying failed login attempts.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Normandy <kuroe@openmailbox.org>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Bhuvan Krishna <bhuvan@swecha.net>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

// -----------------------------------------------------------------------------
// Class: AntiBrutePlugin
// Main AntiBrute plugin class
class AntiBrutePlugin extends Plugin {
    protected $failed_attempts = 0;
    protected $unauthed_user = null;
    protected $client_ip = null;

    const FAILED_LOGIN_IP_SECTION = 'failed_login_ip';

    // -------------------------------------------------------------------------
    // Function: initialize
    // Initializes the plugin.
    public function initialize()
    {
        // This probably needs some work. For example with IPv6 you can easily generate new IPs...
        $client_ip = common_client_ip();
        $this->client_ip = $client_ip[0] ?: $client_ip[1];   // [0] is proxy, [1] should be the real IP
    }

    // -------------------------------------------------------------------------
    // Function: onStartCheckPassword
    // Delay failed login attemptss after the first attempt for up to 5 seconds.
    //
    // Parameters:
    // o string $nickname - email or nickname of the user
    // o string $password - password entered
    // o User $authenticatedUser - an authenticated user
    //
    // Returns:
    // o bool true to continue processing StartCheckPassword
    public function onStartCheckPassword($nickname, $password, &$authenticatedUser)
    {
        if (common_is_email($nickname)) {
            $this->unauthed_user = User::getKV('email', common_canonical_email($nickname));
        } else {
            $this->unauthed_user = User::getKV('nickname', Nickname::normalize($nickname));
        }

        if (!$this->unauthed_user instanceof User) {
            // Unknown username continue processing StartCheckPassword (maybe uninitialized LDAP user etc?)
            return true;
        }

        $this->failed_attempts = (int)$this->unauthed_user->getPref(self::FAILED_LOGIN_IP_SECTION, $this->client_ip);
        switch (true) {
        case $this->failed_attempts >= 5:
            common_log(LOG_WARNING, sprintf('Multiple failed login attempts for user %s from IP %s - brute force attack?',
                                 $this->unauthed_user->getNickname(), $this->client_ip));
            // 5 seconds is a good max waiting time anyway...
            sleep($this->failed_attempts % 5 + 1);
            break;
        case $this->failed_attempts > 0:
            common_debug(sprintf('Previously failed login on user %s from IP %s - sleeping %u seconds.',
                                 $this->unauthed_user->getNickname(), $this->client_ip, $this->failed_attempts));
            sleep($this->failed_attempts);
            break;
        default:
            // No sleeping if it's our first failed attempt.
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Function: onEndCheckPassword
    // Increment attempt count on login failure, and remove failed logins
    // on successful entry.
    //
    // Parameters:
    // o string $nickname - nickname or email of user
    // o string $password - password entered
    // o User $authenticatedUser - an authenticated user
    //
    // Returns:
    // o bool true
    public function onEndCheckPassword($nickname, $password, $authenticatedUser)
    {
        if ($authenticatedUser instanceof User) {
            // We'll trust this IP for this user and remove failed logins for the database..
            $authenticatedUser->delPref(self::FAILED_LOGIN_IP_SECTION, $this->client_ip);
            return true;
        }

        // See if we have an unauthed user from before. If not, it might be because the User did
        // not exist yet (such as autoregistering with LDAP, OpenID etc.).
        if ($this->unauthed_user instanceof User) {
            // And if the login failed, we'll increment the attempt count.
            common_debug(sprintf('Failed login tests for user %s from IP %s',
                                 $this->unauthed_user->getNickname(), $this->client_ip));
            $this->unauthed_user->setPref(self::FAILED_LOGIN_IP_SECTION, $this->client_ip, ++$this->failed_attempts);
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // Function: onPluginVersion
    // Modify a versions array to provide the plugin information.
    //
    // Parameters:
    // o array $versions - versions array to modify
    //
    // Returns:
    // o bool true
    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'AntiBrute',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'http://gnu.io/',
                            'description' =>
                            // TRANS: Plugin description.
                            _m('Anti bruteforce method(s).'));
        return true;
    }
}
// END OF FILE
// =============================================================================