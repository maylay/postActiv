<?php
/* ============================================================================
 * Title: Remember_me
 * Table Definition for remember_me
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
 * Table Definition for remember_me
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
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
// Class: Remember_me
// Superclass representation of remember_me records in the DB.
//
// Properties:
// o __table = 'remember_me' - table name
// o code     -  varchar(32)  primary_key not_null
// o user_id  - int(4)   not_null
// o modified - timestamp()   not_null default_CURRENT_TIMESTAMP
class Remember_me extends Managed_DataObject {
   public $__table = 'remember_me';                     // table name
   public $code;                            // varchar(32)  primary_key not_null
   public $user_id;                         // int(4)   not_null
   public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema in the DB.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'code' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'description' => 'good random code'),
            'user_id' => array('type' => 'int', 'not null' => true, 'description' => 'user who is logged in'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('code'),
         'foreign keys' => array(
            'remember_me_user_id_fkey' => array('user', array('user_id' => 'id')),),);
   }
}

// END OF FILE
// ============================================================================
?>