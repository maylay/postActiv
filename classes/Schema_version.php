<?php
/* ============================================================================
 * Title: Schema_version
 * Table Definition for schema_version
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
 * Table Definition for schema_version.
 *
 * To avoid checking database structure all the time, we store a checksum of 
 * the expected schema info for each table here. If it has not changed since 
 * the last time we checked the table, we can leave it as is.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Evan Prodromou
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


// ============================================================================
// Functions: Schema_version
// Superclass representing a cached database structure checksum so we don't 
// have to keep looking up database structures.
//
// Properties:
// o __table = 'schema_version' - table name
// o table_name - varchar(64)  primary_key not_null
// o checksum   - varchar(64)  not_null
// o modified   - datetime()   not_null
class Schema_version extends Managed_DataObject {
   public $__table = 'schema_version';      // table name
   public $table_name;                      // varchar(64)  primary_key not_null
   public $checksum;                        // varchar(64)  not_null
   public $modified;                        // datetime()   not_null


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema in the DB.
   public static function schemaDef() {
      return array(
         'description' => 'To avoid checking database structure all the time, we store a checksum of the expected schema info for each table here. If it has not changed since the last time we checked the table, we can leave it as is.',
         'fields' => array(
            'table_name' => array('type' => 'varchar', 'length' => '64', 'not null' => true, 'description' => 'Table name'),
            'checksum' => array('type' => 'varchar', 'length' => '64', 'not null' => true, 'description' => 'Checksum of schema array; a mismatch indicates we should check the table more thoroughly.'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('table_name'),);
    }
}

// END OF FILE
// =============================================================================
?>