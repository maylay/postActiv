<?php
/* ============================================================================
 * Title: Nudge
 * Nudge a user action class.
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
 * Nudge a user action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Sarven Capadisli <sarven@capadisli.name>
 * o Robin Millette <robin@millette.name>
 * o Evan Prodromou <evan@status.net>
 * o Eric Helgeson <erichelgeson@gmail.com>
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

require_once INSTALLDIR.'/lib/mail.php';

/**
 * Nudge a user action class.
 */
class NudgeAction extends Action
{
     /**
     * Class handler.
     *
     * @return nothing
     */
    function handle()
    {
        parent::handle();

        if (!common_logged_in()) {
            // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
            $this->clientError(_('Not logged in.'));
        }

        $user  = common_current_user();
        $other = User::getKV('nickname', $this->arg('nickname'));

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            common_redirect(common_local_url('showstream',
                array('nickname' => $other->nickname)));
        }

        // CSRF protection
        $token = $this->trimmed('token');

        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token. Try again, please.'));
        }

        if (!$other->email || !$other->emailnotifynudge) {
            // TRANS: Client error displayed trying to nudge a user that cannot be nudged.
            $this->clientError(_('This user doesn\'t allow nudges or hasn\'t confirmed or set their email address yet.'));
        }

        $this->notify($user, $other);

        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            // TRANS: Page title after sending a nudge.
            $this->element('title', null, _('Nudge sent'));
            $this->elementEnd('head');
            $this->elementStart('body');
            // TRANS: Confirmation text after sending a nudge.
            $this->element('p', array('id' => 'nudge_response'), _('Nudge sent!'));
            $this->elementEnd('body');
            $this->endHTML();
        } else {
            // display a confirmation to the user
            common_redirect(common_local_url('showstream',
                                             array('nickname' => $other->nickname)),
                            303);
        }
    }

     /**
     * Do the actual notification
     *
     * @param class $user  nudger
     * @param class $other nudgee
     *
     * @return nothing
     */
    function notify($user, $other)
    {
        if ($other->id != $user->id) {
            if ($other->email && $other->emailnotifynudge) {
                mail_notify_nudge($user, $other);
            }
            // XXX: notify by IM
            // XXX: notify by SMS
        }
    }

    function isReadOnly($args)
    {
        return true;
    }
}

// END OF FILE
// ============================================================================
?>