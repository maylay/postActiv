<?php
/* ============================================================================
 * Title: GroupUnblock
 * UNblock a user from a group action class.
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
 * UNblock a user from a group action class.
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
 * Unblock a user from a group
 */
class GroupunblockAction extends Action
{
    var $profile = null;
    var $group = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);
        if (!common_logged_in()) {
            // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
            $this->clientError(_('Not logged in.'));
        }
        $token = $this->trimmed('token');
        if (empty($token) || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token. Try again, please.'));
        }
        $id = $this->trimmed('unblockto');
        if (empty($id)) {
            // TRANS: Client error displayed when trying to unblock a user from a group without providing a profile.
            $this->clientError(_('No profile specified.'));
        }
        $this->profile = Profile::getKV('id', $id);
        if (empty($this->profile)) {
            // TRANS: Client error displayed when trying to unblock a user from a group without providing an existing profile.
            $this->clientError(_('No profile with that ID.'));
        }
        $group_id = $this->trimmed('unblockgroup');
        if (empty($group_id)) {
            // TRANS: Client error displayed when trying to unblock a user from a group without providing a group.
            $this->clientError(_('No group specified.'));
        }
        $this->group = User_group::getKV('id', $group_id);
        if (empty($this->group)) {
            // TRANS: Client error displayed when trying to unblock a user from a non-existing group.
            $this->clientError(_('No such group.'));
        }
        $user = common_current_user();
        if (!$user->isAdmin($this->group)) {
            // TRANS: Client error displayed when trying to unblock a user from a group without being an administrator for the group.
            $this->clientError(_('Only an admin can unblock group members.'), 401);
        }
        if (!Group_block::isBlocked($this->group, $this->profile)) {
            // TRANS: Client error displayed when trying to unblock a non-blocked user from a group.
            $this->clientError(_('User is not blocked from group.'));
        }
        return true;
    }

    /**
     * Handle request
     *
     * @param array $args $_REQUEST args; handled in prepare()
     *
     * @return void
     */
    function handle()
    {
        parent::handle();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->unblockProfile();
        }
    }

    /**
     * Unblock a user.
     *
     * @return void
     */
    function unblockProfile()
    {
        $result = Group_block::unblockProfile($this->group, $this->profile);

        if (!$result) {
            // TRANS: Server error displayed when unblocking a user from a group fails because of an unknown error.
            $this->serverError(_('Error removing the block.'));
        }

        foreach ($this->args as $k => $v) {
            if ($k == 'returnto-action') {
                $action = $v;
            } else if (substr($k, 0, 9) == 'returnto-') {
                $args[substr($k, 9)] = $v;
            }
        }

        if ($action) {
            common_redirect(common_local_url($action, $args), 303);
        } else {
            common_redirect(common_local_url('blockedfromgroup', array('nickname' => $this->group->nickname)), 303);
        }
    }
}

// END OF FILE
// ============================================================================
?>