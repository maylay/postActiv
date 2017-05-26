<?php
/* ============================================================================
 * Title: Group_alias
 * Table Definition for group_alias
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
 * Table Definition for group_alias
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Ciaran Gultnieks <ciaran@ciarang.com>
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
// Class: Group_alias
// Superclass representing a group_alias entry in the database.
//
// Properties:
// o __table = 'group_alias' - table name
// o alias    - varchar(64)  primary_key not_null
// o group_id - int(4)   not_null
// o modified - timestamp()   not_null default_CURRENT_TIMESTAMP
class Group_alias extends Managed_DataObject {
   public $__table = 'group_alias';
   public $alias;
   public $group_id;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema in the DB.
   public static function schemaDef() {
       return array(
          'fields' => array(
             'alias' => array('type' => 'varchar', 'length' => 64, 'not null' => true, 'description' => 'additional nickname for the group'),
             'group_id' => array('type' => 'int', 'not null' => true, 'description' => 'group profile is blocked from'),
             'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date alias was created'),),
          'primary key' => array('alias'),
          'foreign keys' => array(
             'group_alias_group_id_fkey' => array('user_group', array('group_id' => 'id')),),
          'indexes' => array(
             'group_alias_group_id_idx' => array('group_id'),),);
   }


   // -------------------------------------------------------------------------
   // Function: getProfile
   // Returns the profile of the group referenced by this alias.
   public function getProfile() {
      $group = User_group::getKV('id', $this->group_id);
      if (!($group instanceof User_group)) {
         return null;    // TODO: Throw exception when other code is ready
      }
      return $group->getProfile();
   }
}

// END OF FILE
// ============================================================================
?>