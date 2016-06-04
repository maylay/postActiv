<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Action class to grant user roles.
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
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * Assign role to user.
 */
class GrantRoleAction extends ProfileFormAction
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
            // TRANS: Client error displayed when trying to assign an invalid role to a user.
            $this->clientError(_('Invalid role.'));
        }
        if (!Profile_role::isSettable($this->role)) {
            // TRANS: Client error displayed when trying to assign an reserved role to a user.
            $this->clientError(_('This role is reserved and cannot be set.'));
        }

        $cur = common_current_user();

        assert(!empty($cur)); // checked by parent

        if (!$cur->hasRight(Right::GRANTROLE)) {
            // TRANS: Client error displayed when trying to assign a role to a user while not being allowed to set roles.
            $this->clientError(_('You cannot grant user roles on this site.'));
        }

        assert(!empty($this->profile)); // checked by parent

        if ($this->profile->hasRole($this->role)) {
            // TRANS: Client error displayed when trying to assign a role to a user that already has that role.
            $this->clientError(_('User already has this role.'));
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
        $this->profile->grantRole($this->role);
    }
}
?>