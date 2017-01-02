<?php
/* ============================================================================
 * Title: ActivityStreamJSONDocument
 * Class abstraction for the JSON API document representing an activity or 
 * activity stream.
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
 * Class for serializing Activity Streams in JSON
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Zach Copley
 *  o Evan Prodromou
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
// Function: ActivityStreamJSONDocument
// A class for generating JSON documents that represent an Activity Streams
//
// Defines:
// o CONTENT_TYPE = 'application/json; charset=utf-8';
//
// Variables:
// o $doc    - Top level array representing the document
// o $cur    - The current authenticated user
// o $scoped - default null
// o $title  - Title of the document
// o $links  - Links associated with this document
// o  $count - Count of items in this document.
//             This is cryptically referred to in the spec:
//             "The Stream serialization MAY contain a count property.
class ActivityStreamJSONDocument extends JSONActivityCollection
{
    // Note: Lot of AS folks think the content type should be:
    // 'application/stream+json; charset=utf-8', but this is more
    // useful at the moment, because some programs actually understand
    // it.
    const CONTENT_TYPE = 'application/json; charset=utf-8';

    protected $doc = array();
    protected $cur;
    protected $scoped = null;
    protected $title;
    protected $links;
    protected $count;


   // -------------------------------------------------------------------------
   // Constructor
   //
   // Parameters:
   // o User $cur the current authenticated user
   function __construct($cur = null, $title = null, $items = null, $links = null, $url = null)
   {
      parent::__construct($items, $url);
      $this->cur = $cur ?: common_current_user();
      $this->scoped = !is_null($this->cur) ? $this->cur->getProfile() : null;
      /* Title of the JSON document */
      $this->title = $title;
      if (!empty($items)) {
         $this->count = count($this->items);
      }
      /* Array of links associated with the document */
      $this->links = empty($links) ? array() : $items;
      /* URL of a document, this document? containing a list of all the items in the stream */
      if (!empty($this->url)) {
         $this->url = $this->url;
      }
   }


   // -------------------------------------------------------------------------
   // Function: setTitle
   // Set the title of the document
   //
   // Parameters:
   // o String $title the title
   function setTitle($title) {
      $this->title = $title;
   }


   // -------------------------------------------------------------------------
   // Function: setUrl
   function setUrl($url) {
      $this->url = $url;
   }


   // -------------------------------------------------------------------------
   // Function: addItemsFromNotices
   // Add more than one Item to the document
   //
   // Parameters:
   // o mixed $notices an array of Notice objects or handle
   function addItemsFromNotices($notices) {
      if (is_array($notices)) {
         foreach ($notices as $notice) {
            $this->addItemFromNotice($notice);
         }
      } else {
         while ($notices->fetch()) {
            $this->addItemFromNotice($notices);
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: addItemFromNotice
   // Add a single Notice to the document
   //
   // Parameters:
   // o Notice $notice a Notice to add
   function addItemFromNotice($notice) {
      $act          = $notice->asActivity($this->scoped);
      $act->extra[] = $notice->noticeInfo($this->scoped);
      array_push($this->items, $act->asArray());
      $this->count++;
   }


   // -------------------------------------------------------------------------
   // Function: addLink
   // Add a link to the JSON document
   //
   // Parameters:
   // o string $url the URL for the link
   // o string $rel the link relationship
   function addLink($url = null, $rel = null, $mediaType = null) {
      $link = new ActivityStreamsLink($url, $rel, $mediaType);
      array_push($this->links, $link->asArray());
   }


   // -------------------------------------------------------------------------
   // Function: asString
   // Return the entire document as a big string of JSON
   //
   // Returns:
   // o string encoded JSON output
   function asString() {
      $this->doc['generator'] = 'postActiv ' . GNUSOCIAL_VERSION; // extension
      $this->doc['title'] = $this->title;
      $this->doc['url']   = $this->url;
      $this->doc['totalItems'] = $this->count;
      $this->doc['items'] = $this->items;
      $this->doc['links'] = $this->links; // extension
      return json_encode(array_filter($this->doc)); // filter out empty elements
   }
}

// END OF FILE
// ============================================================================
?>