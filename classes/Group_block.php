<?php
/* ============================================================================
 * Title: Group_block
 * Table Definition for group_block
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
 * Table Definition for group_block
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou <evan@prodromou.name>
 * o Brion Vibber <brion@pobox.com>
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
// Class: Group_block
// Superclass representing a block record from a group blocking a user.
//
// Properties:
// o __table = 'group_block';                     // table name
// o group_id;                        // int(4)  primary_key not_null
// o blocked;                         // int(4)  primary_key not_null
// o blocker;                         // int(4)   not_null
// o modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP
class Group_block extends Managed_DataObject {
   public $__table = 'group_block';
   public $group_id;
   public $blocked;
   public $blocker;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema in the DB.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'group_id' => array('type' => 'int', 'not null' => true, 'description' => 'group profile is blocked from'),
            'blocked' => array('type' => 'int', 'not null' => true, 'description' => 'profile that is blocked'),
            'blocker' => array('type' => 'int', 'not null' => true, 'description' => 'user making the block'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date of blocking'),),
         'primary key' => array('group_id', 'blocked'),
         'foreign keys' => array(
            'group_block_group_id_fkey' => array('user_group', array('group_id' => 'id')),
            'group_block_blocked_fkey' => array('profile', array('blocked' => 'id')),
            'group_block_blocker_fkey' => array('user', array('blocker' => 'id')),),);
   }


   // -------------------------------------------------------------------------
   // Function: isBlocked
   // Returns true/false whether a given group has a given user blocked.
   static function isBlocked($group, $profile) {
      $block = Group_block::pkeyGet(array('group_id' => $group->id,
                                          'blocked' => $profile->id));
      return !empty($block);
   }


   // -------------------------------------------------------------------------
   // Function: blockProfile
   // Block a user from a group.
   //
   // Returns:
   // o created block entry
   static function blockProfile($group, $profile, $blocker) {
      // Insert the block
      $block = new Group_block();
      $block->query('BEGIN');
      $block->group_id = $group->id;
      $block->blocked  = $profile->id;
      $block->blocker  = $blocker->id;
      $result = $block->insert();

      if ($result === false) {
         common_log_db_error($block, 'INSERT', __FILE__);
         return null;
      }

      // Delete membership if any
      $member = new Group_member();
      $member->group_id   = $group->id;
      $member->profile_id = $profile->id;

      if ($member->find(true)) {
         $result = $member->delete();
         if ($result === false) {
            common_log_db_error($member, 'DELETE', __FILE__);
            return null;
         }
      }

      // Commit, since both have been done
      $block->query('COMMIT');
      return $block;
   }


   // -------------------------------------------------------------------------
   // Function: unblockProfile
   // Unblock a user from a group.
   static function unblockProfile($group, $profile) {
      $block = Group_block::pkeyGet(array('group_id' => $group->id,
                                          'blocked' => $profile->id));
      if (empty($block)) {
         return null;
      }
      
      $result = $block->delete();
      if (!$result) {
         common_log_db_error($block, 'DELETE', __FILE__);
         return null;
      }
      return true;
   }
}

// END OF FILE
// ============================================================================
?>