<?php
/* ============================================================================
 * Title: AtomPubShowMembership
 * Show a single membership as an Activity Streams entry
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
 * Show a single membership as an Activity Streams entry
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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
 * Show (or delete) a single membership event as an ActivityStreams entry
 */
class AtompubshowmembershipAction extends AtompubAction
{
    private $_private    = null;
    private $_group      = null;
    private $_membership = null;

    protected function atompubPrepare()
    {
        $this->_profile = Profile::getKV('id', $this->trimmed('profile'));

        if (!$this->_profile instanceof Profile) {
            // TRANS: Client exception.
            throw new ClientException(_('No such profile.'), 404);
        }

        $this->_group = User_group::getKV('id', $this->trimmed('group'));

        if (!$this->_group instanceof User_group) {
            // TRANS: Client exception thrown when referencing a non-existing group.
            throw new ClientException(_('No such group.'), 404);
        }

        $kv = array('group_id' => $groupId,
                    'profile_id' => $this->_profile->id);

        $this->_membership = Group_member::pkeyGet($kv);

        if (!$this->_membership instanceof Group_member) {
            // TRANS: Client exception thrown when trying to show membership of a non-subscribed group
            throw new ClientException(_('Not a member.'), 404);
        }

        return true;
    }

    protected function handleGet() {
        return $this->showMembership();
    }

    protected function handleDelete() {
        return $this->deleteMembership();
    }

    /**
     * show a single membership
     *
     * @return void
     */
    function showMembership()
    {
        $activity = $this->_membership->asActivity();

        header('Content-Type: application/atom+xml; charset=utf-8');

        $this->startXML();
        $this->raw($activity->asString(true, true, true));
        $this->endXML();

        return;
    }

    /**
     * Delete the membership (leave the group)
     *
     * @return void
     */
    function deleteMembership()
    {
        if (empty($this->auth_user) ||
            $this->auth_user->id != $this->_profile->id) {
            // TRANS: Client exception thrown when deleting someone else's membership.
            throw new ClientException(_("Cannot delete someone else's".
                                        " membership."), 403);
        }

        $this->auth_user->leaveGroup($this->_group);

        return;
    }

    /**
     * Return last modified, if applicable.
     *
     * Because the representation depends on the profile and group,
     * our last modified value is the maximum of their mod time
     * with the actual membership's mod time.
     *
     * @return string last modified http header
     */
    function lastModified()
    {
        return max(strtotime($this->_profile->modified),
                   strtotime($this->_group->modified),
                   strtotime($this->_membership->modified));
    }

    /**
     * Return etag, if applicable.
     *
     * A "weak" Etag including the profile and group id as well as
     * the admin flag and ctime of the membership.
     *
     * @return string etag http header
     */
    function etag()
    {
        $ctime = strtotime($this->_membership->created);

        $adminflag = ($this->_membership->is_admin) ? 't' : 'f';

        return 'W/"' . implode(':', array('AtomPubShowMembership',
                                          $this->_profile->id,
                                          $this->_group->id,
                                          $adminflag,
                                          $ctime)) . '"';
    }
}

// END OF FILE
// ============================================================================
?>