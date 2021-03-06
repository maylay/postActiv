<?php
/* ============================================================================
 * Title: ActivityObject
 * Class abstraction for the actual Activity object
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
 *  o Brion Vibber <brion@pobox.com>
 *  o Zach Copley
 *  o Siebrand Mazeland <s.mazeland@xs4all.nl>
 *  o Sashi Gowda <connect2shashi@gmail.com>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Joshua Judson Rosen <rozzin@geekspace.com>
 *  o Stephen Paul Weber <singpolyma@singpolyma.net>
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
// Class: ActivityObject
// A noun-ish thing in the activity universe
//
// The activity streams spec talks about activity objects, while also having
// a tag activity:object, which is in fact an activity object. Aaaaaah!
//
// This is just a thing in the activity universe. Can be the subject, object,
// or indirect object (target!) of an activity verb. Rotten name, and we're
// propagating it. *sigh*
//
// Defines:
// Constants for the schemas
// o ARTICLE     - 'http://activitystrea.ms/schema/1.0/article';
// o BLOGENTRY   - 'http://activitystrea.ms/schema/1.0/blog-entry';
// o NOTE        - 'http://activitystrea.ms/schema/1.0/note';
// o STATUS      - 'http://activitystrea.ms/schema/1.0/status';
// o FILE        - 'http://activitystrea.ms/schema/1.0/file';
// o PHOTO       - 'http://activitystrea.ms/schema/1.0/photo';
// o ALBUM       - 'http://activitystrea.ms/schema/1.0/photo-album';
// o PLAYLIST    - 'http://activitystrea.ms/schema/1.0/playlist';
// o VIDEO       - 'http://activitystrea.ms/schema/1.0/video';
// o AUDIO       - 'http://activitystrea.ms/schema/1.0/audio';
// o BOOKMARK    - 'http://activitystrea.ms/schema/1.0/bookmark';
// o PERSON      - 'http://activitystrea.ms/schema/1.0/person';
// o GROUP       - 'http://activitystrea.ms/schema/1.0/group';
// o _LIST       - 'http://activitystrea.ms/schema/1.0/list'; // LIST is reserved
// o PLACE       - 'http://activitystrea.ms/schema/1.0/place';
// o COMMENT     - 'http://activitystrea.ms/schema/1.0/comment';
// o ACTIVITY    - 'http://activitystrea.ms/schema/1.0/activity';
// o SERVICE     - 'http://activitystrea.ms/schema/1.0/service';
// o IMAGE       - 'http://activitystrea.ms/schema/1.0/image';
// o COLLECTION  - 'http://activitystrea.ms/schema/1.0/collection';
// o APPLICATION - 'http://activitystrea.ms/schema/1.0/application';
//
// ATOM feed bits we look for
// o TITLE   - 'title';
// o SUMMARY - 'summary';
// o ID      - 'id';
// o SOURCE  - 'source';
// o NAME    - 'name';
// o URI     - 'uri';
// o EMAIL   - 'email';
//
// Posterous stuff
// o POSTEROUS   - 'http://posterous.com/help/rss/1.0';
// o AUTHOR      - 'author';
// o USERIMAGE   - 'userImage';
// o PROFILEURL  - 'profileUrl';
// o NICKNAME    - 'nickName';
// o DISPLAYNAME - 'displayName';
class ActivityObject
{
   const ARTICLE   = 'http://activitystrea.ms/schema/1.0/article';
   const BLOGENTRY = 'http://activitystrea.ms/schema/1.0/blog-entry';
   const NOTE      = 'http://activitystrea.ms/schema/1.0/note';
   const STATUS    = 'http://activitystrea.ms/schema/1.0/status';
   const FILE      = 'http://activitystrea.ms/schema/1.0/file';
   const PHOTO     = 'http://activitystrea.ms/schema/1.0/photo';
   const ALBUM     = 'http://activitystrea.ms/schema/1.0/photo-album';
   const PLAYLIST  = 'http://activitystrea.ms/schema/1.0/playlist';
   const VIDEO     = 'http://activitystrea.ms/schema/1.0/video';
   const AUDIO     = 'http://activitystrea.ms/schema/1.0/audio';
   const BOOKMARK  = 'http://activitystrea.ms/schema/1.0/bookmark';
   const PERSON    = 'http://activitystrea.ms/schema/1.0/person';
   const GROUP     = 'http://activitystrea.ms/schema/1.0/group';
   const _LIST     = 'http://activitystrea.ms/schema/1.0/list'; // LIST is reserved
   const PLACE     = 'http://activitystrea.ms/schema/1.0/place';
   const COMMENT   = 'http://activitystrea.ms/schema/1.0/comment';
   // ^^^^^^^^^^ tea!
   const ACTIVITY    = 'http://activitystrea.ms/schema/1.0/activity';
   const SERVICE     = 'http://activitystrea.ms/schema/1.0/service';
   const IMAGE       = 'http://activitystrea.ms/schema/1.0/image';
   const COLLECTION  = 'http://activitystrea.ms/schema/1.0/collection';
   const APPLICATION = 'http://activitystrea.ms/schema/1.0/application';

   // Atom elements we snarf
   const TITLE   = 'title';
   const SUMMARY = 'summary';
   const ID      = 'id';
   const SOURCE  = 'source';
   const NAME    = 'name';
   const URI     = 'uri';
   const EMAIL   = 'email';

   const POSTEROUS   = 'http://posterous.com/help/rss/1.0';
   const AUTHOR      = 'author';
   const USERIMAGE   = 'userImage';
   const PROFILEURL  = 'profileUrl';
   const NICKNAME    = 'nickName';
   const DISPLAYNAME = 'displayName';

   // @todo move this stuff to it's own PHOTO activity object
   const MEDIA_DESCRIPTION = 'description';

   public $element;
   public $type;
   public $id;
   public $title;
   public $summary;
   public $content;
   public $owner;
   public $link;
   public $source;
   public $avatarLinks = array();
   public $geopoint;
   public $poco;
   public $displayName;

   public $thumbnail;
   public $largerImage;
   public $description;
   public $extra = array();

   public $stream;


   // -------------------------------------------------------------------------
   // Function: __construct
   // Class constructor
   //
   // This probably needs to be refactored
   // to generate a local class (ActivityPerson, ActivityFile, ...)
   // based on the object type.
   //
   // Parameters:
   // o DOMElement $element DOM thing to turn into an Activity thing
   function __construct($element = null) {
      if (empty($element)) {
         return;
      }

      $this->element = $element;
      $this->geopoint = $this->_childContent(
         $element,
         ActivityContext::POINT,
         ActivityContext::GEORSS
      );

      if ($element->tagName == 'author') {
         $this->_fromAuthor($element);
      } else if ($element->tagName == 'item') {
         $this->_fromRssItem($element);
      } else {
         $this->_fromAtomEntry($element);
      }

      // Some per-type attributes...
      if ($this->type == self::PERSON || $this->type == self::GROUP) {
         $this->displayName = $this->title;
         $photos = ActivityUtils::getLinks($element, 'photo');
         if (count($photos)) {
            foreach ($photos as $link) {
               $this->avatarLinks[] = new AvatarLink($link);
            }
         } else {
            $avatars = ActivityUtils::getLinks($element, 'avatar');
            foreach ($avatars as $link) {
               $this->avatarLinks[] = new AvatarLink($link);
            }
         }

         $this->poco = new PoCo($element);
      }

      if ($this->type == self::PHOTO) {
         $this->thumbnail   = ActivityUtils::getLink($element, 'preview');
         $this->largerImage = ActivityUtils::getLink($element, 'enclosure');
         $this->description = ActivityUtils::childContent(
            $element,
            ActivityObject::MEDIA_DESCRIPTION,
            Activity::MEDIA
         );
      }
      if ($this->type == self::_LIST) {
         $owner = ActivityUtils::child($this->element, Activity::AUTHOR, Activity::SPEC);
         $this->owner = new ActivityObject($owner);
      }
   }


   // -------------------------------------------------------------------------
   // Function: _fromAuthor
   // Create an activity object given an element
   //
   // Parameters:
   // o element
   private function _fromAuthor($element) {
      $this->type = $this->_childContent($element,
                                         Activity::OBJECTTYPE,
                                         Activity::SPEC);

      if (empty($this->type)) {
         $this->type = self::PERSON; // XXX: is this fair?
      }

      // Start with <poco::displayName>
      $this->title = ActivityUtils::childContent($element, PoCo::DISPLAYNAME, PoCo::NS);

      // try falling back to <atom:title>
      if (empty($this->title)) {
         $title = ActivityUtils::childHtmlContent($element, self::TITLE);
         if (!empty($title)) {
            $this->title = common_strip_html($title);
         }
      }

      // fall back to <atom:name> as a last resort
      if (empty($this->title)) {
         $this->title = $this->_childContent($element, self::NAME);
      }

      // start with <atom:id>
      $this->id = $this->_childContent($element, self::ID);

      // fall back to <atom:uri>
      if (empty($this->id)) {
         $this->id = $this->_childContent($element, self::URI);
      }

      // fall further back to <atom:email>
      if (empty($this->id)) {
         $email = $this->_childContent($element, self::EMAIL);
         if (!empty($email)) {
            // XXX: acct: ?
            $this->id = 'mailto:'.$email;
         }
      }

      $this->link = ActivityUtils::getPermalink($element);

      // fall finally back to <link rel=alternate>
      if (empty($this->id) && !empty($this->link)) { // fallback if there's no ID
         $this->id = $this->link;
      }
   }


   // -------------------------------------------------------------------------
   // Function: _fromAtomEntry
   // Create an activity object given an element
   //
   // Parameters:
   // o element
   private function _fromAtomEntry($element) {
      $this->type = $this->_childContent($element, Activity::OBJECTTYPE,
                                         Activity::SPEC);

      if (empty($this->type)) {
         $this->type = ActivityObject::NOTE;
      }

      $this->summary = ActivityUtils::childHtmlContent($element, self::SUMMARY);
      $this->content = ActivityUtils::getContent($element);
      // We don't like HTML in our titles, although it's technically allowed
      $this->title = common_strip_html(ActivityUtils::childHtmlContent($element, self::TITLE));
      $this->source  = $this->_getSource($element);
      $this->link = ActivityUtils::getPermalink($element);
      $this->id = $this->_childContent($element, self::ID);

      if (empty($this->id) && !empty($this->link)) { // fallback if there's no ID
         $this->id = $this->link;
      }
   }

   // -------------------------------------------------------------------------
   // Function: _fromRssItem
   // Create an activity object from a RSS item
   //
   // Parameters:
   // o item
   //
   // Todo:
   // o rationalize with Activity::_fromRssItem()
   private function _fromRssItem($item)
   {
      if (empty($this->type)) {
         $this->type = ActivityObject::NOTE;
      }

      $this->title = ActivityUtils::childContent($item, ActivityObject::TITLE, Activity::RSS);
      $contentEl = ActivityUtils::child($item, ActivityUtils::CONTENT, Activity::CONTENTNS);
      if (!empty($contentEl)) {
         $this->content = htmlspecialchars_decode($contentEl->textContent, ENT_QUOTES);
      } else {
         $descriptionEl = ActivityUtils::child($item, Activity::DESCRIPTION, Activity::RSS);
         if (!empty($descriptionEl)) {
            $this->content = htmlspecialchars_decode($descriptionEl->textContent, ENT_QUOTES);
         }
      }

      $this->link = ActivityUtils::childContent($item, ActivityUtils::LINK, Activity::RSS);
      $guidEl = ActivityUtils::child($item, Activity::GUID, Activity::RSS);
      if (!empty($guidEl)) {
         $this->id = $guidEl->textContent;

         if ($guidEl->hasAttribute('isPermaLink') && $guidEl->getAttribute('isPermaLink') != 'false') {
            // overwrites <link>
            $this->link = $this->id;
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: fromRssAuthor
   //
   // Parameters:
   // o el
   //
   // Returns:
   // o ActivityObject object
   public static function fromRssAuthor($el) {
      $text = $el->textContent;
      if (preg_match('/^(.*?) \((.*)\)$/', $text, $match)) {
         $email = $match[1];
         $name = $match[2];
      } else if (preg_match('/^(.*?) <(.*)>$/', $text, $match)) {
         $name = $match[1];
         $email = $match[2];
      } else if (preg_match('/.*@.*/', $text)) {
         $email = $text;
         $name = null;
      } else {
         $name = $text;
         $email = null;
      }

      // Not really enough info
      $obj = new ActivityObject();
      $obj->element = $el;
      $obj->type  = ActivityObject::PERSON;
      $obj->title = $name;
      if (!empty($email)) {
         $obj->id = 'mailto:'.$email;
      }
      return $obj;
   }


   // -------------------------------------------------------------------------
   // Function: fromDcCreator
   //
   // Parameters:
   // o el
   //
   // Returns:
   // o ActivityObject
   public static function fromDcCreator($el) {
      // Not really enough info
      $text = $el->textContent;
      
      $obj = new ActivityObject();
      $obj->element = $el;
      $obj->title = $text;
      $obj->type  = ActivityObject::PERSON;
      return $obj;
   }


   // -------------------------------------------------------------------------
   // Function: fromRssChannel
   //
   // Parameters:
   // o el
   //
   // Returns:
   // o ActivityObject
   public static function fromRssChannel($el) {
      $obj = new ActivityObject();
      $obj->element = $el;
      $obj->type = ActivityObject::PERSON; // @fixme guess better
      $obj->title = ActivityUtils::childContent($el, ActivityObject::TITLE, Activity::RSS);
      $obj->link  = ActivityUtils::childContent($el, ActivityUtils::LINK, Activity::RSS);
      $obj->id    = ActivityUtils::getLink($el, Activity::SELF);
      if (empty($obj->id)) {
         $obj->id = $obj->link;
      }
      $desc = ActivityUtils::childContent($el, Activity::DESCRIPTION, Activity::RSS);
      if (!empty($desc)) {
         $obj->content = htmlspecialchars_decode($desc, ENT_QUOTES);
      }
      $imageEl = ActivityUtils::child($el, Activity::IMAGE, Activity::RSS);
      if (!empty($imageEl)) {
         $url = ActivityUtils::childContent($imageEl, Activity::URL, Activity::RSS);
         $al = new AvatarLink();
         $al->url = $url;
         $obj->avatarLinks[] = $al;
      }
      return $obj;
   }


   // -------------------------------------------------------------------------
   // Function: fromPosterousAuthor
   //
   // Parameters:
   // o el
   //
   // Returns:
   // o ActivityObject obj
   public static function fromPosterousAuthor($el)
   {
      $obj = new ActivityObject();
      $obj->type = ActivityObject::PERSON; // @fixme any others...?
      $userImage = ActivityUtils::childContent($el, self::USERIMAGE, self::POSTEROUS);
      if (!empty($userImage)) {
         $al = new AvatarLink();
         $al->url = $userImage;
         $obj->avatarLinks[] = $al;
      }
      $obj->link = ActivityUtils::childContent($el, self::PROFILEURL, self::POSTEROUS);
      $obj->id   = $obj->link;
      $obj->poco = new PoCo();
      $obj->poco->preferredUsername = ActivityUtils::childContent($el, self::NICKNAME, self::POSTEROUS);
      $obj->poco->displayName       = ActivityUtils::childContent($el, self::DISPLAYNAME, self::POSTEROUS);
      $obj->title = $obj->poco->displayName;
      return $obj;
   }


   // -------------------------------------------------------------------------
   // Function: _childContent
   //
   // Parameters:
   // o element
   // o tag
   // o namespace
   //
   // Returns:
   // o content of child
   private function _childContent($element, $tag, $namespace=ActivityUtils::ATOM) {
      return ActivityUtils::childContent($element, $tag, $namespace);
   }


   // -------------------------------------------------------------------------
   // Function: _getSource
   // Try to get a unique id for the source feed
   //
   // Parameters:
   // o element
   // 
   // Returns:
   // o link to child if exists, NULL if not
   private function _getSource($element)
   {
      $sourceEl = ActivityUtils::child($element, 'source');
      if (empty($sourceEl)) {
            return null;
      } else {
         $href = ActivityUtils::getLink($sourceEl, 'self');
         if (!empty($href)) {
            return $href;
         } else {
            return ActivityUtils::childContent($sourceEl, 'id');
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: fromGroup
   // 
   // Parameters:
   // o User_group group
   //
   // Returns:
   // o ActivityObject object
   static function fromGroup(User_group $group) {
      $object = new ActivityObject();
      if (Event::handle('StartActivityObjectFromGroup', array($group, &$object))) {
         $object->type   = ActivityObject::GROUP;
         $object->id     = $group->getUri();
         $object->title  = $group->getBestName();
         $object->link   = $group->getUri();
         $object->avatarLinks[] = AvatarLink::fromFilename($group->homepage_logo, AVATAR_PROFILE_SIZE);
         $object->avatarLinks[] = AvatarLink::fromFilename($group->stream_logo, AVATAR_STREAM_SIZE);
         $object->avatarLinks[] = AvatarLink::fromFilename($group->mini_logo, AVATAR_MINI_SIZE);
         $object->poco = PoCo::fromGroup($group);
         Event::handle('EndActivityObjectFromGroup', array($group, &$object));
      }
      return $object;
   }


   // -------------------------------------------------------------------------
   // Function: fromPeopletag
   //
   // Parameters:
   // o tag ptag
   //
   // Returns:
   // o ActivityObject object
   static function fromPeopletag($ptag) {
      $object = new ActivityObject();
      if (Event::handle('StartActivityObjectFromPeopletag', array($ptag, &$object))) {
         $object->type    = ActivityObject::_LIST;
         $object->id      = $ptag->getUri();
         $object->title   = $ptag->tag;
         $object->summary = $ptag->description;
         $object->link    = $ptag->homeUrl();
         $object->owner   = Profile::getKV('id', $ptag->tagger);
         $object->poco    = PoCo::fromProfile($object->owner);
         Event::handle('EndActivityObjectFromPeopletag', array($ptag, &$object));
      }
      return $object;
   }


   // -------------------------------------------------------------------------
   // Function: fromFile
   //
   // Parameters:
   // o File file
   //
   // Returns:
   // o ActivityObject object
   static function fromFile(File $file) {
      $object = new ActivityObject();
      if (Event::handle('StartActivityObjectFromFile', array($file, &$object))) {
         $object->type = self::mimeTypeToObjectType($file->mimetype);
         $object->id   = TagURI::mint(sprintf("file:%d", $file->id));
         $object->link = $file->getAttachmentUrl();
         if ($file->title) {
            $object->title = $file->title;
         }
         if ($file->date) {
            $object->date = $file->date;
         }
         try {
            $thumbnail = $file->getThumbnail();
            $object->thumbnail = $thumbnail;
         } catch (UseFileAsThumbnailException $e) {
            $object->thumbnail = null;
         } catch (UnsupportedMediaException $e) {
            $object->thumbnail = null;
         }
         switch (self::canonicalType($object->type)) {
            case 'image':
               $object->largerImage = $file->getUrl();
               break;
            case 'video':
            case 'audio':
               $object->stream = $file->getUrl();
               break;
         }
         Event::handle('EndActivityObjectFromFile', array($file, &$object));
      }
      return $object;
   }


   // --------------------------------------------------------------------------
   // Function: fromNoticeSource
   //
   // Parameters:
   // o Notice_source source
   //
   // Returns:
   // o ActivityObject object
   static function fromNoticeSource(Notice_source $source)
   {
      $object = new ActivityObject();
      $wellKnown = array('web', 'xmpp', 'mail', 'omb', 'system', 'api', 'ostatus',
                         'activity', 'feed', 'mirror', 'twitter', 'facebook');

      if (Event::handle('StartActivityObjectFromNoticeSource', array($source, &$object))) {
         $object->type = ActivityObject::APPLICATION;
         if (in_array($source->code, $wellKnown)) {
            // We use one ID for all well-known StatusNet sources
            $object->id = "tag:status.net,2009:notice-source:".$source->code;
         } else if ($source->url) {
            // They registered with an URL
            $object->id = $source->url;
         } else {
            // Locally-registered, no URL
            $object->id = TagURI::mint("notice-source:".$source->code);
         }

         if ($source->url) {
            $object->link = $source->url;
         }
         if ($source->name) {
            $object->title = $source->name;
         } else {
            $object->title = $source->code;
         }
         if ($source->created) {
            $object->date = $source->created;
         }
         $object->extra[] = array('status_net', array('source_code' => $source->code));
         Event::handle('EndActivityObjectFromNoticeSource', array($source, &$object));
      }
      return $object;
   }


   // -------------------------------------------------------------------------
   // Function: fromMessage
   //
   // Parameters:
   // o Message message
   //
   // Returns:
   // o ActivityObject object
   static function fromMessage(Message $message) {
      $object = new ActivityObject();
      if (Event::handle('StartActivityObjectFromMessage', array($message, &$object))) {
         $object->type    = ActivityObject::NOTE;
         $object->id      = ($message->uri) ? $message->uri : (($message->url) ? $message->url : TagURI::mint(sprintf("message:%d", $message->id)));
         $object->content = $message->rendered;
         $object->date    = $message->created;
         if ($message->url) {
                $object->link = $message->url;
         } else {
                $object->link = common_local_url('showmessage', array('message' => $message->id));
         }
         $object->extra[] = array('status_net', array('message_id' => $message->id));
         Event::handle('EndActivityObjectFromMessage', array($message, &$object));
      }
      return $object;
   }


   // -------------------------------------------------------------------------
   // Function: outputTo
   //
   // Parameters:
   // o xo
   // o tag
   //
   // Returns: void
   function outputTo($xo, $tag='activity:object') {
      if (!empty($tag)) {
            $xo->elementStart($tag);
      }
      if (Event::handle('StartActivityObjectOutputAtom', array($this, $xo))) {
         $xo->element('activity:object-type', null, $this->type);
         // <author> uses URI
         if ($tag == 'author') {
            $xo->element(self::URI, null, $this->id);
         } else {
            $xo->element(self::ID, null, $this->id);
         }
         if (!empty($this->title)) {
            $name = common_xml_safe_str($this->title);
            if ($tag == 'author') {
               // XXX: Backward compatibility hack -- atom:name should contain
               // full name here, instead of nickname, i.e.: $name. Change
               // this in the next version.
               $xo->element(self::NAME, null, $this->poco->preferredUsername);
            } else {
               $xo->element(self::TITLE, null, $name);
            }
         }
         if (!empty($this->summary)) {
            $xo->element(self::SUMMARY, null, common_xml_safe_str($this->summary));
         }
         if (!empty($this->content)) {
            // XXX: assuming HTML content here
            $xo->element(
               ActivityUtils::CONTENT,
               array('type' => 'html'),
               common_xml_safe_str($this->content));
         }
         if (!empty($this->link)) {
            $xo->element('link',
               array(
                  'rel' => 'alternate',
                  'type' => 'text/html',
                  'href' => $this->link), null);
         }
         if(!empty($this->owner)) {
            $owner = $this->owner->asActivityNoun(self::AUTHOR);
            $xo->raw($owner);
         }
         if ($this->type == ActivityObject::PERSON || $this->type == ActivityObject::GROUP) {
            foreach ($this->avatarLinks as $alink) {
               $xo->element('link',
                  array(
                     'rel'          => 'avatar',
                     'type'         => $alink->type,
                     'media:width'  => $alink->width,
                     'media:height' => $alink->height,
                     'href'         => $alink->url),null);
            }
         }
         if (!empty($this->geopoint)) {
            $xo->element('georss:point', null, $this->geopoint);
         }
         if (!empty($this->poco)) {
            $this->poco->outputTo($xo);
         }

         // @fixme there's no way here to make a tree; elements can only contain plaintext
         // @fixme these may collide with JSON extensions
         foreach ($this->extra as $el) {
            list($extraTag, $attrs, $content) = array_pad($el, 3, null);
            $xo->element($extraTag, $attrs, $content);
         }
         Event::handle('EndActivityObjectOutputAtom', array($this, $xo));
      }
      if (!empty($tag)) {
         $xo->elementEnd($tag);
      }
      return;
   }


   // -------------------------------------------------------------------------
   // Function: asString
   // Returns the ActivityObject as an XML string
   //
   // Parameters:
   // o tag - default: "activity:object"
   //
   // Returns:
   // o string
   function asString($tag='activity:object') {
      $xs = new XMLStringer(true);
      $this->outputTo($xs, $tag);
      return $xs->getString();
   }

 
   // -------------------------------------------------------------------------
   // Function: asArray
   // Returns an array based on this Activity Object suitable for
   // encoding as JSON.
   //
   // Returns:
   // o array $object the activity object array
   function asArray()
   {
      $object = array();
      if (Event::handle('StartActivityObjectOutputJson', array($this, &$object))) {
         // XXX: attachments are added by Activity
         // author (Add object for author? Could be useful for repeats.)
         // content (Add rendered version of the notice?)
         // downstreamDuplicates
         
         // id
         if ($this->id) {
               $object['id'] = $this->id;
         } else if ($this->link) {
                $object['id'] = $this->link;
         }
         if ($this->type == ActivityObject::PERSON || $this->type == ActivityObject::GROUP) {
            // displayName
            $object['displayName'] = $this->title;

            // XXX: Not sure what the best avatar is to use for the
            // author's "image". For now, I'm using the large size.
            $imgLink          = null;
            $avatarMediaLinks = array();

            foreach ($this->avatarLinks as $a) {
               // Make a MediaLink for every other Avatar
               $avatar = new ActivityStreamsMediaLink(
                  $a->url, $a->width, $a->height, $a->type, 'avatar');

               // Find the big avatar to use as the "image"
               if ($a->height == AVATAR_PROFILE_SIZE) {
                  $imgLink = $avatar;
               }

               $avatarMediaLinks[] = $avatar->asArray();
            }
            if (!array_key_exists('status_net', $object)) {
               $object['status_net'] = array();
            }
            $object['status_net']['avatarLinks'] = $avatarMediaLinks; // extension

            // image
            if (!empty($imgLink)) {
               $object['image']  = $imgLink->asArray();
            }
         }
         // objectType
         //
         // We can probably use the whole schema URL here but probably the
         // relative simple name is easier to parse
         $object['objectType'] = self::canonicalType($this->type);

         // summary
         $object['summary'] = $this->summary;

         // content, usually rendered HTML
         $object['content'] = $this->content;

         // published (probably don't need. Might be useful for repeats.)

         // updated (probably don't need this)

         // TODO: upstreamDuplicates

         if ($this->link) {
            $object['url'] = $this->link;
         }

         /* Extensions */
         // @fixme these may collide with XML extensions
         // @fixme multiple tags of same name will overwrite each other
         // @fixme text content from XML extensions will be lost
         foreach ($this->extra as $e) {
            list($objectName, $props, $txt) = array_pad($e, 3, null);
            if (!empty($objectName)) {
               $parts = explode(":", $objectName);
               if (count($parts) == 2 && $parts[0] == "statusnet") {
                  if (!array_key_exists('status_net', $object)) {
                     $object['status_net'] = array();
                  }
                  $object['status_net'][$parts[1]] = $props;
               } else {
                  $object[$objectName] = $props;
               }
            }
         }

         if (!empty($this->geopoint)) {
            list($lat, $lon) = explode(' ', $this->geopoint);
            if (!empty($lat) && !empty($lon)) {
                    $object['location'] = array(
                        'objectType' => 'place',
                        'position' => sprintf("%+02.5F%+03.5F/", $lat, $lon),
                        'lat' => $lat,
                        'lon' => $lon);
               $loc = Location::fromLatLon((float)$lat, (float)$lon);
               if ($loc) {
                  $name = $loc->getName();
                  if ($name) {
                     $object['location']['displayName'] = $name;
                  }
                  $url = $loc->getURL();
                  if ($url) {
                     $object['location']['url'] = $url;
                  }
               }
            }
         }

         if (!empty($this->poco)) {
            $object['portablecontacts_net'] = array_filter($this->poco->asArray());
         }
         if (!empty($this->thumbnail)) {
            if (is_string($this->thumbnail)) {
               $object['image'] = array('url' => $this->thumbnail);
            } else {
               $object['image'] = array('url' => $this->thumbnail->getUrl());
               if ($this->thumbnail->width) {
                  $object['image']['width'] = $this->thumbnail->width;
               }
               if ($this->thumbnail->height) {
                  $object['image']['height'] = $this->thumbnail->height;
               }
            }
         }

         switch (self::canonicalType($this->type)) {
            case 'image':
               if (!empty($this->largerImage)) {
                    $object['fullImage'] = array('url' => $this->largerImage);
               }
               break;
            case 'audio':
            case 'video':
               if (!empty($this->stream)) {
                    $object['stream'] = array('url' => $this->stream);
               }
               break;
         }
         Event::handle('EndActivityObjectOutputJson', array($this, &$object));
      }
      return array_filter($object);
   }


   // -------------------------------------------------------------------------
   // Function: getIdentifiers
   //
   // Returns:
   // o array
   public function getIdentifiers() {
      $ids = array();
      foreach(array('id', 'link', 'url') as $id) {
         if (isset($this->$id)) {
            $ids[] = $this->$id;
         }
      }
      return array_unique($ids);
   }


   // -------------------------------------------------------------------------
   // Function: canonicalType:
   //
   // Parameters:
   // o type
   //
   // Returns:
   // o string URI
   static function canonicalType($type) {
      return ActivityUtils::resolveUri($type, true);
   }


   // -------------------------------------------------------------------------
   // Function: mimeTypeToObjectType
   //
   // Parameters:
   // o string mimeType
   //
   // Returns:
   // o string ot
   static function mimeTypeToObjectType($mimeType) {
      $ot = null;
      // Default

      if (empty($mimeType)) {
            return self::FILE;
      }

      $parts = explode('/', $mimeType);
      switch ($parts[0]) {
         case 'image':
            $ot = self::IMAGE;
            break;
         case 'audio':
            $ot = self::AUDIO;
            break;
         case 'video':
            $ot = self::VIDEO;
            break;
         default:
            $ot = self::FILE;
         }
      return $ot;
   }
}

// END OF FILE
// ============================================================================
?>