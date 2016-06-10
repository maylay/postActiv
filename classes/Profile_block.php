<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 * @category  Accounts
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://postactiv.com/
 */

if (!defined('STATUSNET') && !defined('LACONICA')) { exit(1); }

/**
 * Table Definition for profile_block
 */

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Profile_block extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'profile_block';                   // table name
    public $blocker;                         // int(4)  primary_key not_null
    public $blocked;                         // int(4)  primary_key not_null
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'blocker' => array('type' => 'int', 'not null' => true, 'description' => 'user making the block'),
                'blocked' => array('type' => 'int', 'not null' => true, 'description' => 'profile that is blocked'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date of blocking'),
            ),
            'foreign keys' => array(
                'profile_block_blocker_fkey' => array('user', array('blocker' => 'id')),
                'profile_block_blocked_fkey' => array('profile', array('blocked' => 'id')),
            ),
            'primary key' => array('blocker', 'blocked'),
        );
    }

    static function exists(Profile $blocker, Profile $blocked)
    {
        return Profile_block::pkeyGet(array('blocker' => $blocker->id,
                                            'blocked' => $blocked->id));
    }
}
?>