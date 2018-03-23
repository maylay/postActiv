<?php
/* ============================================================================
 * Title: ActivityVerb
 * Abstraction class for an Activity Verb
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
 * An activity
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Siebrand Mazeland <s.mazeland@xs4all.nl>
 *  o Sashi Gowda <connect2shashi@gmail.com>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
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
// Class: ActivityVerb
// Utility class to hold a bunch of constant defining default verb types
//
// Defines:
// o POST           - 'http://activitystrea.ms/schema/1.0/post'
// o SHARE          - 'http://activitystrea.ms/schema/1.0/share'
// o SAVE           - 'http://activitystrea.ms/schema/1.0/save'
// o FAVORITE       - 'http://activitystrea.ms/schema/1.0/favorite'
// o LIKE           - 'http://activitystrea.ms/schema/1.0/like' (This is a synonym of favorite)
// o PLAY           - 'http://activitystrea.ms/schema/1.0/play'
// o FOLLOW         - 'http://activitystrea.ms/schema/1.0/follow'
// o FRIEND         - 'http://activitystrea.ms/schema/1.0/make-friend'
// o JOIN           - 'http://activitystrea.ms/schema/1.0/join'
// o TAG            - 'http://activitystrea.ms/schema/1.0/tag'
// o DELETE         - 'http://activitystrea.ms/schema/1.0/delete'
// o UPDATE         - 'http://activitystrea.ms/schema/1.0/update'
// o UNFAVORITE     - 'http://activitystrea.ms/schema/1.0/unfavorite'
// o UNLIKE         - 'http://activitystrea.ms/schema/1.0/unlike' (This is a synonym of unfavorite)
// o UNFOLLOW       - 'http://ostatus.org/schema/1.0/unfollow'
// o LEAVE          - 'http://ostatus.org/schema/1.0/leave'
// o UNTAG          - 'http://ostatus.org/schema/1.0/untag'
// o UPDATE_PROFILE - 'http://ostatus.org/schema/1.0/update-profile'
class ActivityVerb
{
   const POST     = 'http://activitystrea.ms/schema/1.0/post';
   const SHARE    = 'http://activitystrea.ms/schema/1.0/share';
   const SAVE     = 'http://activitystrea.ms/schema/1.0/save';
   const FAVORITE = 'http://activitystrea.ms/schema/1.0/favorite';
   const LIKE     = 'http://activitystrea.ms/schema/1.0/like'; // This is a synonym of favorite
   const PLAY     = 'http://activitystrea.ms/schema/1.0/play';
   const FOLLOW   = 'http://activitystrea.ms/schema/1.0/follow';
   const FRIEND   = 'http://activitystrea.ms/schema/1.0/make-friend';
   const JOIN     = 'http://activitystrea.ms/schema/1.0/join';
   const TAG      = 'http://activitystrea.ms/schema/1.0/tag';
   const DELETE   = 'http://activitystrea.ms/schema/1.0/delete';
   const UPDATE   = 'http://activitystrea.ms/schema/1.0/update';

   // Custom OStatus verbs for the flipside until they're standardized
   const UNFAVORITE = 'http://activitystrea.ms/schema/1.0/unfavorite';
   const UNLIKE     = 'http://activitystrea.ms/schema/1.0/unlike'; // This is a synonym of unfavorite
   const UNFOLLOW   = 'http://ostatus.org/schema/1.0/unfollow';
   const LEAVE      = 'http://ostatus.org/schema/1.0/leave';
   const UNTAG      = 'http://ostatus.org/schema/1.0/untag';

   // For simple profile-update pings; no content to share.
   const UPDATE_PROFILE = 'http://ostatus.org/schema/1.0/update-profile';


   // -------------------------------------------------------------------------
   // Function: canonical
   // Help function which will return the canonical link for a given Activity
   // verb.
   //
   // Parameters:
   // o verb
   //
   // Returns:
   // o string
   static function canonical($verb) {
      $ns = 'http://activitystrea.ms/schema/1.0/';
      if (substr($verb, 0, mb_strlen($ns)) == $ns) {
         return substr($verb, mb_strlen($ns));
      } else {
         return $verb;
      }
   }
}

// END OF FILE
// ============================================================================
?>