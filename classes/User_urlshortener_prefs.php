<?php
/* ============================================================================
 * Title: URL Shortener Preferences
 * Class to hold a user's URL shortener preferences
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
 * Data class for user URL shortener preferences
 *
 * PHP version:
 * Tested with PHP 7 
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Roland Haeder <roland@mxchange.org>
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
// Class: User_urlshortener_prefs
//
// Variables:
// o __table
// o user_id
// o urlshorteningservice
// o maxurllength
// o created
// o modified
class User_urlshortener_prefs extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_urlshortener_prefs';         // table name
    public $user_id;                         // int(4)  primary_key not_null
    public $urlshorteningservice;            // varchar(50)   default_ur1.ca
    public $maxurllength;                    // int(4)   not_null
    public $maxnoticelength;                 // int(4)   not_null
    public $created;                         // datetime   not_null default_0000-00-00%2000%3A00%3A00
    public $modified;                        // timestamp   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns the table schema for this class
   //
   // Returns:
   // o array
   public static function schemaDef() {
      return array(
         'fields' => array(
            'user_id' => array('type' => 'int', 'not null' => true, 'description' => 'user'),
            'urlshorteningservice' => array('type' => 'varchar', 'length' => 50, 'default' => 'internal', 'description' => 'service to use for auto-shortening URLs'),
            'maxurllength' => array('type' => 'int', 'not null' => true, 'description' => 'urls greater than this length will be shortened, 0 = always, null = never'),
            'maxnoticelength' => array('type' => 'int', 'not null' => true, 'description' => 'notices with content greater than this value will have all urls shortened, 0 = always, -1 = only if notice text is longer than max allowed'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
         ),
         'primary key' => array('user_id'),
         'foreign keys' => array(
            'user_urlshortener_prefs_user_id_fkey' => array('user', array('user_id' => 'id')),
         ),
      );
   }

   // -------------------------------------------------------------------------
   // Function: maxUrlLength
   //
   // Parameters:
   // o user - user we're checking limits for
   //
   // Returns:
   // o integer
   static function maxUrlLength($user) {
      $def = common_config('url', 'maxurllength');
      $prefs = self::getPrefs($user);
      if (empty($prefs)) {
        return $def;
      } else {
        return $prefs->maxurllength;
      }
   }


   // -------------------------------------------------------------------------
   // Function: maxNoticeLength
   // Returns the maximum notice length this user can post
   //
   // Paramters:
   // o user - user we're checking limits for
   //
   // Returns:
   // o integer
   static function maxNoticeLength($user) {
      $def = common_config('url', 'maxnoticelength');
      if ($def == -1) {
         // maxContent==0 means infinite length,
         // but maxNoticeLength==0 means "always shorten"
         // so if maxContent==0 we must set this to -1
         $def = Notice::maxContent() ?: -1;
      }
      $prefs = self::getPrefs($user);
      if (empty($prefs)) {
         return $def;
      } else {
         return $prefs->maxnoticelength;
      }
   }


   // -------------------------------------------------------------------------
   // Function: urlShorteningService
   // Returns the currently-specified urlShorteningService for the user
   // 
   // Parameters:
   // user - user to retrieve the service for
   //
   // Returns:
   // o string
   static function urlShorteningService($user) {
      $def = common_config('url', 'shortener');

      $prefs = self::getPrefs($user);

      if (empty($prefs)) {
         if (!empty($user)) {
            return $user->urlshorteningservice;
         } else {
            return $def;
         }
      } else {
         return $prefs->urlshorteningservice;
      }
   }


   // -------------------------------------------------------------------------
   // Function: getPrefs
   // Returns the preferences for the provided users
   //
   // Parameters:
   // o user - user to get preferences for
   //
   // Returns:
   // o User_urlshortener_prefs object for that user or null if not found
   static function getPrefs($user) {
      if (empty($user)) {
         return null;
      }
      $prefs = User_urlshortener_prefs::getKV('user_id', $user->id);
      return $prefs;
   }
}

// END OF FILE
// ============================================================================
?>