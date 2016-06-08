<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for remember_me
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
 * @category  Authentication
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Remember_me extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'remember_me';                     // table name
    public $code;                            // varchar(32)  primary_key not_null
    public $user_id;                         // int(4)   not_null
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'code' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'description' => 'good random code'),
                'user_id' => array('type' => 'int', 'not null' => true, 'description' => 'user who is logged in'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('code'),
            'foreign keys' => array(
                'remember_me_user_id_fkey' => array('user', array('user_id' => 'id')),
            ),
        );
    }    
}
?>