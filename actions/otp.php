<?php
/* ============================================================================
 * Title: OTP
 * Allow one-time password login
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
 * Allow one-time password login
 *
 * This action will automatically log in the user identified by the user_id
 * parameter. A login_token record must be constructed beforehand, typically
 * by code where the user is already authenticated.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Allow one-time password login
 */
class OtpAction extends Action
{
    var $user;
    var $token;
    var $rememberme;
    var $returnto;
    var $lt;

    function prepare(array $args = array())
    {
        parent::prepare($args);

        if (common_is_real_login()) {
            // TRANS: Client error displayed trying to use "one time password login" when already logged in.
            $this->clientError(_('Already logged in.'));
        }

        $id = $this->trimmed('user_id');

        if (empty($id)) {
            // TRANS: Client error displayed trying to use "one time password login" without specifying a user.
            $this->clientError(_('No user ID specified.'));
        }

        $this->user = User::getKV('id', $id);

        if (empty($this->user)) {
            // TRANS: Client error displayed trying to use "one time password login" without using an existing user.
            $this->clientError(_('No such user.'));
        }

        $this->token = $this->trimmed('token');

        if (empty($this->token)) {
            // TRANS: Client error displayed trying to use "one time password login" without specifying a login token.
            $this->clientError(_('No login token specified.'));
        }

        $this->lt = Login_token::getKV('user_id', $id);

        if (empty($this->lt)) {
            // TRANS: Client error displayed trying to use "one time password login" without requesting a login token.
            $this->clientError(_('No login token requested.'));
        }

        if ($this->lt->token != $this->token) {
            // TRANS: Client error displayed trying to use "one time password login" while specifying an invalid login token.
            $this->clientError(_('Invalid login token specified.'));
        }

        if ($this->lt->modified > time() + Login_token::TIMEOUT) {
            //token has expired
            //delete the token as it is useless
            $this->lt->delete();
            $this->lt = null;
            // TRANS: Client error displayed trying to use "one time password login" while specifying an expired login token.
            $this->clientError(_('Login token expired.'));
        }

        $this->rememberme = $this->boolean('rememberme');
        $this->returnto = $this->trimmed('returnto');

        return true;
    }

    function handle()
    {
        parent::handle();

        // success!
        if (!common_set_user($this->user)) {
            // TRANS: Server error displayed when a user object could not be created trying to login using "one time password login".
            $this->serverError(_('Error setting user. You are probably not authorized.'));
        }

        // We're now logged in; disable the lt

        $this->lt->delete();
        $this->lt = null;

        common_real_login(true);

        if ($this->rememberme) {
            common_rememberme($this->user);
        }

        if (!empty($this->returnto)) {
            $url = $this->returnto;
            // We don't have to return to it again
            common_set_returnto(null);
        } else {
            $url = common_local_url('all',
                                    array('nickname' =>
                                          $this->user->nickname));
        }

        common_redirect($url, 303);
    }
}

// END OF FILE
// ============================================================================
?>