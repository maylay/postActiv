<?php
/* ============================================================================
 * Title: Location_namespace
 * Table Definition for location_namespace
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
 * Table Definition for location_namespace
 *
 * I'm not entirely sure we need this, it seems extraneous and probably a 
 * candidate for removal to reduce complexity of the code. - mb
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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

if (!defined('POSTACTIV')) { exit(1); }

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';


// ============================================================================
// Function: Location_namespace
// Superclass holding the representation of a location namespace form the 
// database.
//
// Properties:
// o __table = 'location_namespace' - table name
// o id          - int(4)  primary_key not_null
// o description - varchar(191)
// o created     - datetime()   not_null
// o modified    - timestamp()   not_null default_CURRENT_TIMESTAMP
class Location_namespace extends Managed_DataObject {
   public $__table = 'location_namespace';
   public $id;
   public $description;
   public $created;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing how schemaDef is stored in the backend 
   // database.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'int', 'not null' => true, 'description' => 'identity for this namespace'),
            'description' => array('type' => 'varchar', 'length' => 191, 'description' => 'description of the namespace'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date the record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('id'),);
   }
}

// END OF FILE
// =============================================================================
?>