<?php
/* ============================================================================
 * Title: ActivityContext
 * Class abstraction for Activity verb contexts
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
 * An activity verb in class form, and the related scaffolding.
 *
 * This file also now consolidates the ActivityContext, ActivityImporter,
 * ActivityMover, ActivitySink, and ActivitySource classes, formerly at
 * /lib/<class>.php
 *
 * o Activity abstracts the class for an activity verb.
 * o ActivityContext contains information of the context of the activity verb.
 * o ActivityImporter abstracts a means that is importing activity verbs
 *   into the system as part of a user's timeline.
 * o ActivityMover abstracts the means to transport activity verbs.
 * o ActivitySink abstracts a class to receive activity verbs.
 * o ActivitySource abstracts a class to represent the source of a received
 *    activity verb.
 *
 * ActivityObject is a noun in the activity universe basically, from
 * the original file:
 *     A noun-ish thing in the activity universe
 *
 *     The activity streams spec talks about activity objects, while also
 *     having a tag activity:object, which is in fact an activity object.
 *     Aaaaaah!
 *
 *     This is just a thing in the activity universe. Can be the subject,
 *     object, or indirect object (target!) of an activity verb. Rotten
 *     name, and I'm propagating it. *sigh*
 * It's large enough that I've left it seperate in activityobject.php
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Brion Vibber <brion@pobox.com>
 * o James Walker <walkah@walkah.net>
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

if (!defined('POSTACTIV')) { exit(1); }


// ----------------------------------------------------------------------------
// Class: ActivityContext
class ActivityContext
{
   public $replyToID;
   public $replyToUrl;
   public $location;
   public $attention = array();    // 'uri' => 'type'
   public $conversation;
   public $scope;

   const THR     = 'http://purl.org/syndication/thread/1.0';
   const GEORSS  = 'http://www.georss.org/georss';
   const OSTATUS = 'http://ostatus.org/schema/1.0';

   const INREPLYTO  = 'in-reply-to';
   const REF        = 'ref';
   const HREF       = 'href';
   
   // OStatus element names with prefixes
   const OBJECTTYPE   = 'ostatus:object-type';   // FIXME: Undocumented!
   const CONVERSATION = 'ostatus:conversation';

   const POINT     = 'point';
   const MENTIONED = 'mentioned';

   const ATTN_PUBLIC  = 'http://activityschema.org/collection/public';


   // -------------------------------------------------------------------------
   // Function: __construct
   // Class constructor
   function __construct($element = null)
   {
      if (empty($element)) {
         return;
      }
      $replyToEl = ActivityUtils::child($element, self::INREPLYTO, self::THR);
      if (!empty($replyToEl)) {
         $this->replyToID  = $replyToEl->getAttribute(self::REF);
         $this->replyToUrl = $replyToEl->getAttribute(self::HREF);
      }

      $this->location = $this->getLocation($element);
      $convs = $element->getElementsByTagNameNS(self::OSTATUS, self::CONVERSATION);
      foreach ($convs as $conv) {
         $this->conversation = $conv->textContent;
      }
      if (empty($this->conversation)) {
         // fallback to the atom:link rel="ostatus:conversation" element
         $this->conversation = ActivityUtils::getLink($element, self::CONVERSATION);
      }
      // Multiple attention links allowed
      $links = $element->getElementsByTagNameNS(ActivityUtils::ATOM, ActivityUtils::LINK);
      for ($i = 0; $i < $links->length; $i++) {
         $link = $links->item($i);
         $linkRel  = $link->getAttribute(ActivityUtils::REL);
         $linkHref = $link->getAttribute(self::HREF);
         if ($linkRel == self::MENTIONED && $linkHref !== '') {
            $this->attention[$linkHref] = $link->getAttribute(ActivityContext::OBJECTTYPE);
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: getLocation
   // Parse location given as a GeoRSS-simple point, if provided.
   // http://www.georss.org/simple
   //
   // Parameters:
   // o feed item $entry
   //
   // Returns:
   // o mixed Location or false
   function getLocation($dom) {
      $points = $dom->getElementsByTagNameNS(self::GEORSS, self::POINT);
      for ($i = 0; $i < $points->length; $i++) {
         $point = $points->item($i)->textContent;
         return self::locationFromPoint($point);
      }
      return null;
   }


   // -------------------------------------------------------------------------
   // Function: locationFromPoint
   // XXX: Move to ActivityUtils or Location?
   //
   // Parameters:
   // o point
   static function locationFromPoint($point) {
      $point = str_replace(',', ' ', $point); // per spec "treat commas as whitespace"
      $point = preg_replace('/\s+/', ' ', $point);
      $point = trim($point);
      $coords = explode(' ', $point);
      if (count($coords) == 2) {
         list($lat, $lon) = $coords;
         if (is_numeric($lat) && is_numeric($lon)) {
            common_log(LOG_INFO, "Looking up location for $lat $lon from georss point");
            return Location::fromLatLon($lat, $lon);
         }
      }
      common_log(LOG_ERR, "Ignoring bogus georss:point value $point");
      return null;
   }


   // -------------------------------------------------------------------------
   // Function: asArray
   // Returns context (StatusNet stuff) as an array suitable for serializing
   // in JSON. Right now context stuff is an extension to Activity.
   //
   // Returns:
   // o array the context
   function asArray() {
      $context = array();
      $context['inReplyTo']    = $this->getInReplyToArray();
      $context['conversation'] = $this->conversation;
      return array_filter($context);
   }


   // -------------------------------------------------------------------------
   // Function: getToArray
   // Returns an array of arrays representing Activity Objects (intended to be
   // serialized in JSON) that represent WHO the Activity is supposed to
   // be received by. This is not really specified but appears in an example
   // of the current spec as an extension. We might want to figure out a JSON
   // serialization for OStatus and use that to express mentions instead.
   //
   // XXX: People's ideas on how to do this are all over the place
   //
   // Returns:
   // o array the array of recipients
   function getToArray() {
      $tos = array();
      foreach ($this->attention as $attnUrl => $attnType) {
         $to = array(
                'objectType' => $attnType,  // can be empty
                'id'         => $attnUrl,);
         $tos[] = $to;
      }
      return $tos;
   }


   // -------------------------------------------------------------------------
   // Function: getInReplyToArray
   // Return an array for the notices this notice is a reply to
   // suitable for serializing as JSON note objects.
   //
   // Returns:
   // o array the array of notes
   function getInReplyToArray() {
      if (empty($this->replyToID) && empty($this->replyToUrl)) {
         return null;
      }
      $replyToObj = array('objectType' => 'note');
      // XXX: Possibly shorten this to just the numeric ID?
      //      Currently, it's the full URI of the notice.
      if (!empty($this->replyToID)) {
         $replyToObj['id'] = $this->replyToID;
      }
      if (!empty($this->replyToUrl)) {
         $replyToObj['url'] = $this->replyToUrl;
      }
      return $replyToObj;
   }
}

// END OF FILE
// ============================================================================
?>