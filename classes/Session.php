<?php
/* ============================================================================
 * Title: Session
 * Table Definition for session
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
 * Table Definition for session
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Brion Vibber <brion@pobox.com>
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
// Class: Session
// Superclass representing a saved database as it exists in the database and the
// associated interfeces.
//
// Properties:
// o __table = 'session' - table name
// o id           - varchar(32)  primary_key not_null
// o session_data - text()
// o created      - datetime()   not_null
// o modified     - timestamp()   not_null default_CURRENT_TIMESTAMP
class Session extends Managed_DataObject {
   public $__table = 'session';
   public $id;
   public $session_data;
   public $created;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array describing how the session is stored in the database.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'description' => 'session ID'),
            'session_data' => array('type' => 'text', 'description' => 'session data'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('id'),
         'indexes' => array(
            'session_modified_idx' => array('modified'),),);
   }


   // -------------------------------------------------------------------------
   // Function: logdeb
   // A helper function to print a session-related message to the debug log if
   // the site session debug configuration option is enabled.
   static function logdeb($msg) {
        if (common_config('sessions', 'debug')) {
            common_debug("Session: " . $msg);
        }
   }


   // -------------------------------------------------------------------------
   // Function: open
   // Dummy option for saving to file (we don't do this for privacy and secruity
   // reasons) needed for full PHP adherence.
   static function open($save_path, $session_name) {
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: close
   // Dummy option for saving to file (we don't do this for privacy and secruity
   // reasons) needed for full PHP adherence.
   static function close() {
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: read
   // Fetch the session data for the session with the given $id.
   static function read($id) {
      self::logdeb("Fetching session '$id'");
      $session = Session::getKV('id', $id);
      if (empty($session)) {
         self::logdeb("Couldn't find '$id'");
         return '';
      } else {
         self::logdeb("Found '$id', returning " .
                       strlen($session->session_data) .
                       " chars of data");
         return (string)$session->session_data;
      }
   }


   // -------------------------------------------------------------------------
   // Function: write
   // Write the session data for session with given $id as $session_data.
   static function write($id, $session_data) {
      self::logdeb("Writing session '$id'");
      $session = Session::getKV('id', $id);
      if (empty($session)) {
         self::logdeb("'$id' doesn't yet exist; inserting.");
         $session = new Session();
         $session->id           = $id;
         $session->session_data = $session_data;
         $session->created      = common_sql_now();

         $result = $session->insert();
         if (!$result) {
            common_log_db_error($session, 'INSERT', __FILE__);
            self::logdeb("Failed to insert '$id'.");
         } else {
            self::logdeb("Successfully inserted '$id' (result = $result).");
         }
         return $result;
      } else {
         self::logdeb("'$id' already exists; updating.");
         if (strcmp($session->session_data, $session_data) == 0) {
            self::logdeb("Not writing session '$id'; unchanged");
            return true;
         } else {
            self::logdeb("Session '$id' data changed; updating");
            $orig = clone($session);
            $session->session_data = $session_data;
            $result = $session->update($orig);

            if (!$result) {
               common_log_db_error($session, 'UPDATE', __FILE__);
               self::logdeb("Failed to update '$id'.");
            } else {
               self::logdeb("Successfully updated '$id' (result = $result).");
            }

            return $result;
         }
      }
      // make sure we return something on all return paths
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: destroy
   // Deletes session with given id $id.
   static function destroy($id) {
      self::logdeb("Deleting session $id");
      $session = Session::getKV('id', $id);
      if (empty($session)) {
         self::logdeb("Can't find '$id' to delete.");
         return false;
      } else {
         $result = $session->delete();
         if (!$result) {
            common_log_db_error($session, 'DELETE', __FILE__);
            self::logdeb("Failed to delete '$id'.");
         } else {
            self::logdeb("Successfully deleted '$id' (result = $result).");
         }
         return $result;
      }
   }


   // -------------------------------------------------------------------------
   // Function: gc
   // Find sessions that have persisted beyond $maxlifetime and delete them.
   // This will be limited by config['sessions']['gc_limit'] - it won't delete
   // more than the number of sessions specified there at a single pass.
   static function gc($maxlifetime) {
      self::logdeb("garbage collection (maxlifetime = $maxlifetime)");
      $epoch = common_sql_date(time() - $maxlifetime);

      $ids = array();

      $session = new Session();
      $session->whereAdd('modified < "'.$epoch.'"');
      $session->selectAdd();
      $session->selectAdd('id');

      $limit = common_config('sessions', 'gc_limit');
      if ($limit > 0) {
         // On large sites, too many sessions to expire
         // at once will just result in failure.
         $session->limit($limit);
      }

      $session->find();
      while ($session->fetch()) {
         $ids[] = $session->id;
      }
      $session->free();
      self::logdeb("Found " . count($ids) . " ids to delete.");

      foreach ($ids as $id) {
         self::logdeb("Destroying session '$id'.");
         self::destroy($id);
      }
   }


   // -------------------------------------------------------------------------
   // Function: setSaveHandler
   // Set our session handler as the handlers for PHP session handling in the
   // context of postActiv.
   //
   // Returns:
   // o mixed Result - result given by PHP of setting the handlers.
   static function setSaveHandler() {
      self::logdeb("setting save handlers");
      $result = session_set_save_handler('Session::open', 'Session::close', 'Session::read',
                                         'Session::write', 'Session::destroy', 'Session::gc');
      self::logdeb("save handlers result = $result");

      // PHP 5.3 with APC ends up destroying a bunch of object stuff before the session
      // save handlers get called on request teardown.
      // Registering an explicit shutdown function should take care of this before
      // everything breaks on us.
      register_shutdown_function('Session::cleanup');
      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: cleanup
   // Stuff to do before the request teardown.
   static function cleanup() {
      session_write_close();
   }
}

// END OF FILE
// ============================================================================
?>