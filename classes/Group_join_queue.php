<?php
/* ============================================================================
 * Title: Group_join_queue
 * Table abstraction for group_join_queue
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
 * Table abstraction for group_join_queue
 *
 * PHP version:
 * Tested with PHP 7
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

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Group_join_queue extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'group_join_queue';       // table name
    public $profile_id;
    public $group_id;
    public $created;

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'description' => 'Holder for group join requests awaiting moderation.',
            'fields' => array(
                'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'remote or local profile making the request'),
                'group_id' => array('type' => 'int', 'description' => 'remote or local group to join, if any'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            ),
            'primary key' => array('profile_id', 'group_id'),
            'indexes' => array(
                'group_join_queue_profile_id_created_idx' => array('profile_id', 'created'),
                'group_join_queue_group_id_created_idx' => array('group_id', 'created'),
            ),
            'foreign keys' => array(
                'group_join_queue_profile_id_fkey' => array('profile', array('profile_id' => 'id')),
                'group_join_queue_group_id_fkey' => array('user_group', array('group_id' => 'id')),
            )
        );
    }

    public static function saveNew(Profile $profile, User_group $group)
    {
        $rq = new Group_join_queue();
        $rq->profile_id = $profile->id;
        $rq->group_id = $group->id;
        $rq->created = common_sql_now();
        $rq->insert();
        return $rq;
    }

    function getMember()
    {
        $member = Profile::getKV('id', $this->profile_id);

        if (empty($member)) {
            // TRANS: Exception thrown providing an invalid profile ID.
            // TRANS: %s is the invalid profile ID.
            throw new Exception(sprintf(_('Profile ID %s is invalid.'),$this->profile_id));
        }

        return $member;
    }

    function getGroup()
    {
        $group  = User_group::getKV('id', $this->group_id);

        if (empty($group)) {
            // TRANS: Exception thrown providing an invalid group ID.
            // TRANS: %s is the invalid group ID.
            throw new Exception(sprintf(_('Group ID %s is invalid.'),$this->group_id));
        }

        return $group;
    }

    /**
     * Abort the pending group join...
     */
    function abort()
    {
        $profile = $this->getMember();
        $group = $this->getGroup();

        if (Event::handle('StartCancelJoinGroup', array($profile, $group))) {
            $this->delete();
            Event::handle('EndCancelJoinGroup', array($profile, $group));
        }
    }

    /**
     * Complete a pending group join...
     *
     * @return Group_member object on success
     */
    function complete()
    {
        $join = null;
        $profile = $this->getMember();
        $group = $this->getGroup();
        if (Event::handle('StartJoinGroup', array($profile, $group))) {
            $join = Group_member::join($group->id, $profile->id);
            $this->delete();
            Event::handle('EndJoinGroup', array($profile, $group));
        }
        if (!$join) {
            throw new Exception('Internal error: group join failed.');
        }
        $join->notify();
        return $join;
    }

    /**
     * Send notifications via email etc to group administrators about
     * this exciting new pending moderation queue item!
     */
    public function notify()
    {
        $joiner = Profile::getKV('id', $this->profile_id);
        $group = User_group::getKV('id', $this->group_id);
        mail_notify_group_join_pending($group, $joiner);
    }
}
?>