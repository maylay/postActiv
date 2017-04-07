<?php
/* ============================================================================
 * Title: Related_group
 * Table Definition for related_group
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
 * Table Definition for related_group
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou <evan@prodromou.name>
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

class Related_group extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'related_group';                   // table name
    public $group_id;                        // int(4)  primary_key not_null
    public $related_group_id;                // int(4)  primary_key not_null
    public $created;                         // datetime()   not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            // @fixme description for related_group?
            'fields' => array(
                'group_id' => array('type' => 'int', 'not null' => true, 'description' => 'foreign key to user_group'),
                'related_group_id' => array('type' => 'int', 'not null' => true, 'description' => 'foreign key to user_group'),

                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            ),
            'primary key' => array('group_id', 'related_group_id'),
            'foreign keys' => array(
                'related_group_group_id_fkey' => array('user_group', array('group_id' => 'id')),
                'related_group_related_group_id_fkey' => array('user_group', array('related_group_id' => 'id')),
            ),
        );
    }
}

// END OF FILE
// ============================================================================
?>