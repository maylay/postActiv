<?php
/* ============================================================================
 * Title: Consumer
 * Table Definition for consumer
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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


// ============================================================================
// Class: Consumer
// Superclass representing an OAuth consumer as it is stored in the database,
// with associated interfaces.
//
// o __table = 'consumer' - table name
// o consumer_key         - varchar(191)  primary_key not_null   not 255 because utf8mb4 takes more space
// o consumer_secret      - varchar(191)   not_null   not 255 because utf8mb4 takes more space
// o seed                 - char(32)   not_null
// o created              - datetime   not_null
// o modified             - timestamp   not_null default_CURRENT_TIMESTAMP
class Consumer extends Managed_DataObject {
   public $__table = 'consumer';
   public $consumer_key;
   public $consumer_secret;
   public $seed;
   public $created;
   public $modified;

   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the schema used to store the consumer in
   // the backend database.
   public static function schemaDef() {
      return array(
         'description' => 'OAuth consumer record',
         'fields' => array(
            'consumer_key' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'unique identifier, root URL'),
            'consumer_secret' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'secret value'),
            'seed' => array('type' => 'char', 'length' => 32, 'not null' => true, 'description' => 'seed for new tokens by this consumer'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('consumer_key'),);
   }


   // -------------------------------------------------------------------------
   // Function: generateNew
   // Create a new OAuth consumer record.
   //
   // Returns:
   // o created Consumer object
   static function generateNew() {
      $cons = new Consumer();
      $rand = common_random_hexstr(16);
      $cons->seed            = $rand;
      $cons->consumer_key    = md5(time() + $rand);
      $cons->consumer_secret = md5(md5(time() + time() + $rand));
      $cons->created         = common_sql_now();
      return $cons;
   }


   // -------------------------------------------------------------------------
   // Function: delete
   // Delete this OAuth Consumer and related tokens and nonces
   //
   // XXX:
   // o Should this happen in an OAuthDataStore instead?
   // o Is there any reason NOT to do this kind of cleanup?
   function delete($useWhere=false) {
      $this->_deleteTokens();
      $this->_deleteNonces();
      return parent::delete($useWhere);
   }


   // -------------------------------------------------------------------------
   // Function: _deleteTokens
   // Helper function to delete tokens related to this OAauth consumer.
   function _deleteTokens() {
        $token = new Token();
        $token->consumer_key = $this->consumer_key;
        $token->delete();
   }


   // -------------------------------------------------------------------------
   // Function: _deleteNonces
   // Helper function to delete nonces related to this OAuth consumer.
   function _deleteNonces() {
      $nonce = new Nonce();
      $nonce->consumer_key = $this->consumer_key;
      $nonce->delete();
   }
}

// END OF FILE
// ============================================================================
?>