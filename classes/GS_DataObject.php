<?php
/* ============================================================================
 * Title: GS_DataObject
 * GS DB object abstraction
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
 * GS DB object abstraction
 *
 * How many bloody redefinitions of DataObject do we need?  Basically this one
 * exists to suppress errors which is a BAD IDEA and we should rectify the
 * errors instead.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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


// ============================================================================
// Class: DB_DataObject
// Superclass redefining DB_DataObject with GS specific functions/overrides.
class GS_DataObject extends DB_DataObject {
   
   // -------------------------------------------------------------------------
   // Function: _autoloadClass
   // Redefine autoload to avoid those annoying PEAR::DB strict standards
   // warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function _autoloadClass($class, $table=false) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::_autoloadClass($class, $table);
      // reset
      error_reporting($old);
      return $res;
   }

   // -------------------------------------------------------------------------
   // Function: _connect
   // Wraps the _connect call so we don't throw E_STRICT warnings during it.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function _connect() {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::_connect();
      // reset
      error_reporting($old);
      return $res;
   }

   // -------------------------------------------------------------------------
   // Function: _loadConfig
   // Wwraps the _loadConfig call so we don't throw E_STRICT warnings during it.
   // Doesn't actually return anything, but we'll follow the same model as the 
   // rest of the wrappers.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function _loadConfig() {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::_loadConfig();
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: count
   // Wraps the count call so we don't throw E_STRICT warnings during it.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function count($countWhat = false,$whereAddOnly = false) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::count($countWhat, $whereAddOnly);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: debugLevel
   // Wraps the debugLevel call so we don't throw E_STRICT warnings during it.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   static public function debugLevel($v = null) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::debugLevel($v);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: delete
   // Delete calls PEAR::isError from DB_DataObject, so let's make that disappear too
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function delete($useWhere = false) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::delete($useWhere);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: factory
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   static public function factory($table = '') {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::factory($table);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: get
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function get($k = null, $v = null) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::get($k, $v);
      // reset
      error_reporting($old);
      return $res;
   }

   
   // -------------------------------------------------------------------------
   // Function: fetch
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function fetch() {
      // avoid those annoying PEAR::DB strict standards warnings it causes
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::fetch();
      // reset
      error_reporting($old);
      return $res;
   }

   // -------------------------------------------------------------------------
   // Function: find
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function find($n = false) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::find($n);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: fetchRow
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function fetchRow($row = null) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::fetchRow($row);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: insert
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function insert() {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::insert();
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: joinAdd
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function joinAdd($obj = false, $joinType='INNER', $joinAs=false, $joinCol=false) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::joinAdd($obj, $joinType, $joinAs, $joinCol);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: links
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function links() {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::links();
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: update
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function update($dataObject = false) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::update($dataObject);
      // reset
      error_reporting($old);
      return $res;
   }
    

   // -------------------------------------------------------------------------
   // Function: staticGet
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   static public function staticGet($class, $k, $v = null) {
      $old = error_reporting();
      error_reporting(error_reporting() & ~E_STRICT);
      $res = parent::staticGet($class, $k, $v);
      // reset
      error_reporting($old);
      return $res;
   }


   // -------------------------------------------------------------------------
   // Function: staticGetAutoloadTable
   // Avoid those annoying PEAR::DB strict standards warnings it causes.
   //
   // This is a dirty hack and suppressing errors, we shouldn't do this and it
   // should be written out.
   public function staticGetAutoloadTable($table)
   {
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::staticGetAutoloadTable($table);

        // reset
        error_reporting($old);
        return $res;
   }
}

// END OF FILE
// ============================================================================
?>