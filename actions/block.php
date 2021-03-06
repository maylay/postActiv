<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: Block
 * Block a user action class.
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
 * Block a user action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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
 * Block a user action class.
 */
class BlockAction extends ProfileFormAction
{
    var $profile = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        if (!parent::prepare($args)) {
            return false;
        }

        $cur = common_current_user();

        assert(!empty($cur)); // checked by parent

        if ($cur->hasBlocked($this->profile)) {
            // TRANS: Client error displayed when blocking a user that has already been blocked.
            $this->clientError(_('You already blocked that user.'));
        }

        return true;
    }

    /**
     * Handle request
     *
     * @return void
     */
    function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->arg('no')) {
                $this->returnToPrevious();
            } elseif ($this->arg('yes')) {
                $this->handlePost();
                $this->returnToPrevious();
            } else {
                $this->showPage();
            }
        } else {
            $this->showPage();
        }
    }

    function showContent() {
        $this->areYouSureForm();
    }

    function title() {
        // TRANS: Title for block user page.
        return _('Block user');
    }

    function showNoticeForm() {
        // nop
    }

    /**
     * Confirm with user.
     *
     * Shows a confirmation form.
     *
     * @return void
     */
    function areYouSureForm()
    {
        // @fixme if we ajaxify the confirmation form, skip the preview on ajax hits
        $profile = new ArrayWrapper(array($this->profile));
        $preview = new ProfileList($profile, $this);
        $preview->show();


        $id = $this->profile->id;
        $this->elementStart('form', array('id' => 'block-' . $id,
                                           'method' => 'post',
                                           'class' => 'form_settings form_entity_block',
                                           'action' => common_local_url('block')));
        $this->elementStart('fieldset');
        $this->hidden('token', common_session_token());
        // TRANS: Legend for block user form.
        $this->element('legend', _('Block user'));
        $this->element('p', null,
                       // TRANS: Explanation of consequences when blocking a user on the block user page.
                       _('Are you sure you want to block this user? '.
                         'Afterwards, they will be unsubscribed from you, '.
                         'unable to subscribe to you in the future, and '.
                         'you will not be notified of any @-replies from them.'));
        $this->element('input', array('id' => 'blockto-' . $id,
                                      'name' => 'profileid',
                                      'type' => 'hidden',
                                      'value' => $id));
        foreach ($this->args as $k => $v) {
            if (substr($k, 0, 9) == 'returnto-') {
                $this->hidden($k, $v);
            }
        }
        $this->submit('form_action-no',
                      // TRANS: Button label on the user block form.
                      _m('BUTTON','No'),
                      'submit form_action-primary',
                      'no',
                      // TRANS: Submit button title for 'No' when blocking a user.
                      _('Do not block this user.'));
        $this->submit('form_action-yes',
                      // TRANS: Button label on the user block form.
                      _m('BUTTON','Yes'),
                      'submit form_action-secondary',
                      'yes',
                      // TRANS: Submit button title for 'Yes' when blocking a user.
                      _('Block this user.'));
        $this->elementEnd('fieldset');
        $this->elementEnd('form');
    }

    /**
     * Actually block a user.
     *
     * @return void
     */

    function handlePost()
    {
        $cur = common_current_user();

        if (Event::handle('StartBlockProfile', array($cur, $this->profile))) {
            $result = $cur->block($this->profile);
            if ($result) {
                Event::handle('EndBlockProfile', array($cur, $this->profile));
            }
        }

        if (!$result) {
            // TRANS: Server error displayed when blocking a user fails.
            $this->serverError(_('Failed to save block information.'));
        }
    }

    function showScripts()
    {
        parent::showScripts();
        $this->autofocus('form_action-yes');
    }

    /**
     * Override for form session token checks; on our first hit we're just
     * requesting confirmation, which doesn't need a token. We need to be
     * able to take regular GET requests from email!
     *
     * @throws ClientException if token is bad on POST request or if we have
     *         confirmation parameters which could trigger something.
     */
    function checkSessionToken()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ||
            $this->arg('yes') ||
            $this->arg('no')) {

            return parent::checkSessionToken();
        }
    }

    /**
     * If we reached this form without returnto arguments, return to the
     * current user's subscription list.
     *
     * @return string URL
     */
    function defaultReturnTo()
    {
        $user = common_current_user();
        if ($user) {
            return common_local_url('subscribers',
                                    array('nickname' => $user->nickname));
        } else {
            return common_local_url('public');
        }
    }
}

// END OF FILE
// ============================================================================
?>