<?php
/* ============================================================================
 * Title: Activity
 * Class abstraction for Activity verbs
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
// Class: Activity
// An activity in the ActivityStrea.ms world
//
// An activity is kind of like a sentence: someone did something
// to something else.
//
// 'someone' is the 'actor'; 'did something' is the verb;
// 'something else' is the object.
//
// Defines:
// o SPEC        - 'http://activitystrea.ms/spec/1.0/'
// o SCHEMA      - 'http://activitystrea.ms/schema/1.0/'
// o MEDIA       - 'http://purl.org/syndication/atommedia'
// o VERB        - 'verb'
// o OBJECT      - 'object'
// o ACTOR       - 'actor'
// o SUBJECT     - 'subject'
// o OBJECTTYPE  - 'object-type'
// o CONTEXT     - 'context'
// o TARGET      - 'target'
// o ATOM        - 'http://www.w3.org/2005/Atom'
// o AUTHOR      - 'author'
// o PUBLISHED   - 'published'
// o UPDATED     - 'updated'
// o RSS         - null
// o PUBDATE     - 'pubDate'
// o DESCRIPTION - 'description'
// o GUID        - 'guid'
// o SELF        - 'self'
// o IMAGE       - 'image'
// o URL         - 'url'
// o DC          - 'http://purl.org/dc/elements/1.1/'
// o CREATOR     - 'creator'
// o CONTENTNS   - 'http://purl.org/rss/1.0/modules/content/'
// o ENCODED     - 'encoded'
//
// Variables:
// o $actor       - an ActivityObject
// o $verb        - a string (the URL)
// o $objects     - an array of ActivityObjects
// o $target      - an ActivityObject
// o $context     - an ActivityObject
// o $time        - Time of the activity
// o $link        - an ActivityObject
// o $entry       - the source entry
// o $feed        - the source feed
// o $summary     - summary of activity
// o $content     - HTML content of activity
// o $id          - ID of the activity
// o $title       - title of the activity
// o $categories  - list of AtomCategory objects
// o $enclosures  - list of enclosure URL references
// o $attachments - list of attachments
// o $extra       - extra elements as array(tag, attrs, content)
// o $source      - ActivitySource object representing 'home feed'
// o $selfLink    - <link rel='self' type='application/atom+xml'>
// o $editLink    - <link rel='edit' type='application/atom+xml'>
// o $generator   - ActivityObject representing the generating application
class Activity
{
   const SPEC   = 'http://activitystrea.ms/spec/1.0/';
   const SCHEMA = 'http://activitystrea.ms/schema/1.0/';
   const MEDIA  = 'http://purl.org/syndication/atommedia';

   const VERB       = 'verb';
   const OBJECT     = 'object';
   const ACTOR      = 'actor';
   const SUBJECT    = 'subject';
   const OBJECTTYPE = 'object-type';
   const CONTEXT    = 'context';
   const TARGET     = 'target';

   const ATOM = 'http://www.w3.org/2005/Atom';

   const AUTHOR    = 'author';
   const PUBLISHED = 'published';
   const UPDATED   = 'updated';

   const RSS = null; // no namespace!

   const PUBDATE     = 'pubDate';
   const DESCRIPTION = 'description';
   const GUID        = 'guid';
   const SELF        = 'self';
   const IMAGE       = 'image';
   const URL         = 'url';

   const DC = 'http://purl.org/dc/elements/1.1/';

   const CREATOR = 'creator';

   const CONTENTNS = 'http://purl.org/rss/1.0/modules/content/';
   const ENCODED = 'encoded';

   public $actor;   // an ActivityObject
   public $verb;    // a string (the URL)
   public $objects = array();  // an array of ActivityObjects
   public $target;  // an ActivityObject
   public $context; // an ActivityObject
   public $time;    // Time of the activity
   public $link;    // an ActivityObject
   public $entry;   // the source entry
   public $feed;    // the source feed

   public $summary; // summary of activity
   public $content; // HTML content of activity
   public $id;      // ID of the activity
   public $title;   // title of the activity
   public $categories = array(); // list of AtomCategory objects
   public $enclosures = array(); // list of enclosure URL references
   public $attachments = array(); // list of attachments

   public $extra = array(); // extra elements as array(tag, attrs, content)
   public $source;  // ActivitySource object representing 'home feed'
   public $selfLink; // <link rel='self' type='application/atom+xml'>
   public $editLink; // <link rel='edit' type='application/atom+xml'>
   public $generator; // ActivityObject representing the generating application
   

   // -------------------------------------------------------------------------
   // Function: __construct
   // Turns a regular old Atom <entry> into a magical activity
   //
   // Parameters:
   // o DOMElement $entry Atom entry to poke at
   // o DOMElement $feed  Atom feed, for context
   function __construct($entry = null, $feed = null) {
      if (is_null($entry)) {
            return;
      }

      // Insist on a feed's root DOMElement; don't allow a DOMDocument
      if ($feed instanceof DOMDocument) {
            throw new ClientException(
                // TRANS: Client exception thrown when a feed instance is a DOMDocument.
                _('Expecting a root feed element but got a whole XML document.')
            );
      }
      $this->entry = $entry;
      $this->feed  = $feed;
      if ($entry->namespaceURI == Activity::ATOM && $entry->localName == 'entry') {
         $this->_fromAtomEntry($entry, $feed);
      } else if ($entry->namespaceURI == Activity::RSS && $entry->localName == 'item') {
         $this->_fromRssItem($entry, $feed);
      } else if ($entry->namespaceURI == Activity::SPEC && $entry->localName == 'object') {
         $this->_fromAtomEntry($entry, $feed);
      } else {
         // Low level exception. No need for i18n.
         throw new Exception("Unknown DOM element: {$entry->namespaceURI} {$entry->localName}");
      }
   }


   // -------------------------------------------------------------------------
   // Function: _fromAtomEntry
   //
   // Parameters:
   // o entry
   // o feed
   function _fromAtomEntry($entry, $feed) {
      $pubEl = $this->_child($entry, self::PUBLISHED, self::ATOM);
      if (!empty($pubEl)) {
         $this->time = strtotime($pubEl->textContent);
      } else {
         // XXX technically an error; being liberal. Good idea...?
         $updateEl = $this->_child($entry, self::UPDATED, self::ATOM);
         if (!empty($updateEl)) {
            $this->time = strtotime($updateEl->textContent);
         } else {
            $this->time = null;
         }
      }
      $this->link = ActivityUtils::getPermalink($entry);
      $verbEl = $this->_child($entry, self::VERB);
      if (!empty($verbEl)) {
         $this->verb = trim($verbEl->textContent);
      } else {
         $this->verb = ActivityVerb::POST;
         // XXX: do other implied stuff here
      }
      // get immediate object children
      $objectEls = ActivityUtils::children($entry, self::OBJECT, self::SPEC);
      if (count($objectEls) > 0) {
         foreach ($objectEls as $objectEl) {
            // Special case for embedded activities
            $objectType = ActivityUtils::childContent($objectEl, self::OBJECTTYPE, self::SPEC);
            if ((!empty($objectType) && $objectType == ActivityObject::ACTIVITY) || $this->verb == ActivityVerb::SHARE) {
               $this->objects[] = new Activity($objectEl);
            } else {
               $this->objects[] = new ActivityObject($objectEl);
            }
         }
      } else {
         // XXX: really?
         $this->objects[] = new ActivityObject($entry);
      }
      $actorEl = $this->_child($entry, self::ACTOR);
      if (!empty($actorEl)) {
         // Standalone <activity:actor> elements are a holdover from older
         // versions of ActivityStreams. Newer feeds should have this data
         // integrated straight into <atom:author>.
         $this->actor = new ActivityObject($actorEl);
         // Cliqset has bad actor IDs (just nickname of user). We
         // work around it by getting the author data and using its
         // id instead
         if (!preg_match('/^\w+:/', $this->actor->id)) {
            $authorEl = ActivityUtils::child($entry, 'author');
            if (!empty($authorEl)) {
               $authorObj = new ActivityObject($authorEl);
               $this->actor->id = $authorObj->id;
            }
         }
      } else if ($authorEl = $this->_child($entry, self::AUTHOR, self::ATOM)) {
         // An <atom:author> in the entry overrides any author info on
         // the surrounding feed.
         $this->actor = new ActivityObject($authorEl);
      } else if (!empty($feed) &&
                   $subjectEl = $this->_child($feed, self::SUBJECT)) {
         // Feed subject is used for things like groups.
         // Should actually possibly not be interpreted as an actor...?
         $this->actor = new ActivityObject($subjectEl);
      } else if (!empty($feed) && $authorEl = $this->_child($feed, self::AUTHOR,
                                                              self::ATOM)) {
         // If there's no <atom:author> on the entry, it's safe to assume
         // the containing feed's authorship info applies.
         $this->actor = new ActivityObject($authorEl);
      }
      $contextEl = $this->_child($entry, self::CONTEXT);
      if (!empty($contextEl)) {
         $this->context = new ActivityContext($contextEl);
      } else {
         $this->context = new ActivityContext($entry);
      }
      $targetEl = $this->_child($entry, self::TARGET);
      if (!empty($targetEl)) {
         $this->target = new ActivityObject($targetEl);
      } elseif (ActivityUtils::compareVerbs($this->verb, array(ActivityVerb::FAVORITE))) {
         // StatusNet didn't send a 'target' for their Favorite atom entries
         $this->target = clone($this->objects[0]);
      }
      $this->summary = ActivityUtils::childContent($entry, 'summary');
      $this->id      = ActivityUtils::childContent($entry, 'id');
      $this->content = ActivityUtils::getContent($entry);
      $catEls = $entry->getElementsByTagNameNS(self::ATOM, 'category');
      if ($catEls) {
         for ($i = 0; $i < $catEls->length; $i++) {
            $catEl = $catEls->item($i);
            $this->categories[] = new AtomCategory($catEl);
         }
      }
      foreach (ActivityUtils::getLinks($entry, 'enclosure') as $link) {
         $this->enclosures[] = $link->getAttribute('href');
      }
      // From APP. Might be useful.
      $this->selfLink = ActivityUtils::getLink($entry, 'self', 'application/atom+xml');
      $this->editLink = ActivityUtils::getLink($entry, 'edit', 'application/atom+xml');
   }


   // -------------------------------------------------------------------------
   // Function: _fromRssItem
   //
   // Parameters:
   // o item
   // o channel
   function _fromRssItem($item, $channel) {
      $verbEl = $this->_child($item, self::VERB);
      if (!empty($verbEl)) {
         $this->verb = trim($verbEl->textContent);
      } else {
         $this->verb = ActivityVerb::POST;
         // XXX: do other implied stuff here
      }
      $pubDateEl = $this->_child($item, self::PUBDATE, self::RSS);
      if (!empty($pubDateEl)) {
         $this->time = strtotime($pubDateEl->textContent);
      }
      if ($authorEl = $this->_child($item, self::AUTHOR, self::RSS)) {
         $this->actor = ActivityObject::fromRssAuthor($authorEl);
      } else if ($dcCreatorEl = $this->_child($item, self::CREATOR, self::DC)) {
         $this->actor = ActivityObject::fromDcCreator($dcCreatorEl);
      } else if ($posterousEl = $this->_child($item, ActivityObject::AUTHOR, ActivityObject::POSTEROUS)) {
         // Special case for Posterous.com
         $this->actor = ActivityObject::fromPosterousAuthor($posterousEl);
      } else if (!empty($channel)) {
         $this->actor = ActivityObject::fromRssChannel($channel);
      } else {
         // No actor!
      }
      $this->title = ActivityUtils::childContent($item, ActivityObject::TITLE, self::RSS);
      $contentEl = ActivityUtils::child($item, self::ENCODED, self::CONTENTNS);
      if (!empty($contentEl)) {
         // <content:encoded> XML node's text content is HTML; no further processing needed.
         $this->content = $contentEl->textContent;
      } else {
         $descriptionEl = ActivityUtils::child($item, self::DESCRIPTION, self::RSS);
         if (!empty($descriptionEl)) {
            // Per spec, <description> must be plaintext.
            // In practice, often there's HTML... but these days good
            // feeds are using <content:encoded> which is explicitly
            // real HTML.
            // We'll treat this following spec, and do HTML escaping
            // to convert from plaintext to HTML.
            $this->content = htmlspecialchars($descriptionEl->textContent);
         }
      }
      $this->link = ActivityUtils::childContent($item, ActivityUtils::LINK, self::RSS);
      // @fixme enclosures
      // @fixme thumbnails... maybe
      $guidEl = ActivityUtils::child($item, self::GUID, self::RSS);
      if (!empty($guidEl)) {
         $this->id = $guidEl->textContent;
         if ($guidEl->hasAttribute('isPermaLink') && $guidEl->getAttribute('isPermaLink') != 'false') {
            // overwrites <link>
            $this->link = $this->id;
         }
      }
      $this->objects[] = new ActivityObject($item);
      $this->context   = new ActivityContext($item);
   }


   // -------------------------------------------------------------------------
   // Function: toAtomEntry
   // In theory, returns an Atom <entry> based on this activity.
   //
   // In reality, a stub/null function.
   function toAtomEntry() {
      return null;
   }


   // -------------------------------------------------------------------------
   // Function: asArray
   // Returns an array based on this activity suitable
   // for encoding as a JSON object
   //
   // Returns:
   // o array $activity
   function asArray() {
      $activity = array();
      // actor
      $activity['actor'] = $this->actor->asArray();
      // content
      $activity['content'] = $this->content;
      // generator
      if (!empty($this->generator)) {
         $activity['generator'] = $this->generator->asArray();
      }
      // icon <-- possibly a mini object representing verb?
      // id
      $activity['id'] = $this->id;
      // object
      if (count($this->objects) == 0) {
         common_log(LOG_ERR, "Can't save " . $this->id);
      } else {
         if (count($this->objects) > 1) {
            common_log(LOG_WARNING, "Ignoring " . (count($this->objects) - 1) . " extra objects in JSON output for activity " . $this->id);
         }
         $object = $this->objects[0];
         if ($object instanceof Activity) {
            // Sharing a post activity is more like sharing the original object
            if (ActivityVerb::canonical($this->verb) == ActivityVerb::canonical(ActivityVerb::SHARE) &&
                    ActivityVerb::canonical($object->verb) == ActivityVerb::canonical(ActivityVerb::POST)) {
               // XXX: Here's one for the obfuscation record books
               $object = $object->objects[0];
            }
         }
         $activity['object'] = $object->asArray();
         if ($object instanceof Activity) {
            $activity['object']['objectType'] = 'activity';
         }
         foreach ($this->attachments as $attachment) {
            if (empty($activity['object']['attachments'])) {
               $activity['object']['attachments'] = array();
            }
            $activity['object']['attachments'][] = $attachment->asArray();
         }
      }
      // Context stuff.
      if (!empty($this->context)) {
         if (!empty($this->context->location)) {
            $loc = $this->context->location;
            $activity['location'] = array(
                    'objectType' => 'place',
                    'position' => sprintf("%+02.5F%+03.5F/", $loc->lat, $loc->lon),
                    'lat' => $loc->lat,
                    'lon' => $loc->lon);
            $name = $loc->getName();
            if ($name) {
               $activity['location']['displayName'] = $name;
            }
            $url = $loc->getURL();
            if ($url) {
               $activity['location']['url'] = $url;
            }
         }
         $activity['to'] = $this->context->getToArray();
         $ctxarr         = $this->context->asArray();
         if (array_key_exists('inReplyTo', $ctxarr)) {
            $activity['object']['inReplyTo'] = $ctxarr['inReplyTo'];
            unset($ctxarr['inReplyTo']);
         }
         if (!array_key_exists('status_net', $activity)) {
            $activity['status_net'] = array();
         }
         foreach ($ctxarr as $key => $value) {
            $activity['status_net'][$key] = $value;
         }
      }
      // published
      $activity['published'] = self::iso8601Date($this->time);
      // provider
      $provider = array(
         'objectType' => 'service',
         'displayName' => common_config('site', 'name'),
         'url' => common_root_url());
      $activity['provider'] = $provider;
      // target
      if (!empty($this->target)) {
         $activity['target'] = $this->target->asArray();
      }
      // title
      $activity['title'] = $this->title;
      // updated <-- Optional. Should we use this to indicate the time we
      //             received a remote notice? Probably not.
      // verb
      $activity['verb'] = ActivityVerb::canonical($this->verb);
      // url
      if ($this->link) {
         $activity['url'] = $this->link;
      }

      /* Purely extensions hereafter */

      if ($activity['verb'] == 'post') {
         $tags = array();
         foreach ($this->categories as $cat) {
            if (mb_strlen($cat->term) > 0) {
               // Couldn't figure out which object type to use, so...
               $tags[] = array('objectType' => 'http://activityschema.org/object/hashtag',
                               'displayName' => $cat->term);
            }
         }
         if (count($tags) > 0) {
            $activity['object']['tags'] = $tags;
         }
      }

      // XXX: a bit of a hack... Since JSON isn't namespaced we probably
      // shouldn't be using 'statusnet:notice_info', but this will work
      // for the moment.

      foreach ($this->extra as $e) {
         list($objectName, $props, $txt) = $e;
         if (!empty($objectName)) {
            $parts = explode(":", $objectName);
            if (count($parts) == 2 && $parts[0] == "statusnet") {
               if (!array_key_exists('status_net', $activity)) {
                  $activity['status_net'] = array();
               }
               $activity['status_net'][$parts[1]] = $props;
            } else {
               $activity[$objectName] = $props;
            }
         }
      }
      return array_filter($activity);
   }


   // -------------------------------------------------------------------------
   // Function: asString
   //
   // Parameters:
   // o namespace - default false
   // o author    - default true
   // o source    - default true
   //
   // Returns:
   // o string
   function asString($namespace=false, $author=true, $source=false) {
      $xs = new XMLStringer(true);
      $this->outputTo($xs, $namespace, $author, $source);
      return $xs->getString();
   }


   // -------------------------------------------------------------------------
   // Function: outputTo
   // Dumps the Activity into an XML stream
   //
   // Parameters:
   // o xs        - XML stream object
   // o namespace - boolean, default false
   // o author    - boolean, default true
   // o source    - boolean, default false
   // o tag       - string, default 'entry'
   //
   // Returns:
   // o void
   function outputTo($xs, $namespace=false, $author=true, $source=false, $tag='entry')
   {
      if ($namespace) {
         $attrs = array('xmlns' => 'http://www.w3.org/2005/Atom',
                        'xmlns:thr' => 'http://purl.org/syndication/thread/1.0',
                        'xmlns:activity' => 'http://activitystrea.ms/spec/1.0/',
                        'xmlns:georss' => 'http://www.georss.org/georss',
                        'xmlns:ostatus' => 'http://ostatus.org/schema/1.0',
                        'xmlns:poco' => 'http://portablecontacts.net/spec/1.0',
                        'xmlns:media' => 'http://purl.org/syndication/atommedia',
                        'xmlns:statusnet' => 'http://status.net/schema/api/1/');
      } else {
         $attrs = array();
      }
      $xs->elementStart($tag, $attrs);
      if ($tag != 'entry') {
         $xs->element('activity:object-type', null, ActivityObject::ACTIVITY);
      }
      if ($this->verb == ActivityVerb::POST && count($this->objects) == 1 && $tag == 'entry') {
         $obj = $this->objects[0];
			$obj->outputTo($xs, null);
      } else {
         $xs->element('id', null, $this->id);
         if ($this->title) {
            $xs->element('title', null, $this->title);
         } else {
            // Require element
            $xs->element('title', null, "");
         }
         $xs->element('content', array('type' => 'html'), $this->content);
         if (!empty($this->summary)) {
            $xs->element('summary', null, $this->summary);
         }
         if (!empty($this->link)) {
            $xs->element('link', array('rel' => 'alternate',
                                           'type' => 'text/html',
                                           'href' => $this->link));
         }
      }
      $xs->element('activity:verb', null, $this->verb);
      $published = self::iso8601Date($this->time);
      $xs->element('published', null, $published);
      $xs->element('updated', null, $published);
      if ($author) {
         $this->actor->outputTo($xs, 'author');
      }
      if ($this->verb != ActivityVerb::POST || count($this->objects) != 1 || $tag != 'entry') {
         foreach($this->objects as $object) {
            if ($object instanceof Activity) {
               $object->outputTo($xs, false, true, true, 'activity:object');
            } else {
               $object->outputTo($xs, 'activity:object');
            }
         }
      }
      if (!empty($this->context)) {
         if (!empty($this->context->replyToID)) {
            if (!empty($this->context->replyToUrl)) {
               $xs->element('thr:in-reply-to',
                      array('ref' => $this->context->replyToID,
                            'href' => $this->context->replyToUrl));
            } else {
                    $xs->element('thr:in-reply-to',
                           array('ref' => $this->context->replyToID));
            }
         }
         if (!empty($this->context->replyToUrl)) {
            $xs->element('link', array('rel' => 'related',
                                       'href' => $this->context->replyToUrl));
         }
         if (!empty($this->context->conversation)) {
            $convattr = [];
            $conv = Conversation::getKV('uri', $this->context->conversation);
            if ($conv instanceof Conversation) {
               $convattr['href'] = $conv->getUrl();
               $convattr['local_id'] = $conv->getID();
               $convattr['ref'] = $conv->getUri();
               $xs->element('link', array('rel' => ActivityContext::CONVERSATION,
                                          'href' => $convattr['href']));
            } else {
               $convattr['ref'] = $this->context->conversation;
            }
            $xs->element(ActivityContext::CONVERSATION,
                                $convattr,
                                $this->context->conversation);
            /* Since we use XMLWriter we just use the previously hardcoded prefix for ostatus,
               otherwise we should use something like this:
               $xs->elementNS(array(ActivityContext::OSTATUS => 'ostatus'),    // namespace
                                'conversation',  // tag (or the element name from ActivityContext::CONVERSATION)
                                null,   // attributes
                                $this->context->conversation);  // content
             */
         }
         foreach ($this->context->attention as $attnURI=>$type) {
            $xs->element('link', array('rel' => ActivityContext::MENTIONED,
                                        ActivityContext::OBJECTTYPE => $type,  // FIXME: undocumented
                                        'href' => $attnURI));
         }
         if (!empty($this->context->location)) {
            $loc = $this->context->location;
            $xs->element('georss:point', null, $loc->lat . ' ' . $loc->lon);
         }
      }

      if ($this->target) {
         $this->target->outputTo($xs, 'activity:target');
      }
      foreach ($this->categories as $cat) {
         $cat->outputTo($xs);
      }
      // can be either URLs or enclosure objects
      foreach ($this->enclosures as $enclosure) {
         if (is_string($enclosure)) {
                $xs->element('link', array('rel' => 'enclosure',
                                           'href' => $enclosure));
         } else {
                $attributes = array('rel' => 'enclosure',
                                    'href' => $enclosure->url,
                                    'type' => $enclosure->mimetype,
                                    'length' => $enclosure->size);
                if ($enclosure->title) {
                    $attributes['title'] = $enclosure->title;
                }
                $xs->element('link', $attributes);
         }
      }
      // Info on the source feed
      if ($source && !empty($this->source)) {
         $xs->elementStart('source');
         $xs->element('id', null, $this->source->id);
         $xs->element('title', null, $this->source->title);
         if (array_key_exists('alternate', $this->source->links)) {
            $xs->element('link', array('rel' => 'alternate',
                                       'type' => 'text/html',
                                       'href' => $this->source->links['alternate']));
         }
         if (array_key_exists('self', $this->source->links)) {
            $xs->element('link', array('rel' => 'self',
                                       'type' => 'application/atom+xml',
                                       'href' => $this->source->links['self']));
         }
         if (array_key_exists('license', $this->source->links)) {
            $xs->element('link', array('rel' => 'license',
                                       'href' => $this->source->links['license']));
         }
         if (!empty($this->source->icon)) {
            $xs->element('icon', null, $this->source->icon);
         }
         if (!empty($this->source->updated)) {
            $xs->element('updated', null, $this->source->updated);
         }
         $xs->elementEnd('source');
      }
      if (!empty($this->selfLink)) {
         $xs->element('link', array('rel' => 'self',
                                    'type' => 'application/atom+xml',
                                    'href' => $this->selfLink));
      }
      if (!empty($this->editLink)) {
         $xs->element('link', array('rel' => 'edit',
                                    'type' => 'application/atom+xml',
                                    'href' => $this->editLink));
      }

      // For throwing in extra elements; used for statusnet:notice_info
      foreach ($this->extra as $el) {
         list($tag, $attrs, $content) = $el;
         $xs->element($tag, $attrs, $content);
      }
      $xs->elementEnd($tag);
      return;
   }


   // -------------------------------------------------------------------------
   // Function: _child
   // Easy creation of a child class
   private function _child($element, $tag, $namespace=self::SPEC) {
      return ActivityUtils::child($element, $tag, $namespace);
   }


   // Function: iso8601Date
   // For consistency, we'll always output UTC rather than local time.
   // Note that clients *should* accept any timezone we give them as long
   // as it's properly formatted.
   //
   // MB footnote: "should" doesn't mean "actually will"
   //
   // Parameter:
   // o int $tm Unix timestamp
   //
   // Returns:
   // o string
    static function iso8601Date($tm)
    {
        $dateStr = date('d F Y H:i:s', $tm);
        $d = new DateTime($dateStr, new DateTimeZone('UTC'));
        return $d->format('c');
    }
}

// END OF FILE
// ============================================================================
?>