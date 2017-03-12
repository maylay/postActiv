<?php
/* ============================================================================
 * Title: Subscribe
 * Subscription action.
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
 * Subscription action.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Zach Copley
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Robin Millette <robin@millette.info>
 * o Sarven Capadisli
 * o Brion Vibber <brion@pobox.com>
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
 * Subscription action
 *
 * Subscribing to a profile. Likely to work for OStatus profiles.
 *
 * Takes parameters:
 *
 *    - subscribeto: a profile ID
 *    - token: session token to prevent CSRF attacks
 *    - ajax: boolean; whether to return Ajax or full-browser results
 *
 * Only works if the current user is logged in.
 */
class SubscribeAction extends Action
{
    var $user;
    var $other;

    /**
     * Check pre-requisites and instantiate attributes
     *
     * @param Array $args array of arguments (URL, GET, POST)
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        // Only allow POST requests

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // TRANS: Client error displayed trying to perform any request method other than POST.
            // TRANS: Do not translate POST.
            $this->clientError(_('This action only accepts POST requests.'));
        }

        // CSRF protection

        $token = $this->trimmed('token');

        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token is not okay.
            $this->clientError(_('There was a problem with your session token.'.
                                 ' Try again, please.'));
        }

        // Only for logged-in users

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
            $this->clientError(_('Not logged in.'));
        }

        // Profile to subscribe to

        $other_id = $this->arg('subscribeto');

        $this->other = Profile::getKV('id', $other_id);

        if (empty($this->other)) {
            // TRANS: Client error displayed trying to subscribe to a non-existing profile.
            $this->clientError(_('No such profile.'));
        }

        return true;
    }

    /**
     * Handle request
     *
     * Does the subscription and returns results.
     *
     * @return void
     */
    function handle()
    {
        // Throws exception on error

        $sub = Subscription::ensureStart($this->user->getProfile(),
                                   $this->other);

        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            // TRANS: Page title when subscription succeeded.
            $this->element('title', null, _('Subscribed'));
            $this->elementEnd('head');
            $this->elementStart('body');
            if ($sub instanceof Subscription) {
                $form = new UnsubscribeForm($this, $this->other);
            } else {
                $form = new CancelSubscriptionForm($this, $this->other);
            }
            $form->show();
            $this->elementEnd('body');
            $this->endHTML();
        } else {
            $url = common_local_url('subscriptions',
                                    array('nickname' => $this->user->nickname));
            common_redirect($url, 303);
        }
    }
}
?>