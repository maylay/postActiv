<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for user_username
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
 * @author    Craig Androws <candrews@integralblue.com>
 * @author    Jeffery To <jeffery.to@gmail.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Evan Prodromou <evan@prodromou.name> 
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class User_username extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_username';                     // table name
    public $user_id;                        // int(4)  not_null
    public $provider_name;                  // varchar(191)  primary_key not_null   not 255 because utf8mb4 takes more space
    public $username;                       // varchar(191)  primary_key not_null   not 255 because utf8mb4 takes more space
    public $created;                        // datetime()   not_null
    public $modified;                       // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'provider_name' => array('type' => 'varchar', 'length' => 191, 'description' => 'provider name'),
                'username' => array('type' => 'varchar', 'length' => 191, 'description' => 'username'),
                'user_id' => array('type' => 'int', 'not null' => true, 'description' => 'notice id this title relates to'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('provider_name', 'username'),
            'indexes' => array(
                'user_id_idx' => array('user_id')
            ),
            'foreign keys' => array(
                'user_username_user_id_fkey' => array('user', array('user_id' => 'id')),
            ),
        );
    }

    /**
    * Register a user with a username on a given provider
    * @param User User object
    * @param string username on the given provider
    * @param provider_name string name of the provider
    * @return mixed User_username instance if the registration succeeded, false if it did not
    */
    static function register($user, $username, $provider_name)
    {
        $user_username = new User_username();
        $user_username->user_id = $user->id;
        $user_username->provider_name = $provider_name;
        $user_username->username = $username;
        $user_username->created = common_sql_now();

        if($user_username->insert()){
            return $user_username;
        }else{
            return false;
        }
    }
}
?>