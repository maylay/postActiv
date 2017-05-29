<?php
/* ============================================================================
 * Title: Profile_block
 * Table Definition for profile_block
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
 * Table Definition for profile_block
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

/**
 * Table Definition for profile_block
 */

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';


// ============================================================================
// Class: Profile_block
// Superclass representing how a block is saved in the backend database, and
// related interfaces.
//
// Properties:
// o __table = 'profile_block' - table name
// o blocker  - int(4)  primary_key not_null
// o blocked  - int(4)  primary_key not_null
// o modified - timestamp()   not_null default_CURRENT_TIMESTAMP
class Profile_block extends Managed_DataObject {
   public $__table = 'profile_block';                   // table name
   public $blocker;                         // int(4)  primary_key not_null
   public $blocked;                         // int(4)  primary_key not_null
   public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an associative array representing how the block is stored in the
   // backend database.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'blocker' => array('type' => 'int', 'not null' => true, 'description' => 'user making the block'),
            'blocked' => array('type' => 'int', 'not null' => true, 'description' => 'profile that is blocked'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date of blocking'),),
         'foreign keys' => array(
            'profile_block_blocker_fkey' => array('user', array('blocker' => 'id')),
            'profile_block_blocked_fkey' => array('profile', array('blocked' => 'id')),),
         'primary key' => array('blocker', 'blocked'),);
   }

   // -------------------------------------------------------------------------
   // Function: exists
   // Returns true/false whether $blocker has a block record on file for
   // $blocked.
   static function exists(Profile $blocker, Profile $blocked) {
      return Profile_block::pkeyGet(array('blocker' => $blocker->id, 'blocked' => $blocked->id));
   }
}

// END OF FILE
// ============================================================================
?>