<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Action class to sandbox an abusive user
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Sandbox a user.
 */
class RevokeRoleAction extends ProfileFormAction
{
    /**
     * Check parameters
     *
     * @param array $args action arguments (URL, GET, POST)
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        if (!parent::prepare($args)) {
            return false;
        }

        $this->role = $this->arg('role');
        if (!Profile_role::isValid($this->role)) {
            // TRANS: Client error displayed when trying to revoke an invalid role.
            $this->clientError(_('Invalid role.'));
        }
        if (!Profile_role::isSettable($this->role)) {
            // TRANS: Client error displayed when trying to revoke a reserved role.
            $this->clientError(_('This role is reserved and cannot be set.'));
        }

        $cur = common_current_user();

        assert(!empty($cur)); // checked by parent

        if (!$cur->hasRight(Right::REVOKEROLE)) {
            // TRANS: Client error displayed when trying to revoke a role without having the right to do that.
            $this->clientError(_('You cannot revoke user roles on this site.'));
        }

        assert(!empty($this->profile)); // checked by parent

        if (!$this->profile->hasRole($this->role)) {
            // TRANS: Client error displayed when trying to revoke a role that is not set.
            $this->clientError(_('User does not have this role.'));
        }

        return true;
    }

    /**
     * Sandbox a user.
     *
     * @return void
     */
    function handlePost()
    {
        $this->profile->revokeRole($this->role);
    }
}
?>