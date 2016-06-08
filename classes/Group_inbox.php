<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for group_inbox
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
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */
class Group_inbox extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'group_inbox';                     // table name
    public $group_id;                        // int(4)  primary_key not_null
    public $notice_id;                       // int(4)  primary_key not_null
    public $created;                         // datetime()   not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'description' => 'Many-many table listing notices posted to a given group, or which groups a given notice was posted to.',
            'fields' => array(
                'group_id' => array('type' => 'int', 'not null' => true, 'description' => 'group receiving the message'),
                'notice_id' => array('type' => 'int', 'not null' => true, 'description' => 'notice received'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date the notice was created'),
            ),
            'primary key' => array('group_id', 'notice_id'),
            'foreign keys' => array(
                'group_inbox_group_id_fkey' => array('user_group', array('group_id' => 'id')),
                'group_inbox_notice_id_fkey' => array('notice', array('notice_id' => 'id')),
            ),
            'indexes' => array(
                'group_inbox_created_idx' => array('created'),
                'group_inbox_notice_id_idx' => array('notice_id'),
                'group_inbox_group_id_created_notice_id_idx' => array('group_id', 'created', 'notice_id'),
            ),
        );
    }
}
?>