<?php
/* ============================================================================
 * Title: Spam_score
 * Score of a notice by activity spam service
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
 * Score of a notice by activity spam service
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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


// ----------------------------------------------------------------------------
// Class: Spam_score
// Score of a notice per the activity spam service
//
// Defines:
// o MAX_SCALE - 10000
//
// Variables:
// o $__table   - 'spam_score'
// o $notice_id - int
// o $score     - float
// o $created   - datetime
class Spam_score extends Managed_DataObject
{
   const MAX_SCALE = 10000;

   public $__table = 'spam_score'; // table name
   public $notice_id;   // int
   public $score;       // float
   public $created;     // datetime


   // Function: schemaDef
   // Returns an array containing the layout of the database table for the
   // purposes of maintenance tools to understand the database.
   //
   // Returns:
   // o array
   public static function schemaDef() {
      return array(
         'description' => 'score of the notice per activityspam',
         'fields' => array(
            'notice_id' => array('type' => 'int',
                                 'not null' => true,
                                 'description' => 'notice getting scored'),
            'score' => array('type' => 'double',
                             'not null' => true,
                             'description' => 'score for the notice (0.0, 1.0)'),
            'scaled' => array('type' => 'int',
                              'description' => 'scaled score for the notice (0, 10000)'),
            'is_spam' => array('type' => 'tinyint',
                               'description' => 'flag for spamosity'),
            'created' => array('type' => 'datetime',
                               'not null' => true,
                               'description' => 'date this record was created'),
            'notice_created' => array('type' => 'datetime',
                                      'description' => 'date the notice was created')),
         'primary key' => array('notice_id'),
            'foreign keys' => array(
                'spam_score_notice_id_fkey' => array('notice', array('notice_id' => 'id'))),
         'indexes' => array(
                'spam_score_created_idx' => array('created'),
                'spam_score_scaled_idx' => array('scaled')));
   }

   // -------------------------------------------------------------------------
   // Function: saveNew
   // Create a new spam score for a notice using a provider.  Called from the
   // provider to do so.
   //
   // Parameters:
   // o notice - notice the spam score is for
   // o result - spam score of the notice
   //
   // Return:
   // o Spam_score score - new Spam_score object
   function saveNew($notice, $result) {
      $score = new Spam_score();
      $score->notice_id      = $notice->id;
      $score->score          = $result->probability;
      $score->is_spam        = $result->isSpam;
      $score->scaled         = Spam_score::scale($score->score);
      $score->created        = common_sql_now();
      $score->notice_created = $notice->created;
      $score->insert();
      self::blow('spam_score:notice_ids');
      return $score;
   }


   // -------------------------------------------------------------------------
   // Function: save
   // As saveNew but does duplication checking and a few other things.  If an
   // existing spam score is there for the notice, we update it, rather than
   // creating a new object.
   //
   // Parameters:
   // o notice - notice the spam score is for
   // o result - spam score of the notice
   //
   // Return:
   // o Spam_score score - new Spam_score object
   function save($notice, $result) {
      $orig  = null;
      $score = Spam_score::getKV('notice_id', $notice->id);
      if (empty($score)) {
         $score = new Spam_score();
      } else {
         $orig = clone($score);
      }
      $score->notice_id      = $notice->id;
      $score->score          = $result->probability;
      $score->is_spam        = $result->isSpam;
      $score->scaled         = Spam_score::scale($score->score);
      $score->created        = common_sql_now();
      $score->notice_created = $notice->created;
      if (empty($orig)) {
            $score->insert();
      } else {
            $score->update($orig);
      }
      self::blow('spam_score:notice_ids');
      return $score;
   }


   // -------------------------------------------------------------------------
   // Function: delete
   // Delete a spam score entry
   function delete($useWhere=false) {
      self::blow('spam_score:notice_ids');
      self::blow('spam_score:notice_ids;last');
      return parent::delete($useWhere);
   }


   // -------------------------------------------------------------------------
   // Function: upgrade
   // Perform some basic maintanence functions when ./scripts/upgrade.php is
   // run.
   public static function upgrade() {
      Spam_score::upgradeScaled();
      Spam_score::upgradeIsSpam();
      Spam_score::upgradeNoticeCreated();
   }


   // -------------------------------------------------------------------------
   // Function: upgradeScaled
   // Finds entries in the database where the `scaled` attribute is NULL and
   // updates them with the appropriate value.
   protected static function upgradeScaled() {
      $score = new Spam_score();
      $score->whereAdd('scaled IS NULL');
      if ($score->find()) {
         while ($score->fetch()) {
            $orig = clone($score);
            $score->scaled = Spam_score::scale($score->score);
            $score->update($orig);
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: upgradeIsSpam
   // Finds entries in the database where the `is_spam` attribute is NULL and
   // updates them with the appropriate value.
   protected static function upgradeIsSpam() {
      $score = new Spam_score();
      $score->whereAdd('is_spam IS NULL');
      if ($score->find()) {
         while ($score->fetch()) {
            $orig = clone($score);
            $score->is_spam = ($score->score >= 0.90) ? 1 : 0;
            $score->update($orig);
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: upgradeNoticeCreated
   // Finds entries in the database where the `notice_created` attribute is
   // NULL and updates them with the appropriate value.
   protected static function upgradeNoticeCreated() {
      $score = new Spam_score();
      $score->whereAdd('notice_created IS NULL');
      if ($score->find()) {
         while ($score->fetch()) {
            $notice = Notice::getKV('id', $score->notice_id);
            if (!empty($notice)) {
               $orig = clone($score);
               $score->notice_created = $notice->created;
               $score->update($orig);
            }
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: scale
   // Scale the score given from a provider to our internal maximum.
   public static function scale($score) {
      $raw = round($score * Spam_score::MAX_SCALE);
      return max(0, min(Spam_score::MAX_SCALE, $raw));
   }
}

// END OF FILE
// ============================================================================
?>