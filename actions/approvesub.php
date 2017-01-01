<?php
/* ============================================================================
 * Title: ApproveSub
 * Approve group subscription request
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
 * Approve group subscription request
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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
 * Leave a group
 *
 * This is the action for leaving a group. It works more or less like the subscribe action
 * for users.
 */
class ApprovesubAction extends Action
{
    var $profile = null;

    /**
     * Prepare to run
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        $cur = common_current_user();
        if (empty($cur)) {
            // TRANS: Client error displayed trying to approve group membership while not logged in.
            $this->clientError(_('Must be logged in.'), 403);
        }
        if ($this->arg('profile_id')) {
            $this->profile = Profile::getKV('id', $this->arg('profile_id'));
        } else {
            // TRANS: Client error displayed trying to approve subscriptionswithout specifying a profile to approve.
            $this->clientError(_('Must specify a profile.'));
        }

        $this->request = Subscription_queue::pkeyGet(array('subscriber' => $this->profile->id,
                                                           'subscribed' => $cur->id));

        if (empty($this->request)) {
            // TRANS: Client error displayed trying to approve subscription for a non-existing request.
            // TRANS: %s is a user nickname.
            $this->clientError(sprintf(_('%s is not in the moderation queue for your subscriptions.'), $this->profile->nickname), 403);
        }

        $this->approve = (bool)$this->arg('approve');
        $this->cancel = (bool)$this->arg('cancel');
        if (!$this->approve && !$this->cancel) {
            // TRANS: Client error displayed trying to approve/deny subscription.
            $this->clientError(_('Internal error: received neither cancel nor abort.'));
        }
        if ($this->approve && $this->cancel) {
            // TRANS: Client error displayed trying to approve/deny  subscription
            $this->clientError(_('Internal error: received both cancel and abort.'));
        }
        return true;
    }

    /**
     * Handle the request
     *
     * On POST, add the current user to the group
     *
     * @return void
     */
    function handle()
    {
        parent::handle();
        $cur = common_current_user();

        try {
            if ($this->approve) {
                $this->request->complete();
            } elseif ($this->cancel) {
                $this->request->abort();
            }
        } catch (Exception $e) {
            common_log(LOG_ERR, "Exception canceling sub: " . $e->getMessage());
            // TRANS: Server error displayed when cancelling a queued subscription request fails.
            // TRANS: %1$s is the leaving user's nickname, $2$s is the nickname for which the leave failed.
            $this->serverError(sprintf(_('Could not cancel or approve request for user %1$s to join group %2$s.'),
                                       $this->profile->nickname, $cur->nickname));
            return;
        }

        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            // TRANS: Title for subscription approval ajax return
            // TRANS: %1$s is the approved user's nickname
            $this->element('title', null, sprintf(_m('TITLE','%1$s\'s request'),
                                                  $this->profile->nickname));
            $this->elementEnd('head');
            $this->elementStart('body');
            if ($this->approve) {
                // TRANS: Message on page for user after approving a subscription request.
                $this->element('p', 'success', _('Subscription approved.'));
            } elseif ($this->cancel) {
                // TRANS: Message on page for user after rejecting a subscription request.
                $this->element('p', 'success', _('Subscription canceled.'));
            }
            $this->elementEnd('body');
            $this->endHTML();
        } else {
            common_redirect(common_local_url('subqueue', array('nickname' =>
                                                               $cur->nickname)),
                            303);
        }
    }
}

// END OF FILE
// ============================================================================
?>