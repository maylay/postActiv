<?php
/* ============================================================================
 * Title: Attention
 * Class to hold a notification, essentially
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
 * Class to hold a notification, essentially
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chimo <chimo@chromic.org>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.


// ----------------------------------------------------------------------------
// Class: Attention
// Class abstraction to hold notifications that are not a direct reply and not
// part of the general feed.
class Attention extends Managed_DataObject
{
   public $__table = 'attention';  // table name
   public $notice_id;              // int(4) primary_key not_null
   public $profile_id;             // int(4) primary_key not_null
   public $reason;                 // varchar(191)   not 255 because utf8mb4 takes more space
   public $created;                // datetime()   not_null
   public $modified;               // timestamp   not_null default_CURRENT_TIMESTAMP


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Return the schema definition for this class
   //
   // Returns:
   // o array
   public static function schemaDef() {
      return array(
         'description' => 'Notice attentions to profiles (that are not a mention and not result of a subscription)',
         'fields' => array(
            'notice_id' => array('type' => 'int', 'not null' => true, 'description' => 'notice_id to give attention'),
            'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'profile_id for feed receiver'),
            'reason' => array('type' => 'varchar', 'length' => 191, 'description' => 'Optional reason why this was brought to the attention of profile_id'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
         ),
         'primary key' => array('notice_id', 'profile_id'),
         'foreign keys' => array(
            'attention_notice_id_fkey' => array('notice', array('notice_id' => 'id')),
            'attention_profile_id_fkey' => array('profile', array('profile_id' => 'id')),
         ),
         'indexes' => array(
            'attention_notice_id_idx' => array('notice_id'),
            'attention_profile_id_idx' => array('profile_id'),
         ),
      );
   }


   // -------------------------------------------------------------------------
   // Function: saveNew
   // Save a new attention
   //
   // Parameters:
   // o Notice notice  - the notice to make an attention for
   // o Profile target - the user this is an attention for
   // o string reason  - the reason this is brought to their attenntion
   //
   // Return:
   // o att - constructed attention object
   public static function saveNew(Notice $notice, Profile $target, $reason=null)
   {
      try {
         $att = Attention::getByKeys(['notice_id'=>$notice->getID(), 'profile_id'=>$target->getID()]);
         throw new AlreadyFulfilledException('Attention already exists with reason: '._ve($att->reason));
      } catch (NoResultException $e) {
         $att = new Attention();
         $att->notice_id = $notice->getID();
         $att->profile_id = $target->getID();
         $att->reason = $reason;
         $att->created = common_sql_now();
         $result = $att->insert();
         if ($result === false) {
            throw new Exception('Failed Attention::saveNew for notice id=='.$notice->getID().' target id=='.$target->getID().', reason=="'.$reason.'"');
         }
      }
      self::blow('attention:stream:%d', $target->getID());
      return $att;
   }
}
// END OF FILE
// ============================================================================
?>