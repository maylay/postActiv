<?php
/* ============================================================================
 * Title: Sms_carrier
 * Table Definition for sms_carrier
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
 * Table Definition for sms_carrier
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
// Class: Sms_carrier
// Superclass representing a record with information about a SMS carrier.
//
// Properties:
// o __table = 'sms_carrier' - table name
// o id            - int(4)  primary_key not_null
// o name          - varchar(64)  unique_key
// o email_pattern - varchar(191)   not_null   not 255 because utf8mb4 takes more space
// o created       - datetime()   not_null
// o modified      - timestamp()   not_null default_CURRENT_TIMESTAMP
class Sms_carrier extends Managed_DataObject {
   public $__table = 'sms_carrier';                     // table name
   public $id;                              // int(4)  primary_key not_null
   public $name;                            // varchar(64)  unique_key
   public $email_pattern;                   // varchar(191)   not_null   not 255 because utf8mb4 takes more space
   public $created;                         // datetime()   not_null
   public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representation of the table's schema definition in the DB.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'int', 'not null' => true, 'description' => 'primary key for SMS carrier'),
            'name' => array('type' => 'varchar', 'length' => 64, 'description' => 'name of the carrier'),
            'email_pattern' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'sprintf pattern for making an email address from a phone number'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('id'),
         'unique keys' => array(
            'sms_carrier_name_key' => array('name'),),
      );
   }


   // -------------------------------------------------------------------------
   // Function: toEmailAddress
   // Given the email pattern in a given SMS entry, turn it into a gateway
   // email address.
   function toEmailAddress($sms) {
      return sprintf($this->email_pattern, $sms);
   }
}

// END OF FILE
// ============================================================================
?>