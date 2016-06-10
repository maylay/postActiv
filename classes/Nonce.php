<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for nonce
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
 * @category  oAuth
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
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
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Nonce extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'nonce';                           // table name
    public $consumer_key;                    // varchar(191)  primary_key not_null   not 255 because utf8mb4 takes more space
    public $tok;                             // char(32)
    public $nonce;                           // char(32)  primary_key not_null
    public $ts;                              // datetime()  primary_key not_null
    public $created;                         // datetime()   not_null
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /**
     * Compatibility hack for PHP 5.3
     *
     * The statusnet.links.ini entry cannot be read because "," is no longer
     * allowed in key names when read by parse_ini_file().
     *
     * @return   array
     * @access   public
     */
    function links()
    {
        return array('consumer_key,token' => 'token:consumer_key,token');
    }

    public static function schemaDef()
    {
        return array(
            'description' => 'OAuth nonce record',
            'fields' => array(
                'consumer_key' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'unique identifier, root URL'),
                'tok' => array('type' => 'char', 'length' => 32, 'description' => 'buggy old value, ignored'),
                'nonce' => array('type' => 'char', 'length' => 32, 'not null' => true, 'description' => 'nonce'),
                'ts' => array('type' => 'datetime', 'not null' => true, 'description' => 'timestamp sent'),

                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('consumer_key', 'ts', 'nonce'),
        );
    }
}
?>