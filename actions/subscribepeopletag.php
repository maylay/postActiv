<?php
/* ============================================================================
 * Title: SubscribePeopleTag
 * Subscribe to a peopletag
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
 * Subscribe to a peopletag
 *
 * This is the action for subscribing to a peopletag. It works more or less like the join action
 * for groups.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Shashi Gowda <connect2shashi@gmail.com>
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
 * Subscribe to a peopletag
 */
class SubscribepeopletagAction extends Action
{
    var $peopletag = null;
    var $tagger = null;

    /**
     * Prepare to run
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        if (!common_logged_in()) {
            // TRANS: Client error displayed when trying to perform an action while not logged in.
            $this->clientError(_('You must be logged in to unsubscribe from a list.'));
        }
        // Only allow POST requests

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // TRANS: Client error displayed when trying to use another method than POST.
            $this->clientError(_('This action only accepts POST requests.'));
        }

        // CSRF protection

        $token = $this->trimmed('token');

        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token.'.
                                 ' Try again, please.'));
        }

        $tagger_arg = $this->trimmed('tagger');
        $tag_arg = $this->trimmed('tag');

        $id = intval($this->arg('id'));
        if ($id) {
            $this->peopletag = Profile_list::getKV('id', $id);
        } else {
            // TRANS: Client error displayed when trying to perform an action without providing an ID.
            $this->clientError(_('No ID given.'), 404);
        }

        if (!$this->peopletag || $this->peopletag->private) {
            // TRANS: Client error displayed trying to reference a non-existing list.
            $this->clientError(_('No such list.'), 404);
        }

        $this->tagger = Profile::getKV('id', $this->peopletag->tagger);

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
            Profile_tag_subscription::add($this->peopletag, $cur);
        } catch (Exception $e) {
            // TRANS: Server error displayed subscribing to a list fails.
            // TRANS: %1$s is a user nickname, %2$s is a list, %3$s is the error message (no period).
            $this->serverError(sprintf(_('Could not subscribe user %1$s to list %2$s: %3$s'),
                                       $cur->nickname, $this->peopletag->tag), $e->getMessage());
        }

        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            // TRANS: Title of form to subscribe to a list.
            // TRANS: %1%s is a user nickname, %2$s is a list, %3$s is a tagger nickname.
            $this->element('title', null, sprintf(_('%1$s subscribed to list %2$s by %3$s'),
                                                  $cur->nickname,
                                                  $this->peopletag->tag,
                                                  $this->tagger->nickname));
            $this->elementEnd('head');
            $this->elementStart('body');
            $lf = new UnsubscribePeopletagForm($this, $this->peopletag);
            $lf->show();
            $this->elementEnd('body');
            $this->endHTML();
        } else {
            common_redirect(common_local_url('peopletagsubscribers',
                                array('tagger' => $this->tagger->nickname,
                                      'tag' =>$this->peopletag->tag)),
                            303);
        }
    }
}
?>