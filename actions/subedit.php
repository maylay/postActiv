<?php
/* ============================================================================
 * Title: SubEdit
 * Edit an existing subscription
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
 * Edit an existing subscription
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

// @todo FIXME: Documentation needed.
class SubeditAction extends Action
{
    var $profile = null;

    function prepare(array $args = array())
    {
        parent::prepare($args);

        if (!common_logged_in()) {
            // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
            $this->clientError(_('Not logged in.'));
        }

        $token = $this->trimmed('token');

        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token. '.
                                 'Try again, please.'));
        }

        $id = $this->trimmed('profile');

        if (!$id) {
            // TRANS: Client error displayed trying a change a subscription without providing a profile.
            $this->clientError(_('No profile specified.'));
        }

        $this->profile = Profile::getKV('id', $id);

        if (!$this->profile) {
            // TRANS: Client error displayed trying a change a subscription for a non-existant profile ID.
            $this->clientError(_('No profile with that ID.'));
        }

        return true;
    }

    function handle()
    {
        parent::handle();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cur = common_current_user();

            $sub = Subscription::pkeyGet(array('subscriber' => $cur->id,
                                               'subscribed' => $this->profile->id));

            if (!$sub) {
                // TRANS: Client error displayed trying a change a subscription for a non-subscribed profile.
                $this->clientError(_('You are not subscribed to that profile.'));
            }

            $orig = clone($sub);

            $sub->jabber = $this->boolean('jabber');
            $sub->sms = $this->boolean('sms');

            $result = $sub->update($orig);

            if (!$result) {
                common_log_db_error($sub, 'UPDATE', __FILE__);
                // TRANS: Server error displayed when updating a subscription fails with a database error.
                $this->serverError(_('Could not save subscription.'));
            }

            common_redirect(common_local_url('subscriptions', array('nickname' => $cur->nickname)), 303);
        }
    }
}

// END OF FILE
// ============================================================================
?>