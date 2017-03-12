<?php
/* ============================================================================
 * Title: APIGroupLeave
 * Leave a group via the API
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
 * Leave a group via the API
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Michele Azzolari <macno@macno.org>
 * o Brion Vibber <brion@pobox.com>
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
 * Removes the authenticated user from the group specified by ID
 */
class ApiGroupLeaveAction extends ApiAuthAction
{
    protected $needPost = true;

    var $group   = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $this->group = $this->getTargetGroup($this->arg('id'));

        return true;
    }

    /**
     * Handle the request
     *
     * Save the new message
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (!$this->scoped instanceof Profile) {
            // TRANS: Client error displayed when trying to have a non-existing user leave a group.
            $this->clientError(_('No such user.'), 404);
        }

        if (!$this->group instanceof User_group) {
            // TRANS: Client error displayed when trying to leave a group that does not exist.
            $this->clientError(_('Group not found.'), 404);
        }

        $member = new Group_member();

        $member->group_id   = $this->group->id;
        $member->profile_id = $this->scoped->id;

        if (!$member->find(true)) {
            // TRANS: Server error displayed when trying to leave a group the user is not a member of.
            $this->serverError(_('You are not a member of this group.'));
        }

        try {
            $this->user->leaveGroup($this->group);
        } catch (Exception $e) {
            // TRANS: Server error displayed when leaving a group failed in the database.
            // TRANS: %1$s is the leaving user's nickname, $2$s is the group nickname for which the leave failed.
            $this->serverError(sprintf(_('Could not remove user %1$s from group %2$s.'),
                                       $this->scoped->getNickname(), $this->group->nickname));
        }
        switch($this->format) {
        case 'xml':
            $this->showSingleXmlGroup($this->group);
            break;
        case 'json':
            $this->showSingleJsonGroup($this->group);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }
}

// END OF FILE
// ============================================================================
?>