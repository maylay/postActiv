<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for group_block
 *
 * PHP version 5
 *
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
 * @category  Groups
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://postactiv.com/
 */

if (!defined('STATUSNET') && !defined('LACONICA')) { exit(1); }

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Group_block extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'group_block';                     // table name
    public $group_id;                        // int(4)  primary_key not_null
    public $blocked;                         // int(4)  primary_key not_null
    public $blocker;                         // int(4)   not_null
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'group_id' => array('type' => 'int', 'not null' => true, 'description' => 'group profile is blocked from'),
                'blocked' => array('type' => 'int', 'not null' => true, 'description' => 'profile that is blocked'),
                'blocker' => array('type' => 'int', 'not null' => true, 'description' => 'user making the block'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date of blocking'),
            ),
            'primary key' => array('group_id', 'blocked'),
            'foreign keys' => array(
                'group_block_group_id_fkey' => array('user_group', array('group_id' => 'id')),
                'group_block_blocked_fkey' => array('profile', array('blocked' => 'id')),
                'group_block_blocker_fkey' => array('user', array('blocker' => 'id')),
            ),
        );
    }

    static function isBlocked($group, $profile)
    {
        $block = Group_block::pkeyGet(array('group_id' => $group->id,
                                            'blocked' => $profile->id));
        return !empty($block);
    }

    static function blockProfile($group, $profile, $blocker)
    {
        // Insert the block

        $block = new Group_block();

        $block->query('BEGIN');

        $block->group_id = $group->id;
        $block->blocked  = $profile->id;
        $block->blocker  = $blocker->id;

        $result = $block->insert();

        if ($result === false) {
            common_log_db_error($block, 'INSERT', __FILE__);
            return null;
        }

        // Delete membership if any

        $member = new Group_member();

        $member->group_id   = $group->id;
        $member->profile_id = $profile->id;

        if ($member->find(true)) {
            $result = $member->delete();
            if ($result === false) {
                common_log_db_error($member, 'DELETE', __FILE__);
                return null;
            }
        }

        // Commit, since both have been done

        $block->query('COMMIT');

        return $block;
    }

    static function unblockProfile($group, $profile)
    {
        $block = Group_block::pkeyGet(array('group_id' => $group->id,
                                            'blocked' => $profile->id));

        if (empty($block)) {
            return null;
        }

        $result = $block->delete();

        if (!$result) {
            common_log_db_error($block, 'DELETE', __FILE__);
            return null;
        }

        return true;
    }
}
?>