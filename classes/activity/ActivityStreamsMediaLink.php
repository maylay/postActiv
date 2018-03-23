<?php
/* ============================================================================
 * Title: ActivityStreamsLink
 * Class for media links in an ActivityStreams JSON Activity.
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
 * A class for representing MediaLinks in JSON Activities
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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
// Class: ActivityStreamsMediaLink
// Class for media links in an ActivityStreams JSON Activity.
class ActivityStreamsMediaLink extends ActivityStreamsLink
{
   private $linkDict;


   // ------------------------------------------------------------------------
   // Function: __construct
   // Class constructor
   //
   // Parameters:
   // o url
   // o width
   // o height
   // o mediaType
   // o rel
   // o duration
   function __construct(
        $url       = null,
        $width     = null,
        $height    = null,
        $mediaType = null, // extension
        $rel       = null, // extension
        $duration  = null) {
      parent::__construct($url, $rel, $mediaType);
      $this->linkDict = array(
         'width'      => intval($width),
         'height'     => intval($height),
         'duration'   => intval($duration));
   }


   // ------------------------------------------------------------------------
   // Function: asArray
   // Return the class as an array suitable for JSON
   //
   // Returns:
   // o array
   function asArray() {
      return array_merge(parent::asArray(), array_filter($this->linkDict));
   }
}

// END OF FILE
// ----------------------------------------------------------------------------
?>