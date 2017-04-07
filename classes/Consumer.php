<?php
/* ============================================================================
 * Title: Consumer
 * Table Definition for consumer
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
 * Table Definition for consumer
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Evan Prodromou <evan@prodromou.name>
 * o Zach Copley
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

class Consumer extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'consumer';                        // table name
    public $consumer_key;                    // varchar(191)  primary_key not_null   not 255 because utf8mb4 takes more space
    public $consumer_secret;                 // varchar(191)   not_null   not 255 because utf8mb4 takes more space
    public $seed;                            // char(32)   not_null
    public $created;                         // datetime   not_null
    public $modified;                        // timestamp   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'description' => 'OAuth consumer record',
            'fields' => array(
                'consumer_key' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'unique identifier, root URL'),
                'consumer_secret' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'secret value'),
                'seed' => array('type' => 'char', 'length' => 32, 'not null' => true, 'description' => 'seed for new tokens by this consumer'),

                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('consumer_key'),
        );
    }

    static function generateNew()
    {
        $cons = new Consumer();
        $rand = common_random_hexstr(16);

        $cons->seed            = $rand;
        $cons->consumer_key    = md5(time() + $rand);
        $cons->consumer_secret = md5(md5(time() + time() + $rand));
        $cons->created         = common_sql_now();

        return $cons;
    }

    /**
     * Delete a Consumer and related tokens and nonces
     *
     * XXX: Should this happen in an OAuthDataStore instead?
     *
     */
    function delete($useWhere=false)
    {
        // XXX: Is there any reason NOT to do this kind of cleanup?

        $this->_deleteTokens();
        $this->_deleteNonces();

        return parent::delete($useWhere);
    }

    function _deleteTokens()
    {
        $token = new Token();
        $token->consumer_key = $this->consumer_key;
        $token->delete();
    }

    function _deleteNonces()
    {
        $nonce = new Nonce();
        $nonce->consumer_key = $this->consumer_key;
        $nonce->delete();
    }
}

// END OF FILE
// ============================================================================
?>