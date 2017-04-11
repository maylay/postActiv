<?php
/* ============================================================================
 * Title: GrantRole
 * Action class to grant user roles.
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
 * Action class to grant user roles.
 *
 * PHP version:
 * Tested with PHP 7.0
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

// END OF FILE
// ============================================================================
?>