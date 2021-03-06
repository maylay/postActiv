<?php
/* ============================================================================
 * Title: Profile_tag_subscription
 * Table Definition for profile_tag_subscription
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
 * Table Definition for profile_tag_subscription
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Evan Prodromou <evan@prodromou.name>
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
// Class: Profile_tag_subscription
// Superclass representing the subscription entry for a profile tag
//
// Properties:
// o __table = 'profile_tag_subscription' - table name
// o profile_tag_id - int(4)  not_null
// o profile_id     - int(4)  not_null
// o created        - datetime   not_null default_0000-00-00%2000%3A00%3A00
// o modified       - timestamp()   not_null default_CURRENT_TIMESTAMP
class Profile_tag_subscription extends Managed_DataObject {
   public $__table = 'profile_tag_subscription';
   public $profile_tag_id;
   public $profile_id;
   public $created;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema in the DB.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'profile_tag_id' => array('type' => 'int', 'not null' => true, 'description' => 'foreign key to profile_tag'),
            'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'foreign key to profile table'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('profile_tag_id', 'profile_id'),
         'foreign keys' => array(
            'profile_tag_subscription_profile_list_id_fkey' => array('profile_list', array('profile_tag_id' => 'id')),
            'profile_tag_subscription_profile_id_fkey' => array('profile', array('profile_id' => 'id')),),
         'indexes' => array(
            'profile_tag_subscription_profile_id_idx' => array('profile_id'),
            'profile_tag_subscription_created_idx' => array('created'),),);
   }


   // -------------------------------------------------------------------------
   // Function: add
   // Create a peopletag subscription and fire the related events.
   //
   // Error States:
   // o throws an Exception if the insert to the DB fails
   static function add($peopletag, $profile) {
      if ($peopletag->private) {
         return false;
      }

      if (Event::handle('StartSubscribePeopletag', array($peopletag, $profile))) {
         $args = array('profile_tag_id' => $peopletag->id,
                       'profile_id' => $profile->id);
         $existing = Profile_tag_subscription::pkeyGet($args);
         if(!empty($existing)) {
            return $existing;
         }

         $sub = new Profile_tag_subscription();
         $sub->profile_tag_id = $peopletag->id;
         $sub->profile_id = $profile->id;
         $sub->created = common_sql_now();
         $result = $sub->insert();

         if (!$result) {
            common_log_db_error($sub, 'INSERT', __FILE__);
            // TRANS: Exception thrown when inserting a list subscription in the database fails.
            throw new Exception(_('Adding list subscription failed.'));
         }

         $ptag = Profile_list::getKV('id', $peopletag->id);
         $ptag->subscriberCount(true);
         Event::handle('EndSubscribePeopletag', array($peopletag, $profile));
         return $ptag;
      }
   }


   // -------------------------------------------------------------------------
   // Function: remove
   // Delete a peopletag subscription and fire the related events
   //
   // Error States:
   // o throws an exception if the DB delete fails
   static function remove($peopletag, $profile) {
      $sub = Profile_tag_subscription::pkeyGet(array('profile_tag_id' => $peopletag->id,
                                            'profile_id' => $profile->id));
      if (empty($sub)) {
         // silence is golden?
         return true;
      }

      if (Event::handle('StartUnsubscribePeopletag', array($peopletag, $profile))) {
         $result = $sub->delete();
         if (!$result) {
            common_log_db_error($sub, 'DELETE', __FILE__);
            // TRANS: Exception thrown when deleting a list subscription from the database fails.
            throw new Exception(_('Removing list subscription failed.'));
         }
         $peopletag->subscriberCount(true);
         Event::handle('EndUnsubscribePeopletag', array($peopletag, $profile));
         return true;
      }
   }


   // -------------------------------------------------------------------------
   // Function: cleanup
   // Helper function to perform garbage collection for an unsubscription event.
   static function cleanup($profile_list) {
      $subs = new self();
      $subs->profile_tag_id = $profile_list->id;
      $subs->find();
      while($subs->fetch()) {
         $profile = Profile::getKV('id', $subs->profile_id);
         Event::handle('StartUnsubscribePeopletag', array($profile_list, $profile));
         // Delete anyway
         $subs->delete();
         Event::handle('StartUnsubscribePeopletag', array($profile_list, $profile));
      }
   }


   // -------------------------------------------------------------------------
   // Function: insert
   // Helper function to perform the DB insertion of a peopletag subscription
   // record.
   function insert() {
      $result = parent::insert();
      if ($result) {
         self::blow('profile_list:subscriber_count:%d', $this->profile_tag_id);
      }
      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: delete
   // Helper function to perform the DB delete of a peopletag subscription
   // record.
   function delete($useWhere=false) {
      $result = parent::delete($useWhere);
      if ($result !== false) {
         self::blow('profile_list:subscriber_count:%d', $this->profile_tag_id);
      }
      return $result;
   }
}

// END OF FILE
// ============================================================================
?>