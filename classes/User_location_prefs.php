<?php
/* ============================================================================
 * Title: User Location Preferences
 * Class to hold a user's location preferences
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016, Maiyannah Bishop
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
 * Data class for user location preferences
 *
 * PHP version:
 * Tested with PHP 5.6
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

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

// ============================================================================
// Class: User_location_prefs
// Class to hold user location data preferences
//
// Variables:
// o __table
// o user_id
// o share_location
// o created
// o modified
class User_location_prefs extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_location_prefs';             // table name
    public $user_id;                         // int(4)  primary_key not_null
    public $share_location;                  // tinyint(1)   default_1
    public $created;                         // datetime   not_null default_0000-00-00%2000%3A00%3A00
    public $modified;                        // timestamp   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns the schema definition of this class
   // 
   // Returns:
   // o array
   public static function schemaDef() {
      return array(
         'fields' => array(
            'user_id' => array('type' => 'int', 'not null' => true, 'description' => 'user who has the preference'),
            'share_location' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'Whether to share location data'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
         ),
         'primary key' => array('user_id'),
         'foreign keys' => array(
            'user_location_prefs_user_id_fkey' => array('user', array('user_id' => 'id')),
         ),
      );
   }
}

// END OF FILE
// ============================================================================
?>