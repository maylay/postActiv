<?php
/* ============================================================================
 * Title: Login_token
 * Table Definition for login_token
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
 * Table Definition for login_token
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Craig Andrews <candrews@integralblue.com>
 * o Evan Prodromou
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

if (!defined('POSTACTIV')) { exit(1); }

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

// ============================================================================
// Class: Login_token
// Superclass representing a login token in the database, with the related
// interfaces.
//
// Constants:
// o TIMEOUT = 120; - seconds after which to timeout the token
//
// Properties:
// o __table = 'login_token' - table name
// o user_id    - int(4)  primary_key not_null
// o token      - char(32)  not_null
// o created    - datetime()   not_null
// o modified   - timestamp()   not_null default_CURRENT_TIMESTAMP
class Login_token extends Managed_DataObject {
   const TIMEOUT = 120; // seconds after which to timeout the token

   public $__table = 'login_token';         // table name
   public $user_id;                         // int(4)  primary_key not_null
   public $token;                           // char(32)  not_null
   public $created;                         // datetime()   not_null
   public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an associative array containing a description of how the login 
   // token is stored in the backend database.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'user_id' => array('type' => 'int', 'not null' => true, 'description' => 'user owning this token'),
            'token' => array('type' => 'char', 'length' => 32, 'not null' => true, 'description' => 'token useable for logging in'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('user_id'),
         'foreign keys' => array('login_token_user_id_fkey' => array('user', array('user_id' => 'id')),),);
    }


   // -------------------------------------------------------------------------
   // Function: makeNew
   // Construct a new login token for User $user.
   //
   // Returns:
   // o constructed Login_token
   function makeNew($user) {
      $login_token = Login_token::getKV('user_id', $user->id);
      if (!empty($login_token)) {
         $login_token->delete();
      }

      $login_token = new Login_token();
      $login_token->user_id = $user->id;
      $login_token->token   = common_random_hexstr(16);
      $login_token->created = common_sql_now();
      $result = $login_token->insert();
      if (!$result) {
         common_log_db_error($login_token, 'INSERT', __FILE__);
         // TRANS: Exception thrown when trying creating a login token failed.
         // TRANS: %s is the user nickname for which token creation failed.
         throw new Exception(sprintf(_('Could not create login token for %s'), $user->nickname));
      }
      return $login_token;
   }
}

// END OF FILE
// ============================================================================
?>