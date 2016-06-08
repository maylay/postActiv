<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * PHP version 5 
 *
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
 * @category  Actions
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('STATUSNET') && !defined('LACONICA')) { exit(1); }

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
?>