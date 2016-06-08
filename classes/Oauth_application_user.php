<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for oauth_application_user
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
 * @author    Zach Copley <zach@copley.name>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Oauth_application_user extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'oauth_application_user';          // table name
    public $profile_id;                      // int(4)  primary_key not_null
    public $application_id;                  // int(4)  primary_key not_null
    public $access_type;                     // tinyint(1)
    public $token;                           // varchar(191)   not 255 because utf8mb4 takes more space
    public $created;                         // datetime   not_null
    public $modified;                        // timestamp   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'user of the application'),
                'application_id' => array('type' => 'int', 'not null' => true, 'description' => 'id of the application'),
                'access_type' => array('type' => 'int', 'size' => 'tiny', 'default' => 0, 'description' => 'access type, bit 1 = read, bit 2 = write'),
                'token' => array('type' => 'varchar', 'length' => 191, 'description' => 'request or access token'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('profile_id', 'application_id'),
            'foreign keys' => array(
                'oauth_application_user_profile_id_fkey' => array('profile', array('profile_id' => 'id')),
                'oauth_application_user_application_id_fkey' => array('oauth_application', array('application_id' => 'id')),
            ),
        );
    }

    static function getByUserAndToken($user, $token)
    {
        if (empty($user) || empty($token)) {
            return null;
        }

        $oau = new Oauth_application_user();

        $oau->profile_id = $user->id;
        $oau->token      = $token;
        $oau->limit(1);

        $result = $oau->find(true);

        return empty($result) ? null : $oau;
    }

    function updateKeys(&$orig)
    {
        $this->_connect();
        $parts = array();
        foreach (array('profile_id', 'application_id', 'token', 'access_type') as $k) {
            if (strcmp($this->$k, $orig->$k) != 0) {
                $parts[] = $k . ' = ' . $this->_quote($this->$k);
            }
        }
        if (count($parts) == 0) {
            // No changes
            return true;
        }
        $toupdate = implode(', ', $parts);

        $table = $this->tableName();
        if(common_config('db','quote_identifiers')) {
            $table = '"' . $table . '"';
        }
        $qry = 'UPDATE ' . $table . ' SET ' . $toupdate .
          ' WHERE profile_id = ' . $orig->profile_id
          . ' AND application_id = ' . $orig->application_id
          . " AND token = '$orig->token'";
        $orig->decache();
        $result = $this->query($qry);
        if ($result) {
            $this->encache();
        }
        return $result;
    }
}
?>