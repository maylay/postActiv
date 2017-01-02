<?php
/* ============================================================================
 * Title: ActivitySink
 * A collection of Activities
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
 * Tested with PHP 5.6
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
// Class: ActivitySink
// A collection of activities.  In practice this allows us to use external
// ActivityStreams services.
//
// Variables:
// o svcDoxUrl
// o username
// o password
// o collections
class ActivitySink
{
   protected $svcDocUrl   = null;
   protected $username    = null;
   protected $password    = null;
   protected $collections = array();


   // -------------------------------------------------------------------------
   // Function: __construct
   // Constructor for the class object
   //
   // Parameters:
   // o svcDocUrl
   // o username
   // o password
   function __construct($svcDocUrl, $username, $password) {
      $this->svcDocUrl = $svcDocUrl;
      $this->username  = $username;
      $this->password  = $password;
      $this->_parseSvcDoc();
   }


   // -------------------------------------------------------------------------
   // Function: _parseSvcDoc
   private function _parseSvcDoc()
   {
      $client   = new HTTPClient();
      $response = $client->get($this->svcDocUrl);
      if ($response->getStatus() != 200) {
         throw new ServerException("Can't get {$this->svcDocUrl}; response status " . $response->getStatus());
      }
      $xml = $response->getBody();
      $dom = new DOMDocument();
      // We don't want to bother with white spaces
      $dom->preserveWhiteSpace = false;
      // Don't spew XML warnings to output
      $old = error_reporting();
      error_reporting($old & ~E_WARNING);
      $ok = $dom->loadXML($xml);
      error_reporting($old);
      $path = new DOMXPath($dom);
      $path->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
      $path->registerNamespace('app', 'http://www.w3.org/2007/app');
      $path->registerNamespace('activity', 'http://activitystrea.ms/spec/1.0/');
      $collections = $path->query('//app:collection');
      for ($i = 0; $i < $collections->length; $i++) {
         $collection = $collections->item($i);
         $url = $collection->getAttribute('href');
         $takesEntries = false;
         $accepts = $path->query('app:accept', $collection);
         for ($j = 0; $j < $accepts->length; $j++) {
            $accept = $accepts->item($j);
            $acceptValue = $accept->nodeValue;
            if (preg_match('#application/atom\+xml(;\s*type=entry)?#', $acceptValue)) {
               $takesEntries = true;
               break;
            }
         }
         if (!$takesEntries) {
            continue;
         }
         $verbs = $path->query('activity:verb', $collection);
         if ($verbs->length == 0) {
            $this->_addCollection(ActivityVerb::POST, $url);
         } else {
            for ($k = 0; $k < $verbs->length; $k++) {
               $verb = $verbs->item($k);
               $this->_addCollection($verb->nodeValue, $url);
            }
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: _addCollection
   //
   // Parameters:
   // o verb
   // o url
   //
   // Returns:
   // o void
   private function _addCollection($verb, $url) {
      if (array_key_exists($verb, $this->collections)) {
            $this->collections[$verb][] = $url;
      } else {
            $this->collections[$verb] = array($url);
      }
      return;
   }


   // -------------------------------------------------------------------------
   // Function: postActivity
   // Put an activity in the collection
   function postActivity($activity)
   {
      if (!array_key_exists($activity->verb, $this->collections)) {
         throw new Exception("No collection for verb {$activity->verb}");
      } else {
         if (count($this->collections[$activity->verb]) > 1) {
            common_log(LOG_NOTICE, "More than one collection for verb {$activity->verb}");
         }
         $this->postToCollection($this->collections[$activity->verb][0], $activity);
      }
   }


   // -------------------------------------------------------------------------
   // Function: postToCollection
   // Push an activity to a remote service
   //
   // Parameters:
   // o url
   // o activity
   // 
   // Error states:
   // o A variety of errors will be raised due to HTTP error codes if received.
   function postToCollection($url, $activity) {
      $client = new HTTPClient($url);
      $client->setMethod('POST');
      $client->setAuth($this->username, $this->password);
      $client->setHeader('Content-Type', 'application/atom+xml;type=entry');
      $client->setBody($activity->asString(true, true, true));
      $response = $client->send();
      $status = $response->getStatus();
      $reason = $response->getReasonPhrase();
      if ($status >= 200 && $status < 300) {
         return true;
      } else if ($status >= 400 && $status < 500) {
         // TRANS: Client exception thrown when post to collection fails with a 400 status.
         // TRANS: %1$s is a URL, %2$s is the status, %s$s is the fail reason.
         throw new ClientException(sprintf(_m('URLSTATUSREASON','%1$s %2$s %3$s'), $url, $status, $reason));
      } else if ($status >= 500 && $status < 600) {
         // TRANS: Server exception thrown when post to collection fails with a 500 status.
         // TRANS: %1$s is a URL, %2$s is the status, %s$s is the fail reason.
         throw new ServerException(sprintf(_m('URLSTATUSREASON','%1$s %2$s %3$s'), $url, $status, $reason));
      } else {
         // That's unexpected.
         // TRANS: Exception thrown when post to collection fails with a status that is not handled.
         // TRANS: %1$s is a URL, %2$s is the status, %s$s is the fail reason.
         throw new Exception(sprintf(_m('URLSTATUSREASON','%1$s %2$s %3$s'), $url, $status, $reason));
      }
   }
}

// END OF FILE
// ============================================================================
?>