<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for schema_version
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
 * @category  Database
 * @package   postActiv
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @copyright 2010-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

class Schema_version extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'schema_version';      // table name
    public $table_name;                      // varchar(64)  primary_key not_null
    public $checksum;                        // varchar(64)  not_null
    public $modified;                        // datetime()   not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'description' => 'To avoid checking database structure all the time, we store a checksum of the expected schema info for each table here. If it has not changed since the last time we checked the table, we can leave it as is.',
            'fields' => array(
                'table_name' => array('type' => 'varchar', 'length' => '64', 'not null' => true, 'description' => 'Table name'),
                'checksum' => array('type' => 'varchar', 'length' => '64', 'not null' => true, 'description' => 'Checksum of schema array; a mismatch indicates we should check the table more thoroughly.'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('table_name'),
        );
    }
}
?>