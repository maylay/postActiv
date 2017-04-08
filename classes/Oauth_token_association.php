<?php
/* ============================================================================
 * Title: Oauth_token_association
 * Table Definition for oauth_association
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
 * Table Definition for oauth_association
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley <zach@copley.name>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

require_once INSTALLDIR . '/classes/Memcached_DataObject.php';

class Oauth_token_association extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'oauth_token_association';          // table name
    public $profile_id;                      // int(4)  primary_key not_null
    public $application_id;                  // int(4)  primary_key not_null
    public $token;                           // varchar(191) primary key not null   not 255 because utf8mb4 takes more space
    public $created;                         // datetime   not_null
    public $modified;                        // timestamp   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    static function getByUserAndToken($user, $token)
    {
        if (empty($user) || empty($token)) {
            return null;
        }

        $oau = new oauth_request_token();

        $oau->profile_id = $user->id;
        $oau->token      = $token;
        $oau->limit(1);

        $result = $oau->find(true);

        return empty($result) ? null : $oau;
    }

    public static function schemaDef()
    {
        return array(
            'description' => 'Associate an application ID and profile ID with an OAuth token',
            'fields' => array(
                'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'associated user'),
                'application_id' => array('type' => 'int', 'not null' => true, 'description' => 'the application'),
                'token' => array('type' => 'varchar', 'length' => '191', 'not null' => true, 'description' => 'token used for this association'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('profile_id', 'application_id', 'token'),
            'foreign keys' => array(
                'oauth_token_association_profile_fkey' => array('profile_id', array('profile' => 'id')),
                'oauth_token_association_application_fkey' => array('application_id', array('application' => 'id')),
            )
        );
    }
}

// END OF FILE
// ============================================================================
?>