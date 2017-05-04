<?php
/* ============================================================================
 * Title: AS2Object
 * Class representation of an ActivityStreams 2.0 object
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
 * Class representation of an ActivityStreams 2.0 object
 *
 * The code in this plugin is wholly (c) copyright Maiyannah Bishop.
 *
 * For the W3C standards definition of an ActivityObject, see here:
 * o https://www.w3.org/TR/activitystreams-core/#asobject
 *
 * I can't gaurantee this is a purely-compliant interpretation or that what
 * hits the road when we actually try federating with this will end up being
 * standards-complaint, because the theory of a thing and the practical reality
 * usually end up very different.  Nonetheless, we should try to be as close
 * to the standard as we can.
 *
 * An ActivityObject can be considered the basic AS2 thing, and it is somewhat
 * of an extension of what we know in OStatus as an ActivityVerb, just with more
 * *stuff*.
 *
 * Stuff we will require even though AS2 makes them optional:
 * o uid           - our internal ID for the ActivityObject
 * o id            - URI of the notice/object/whatever
 * o type          - type of ActivityObject which this is
 *                   (how could this be optional?)
 *
 * Possible properties for an AS2 object per definition:
 * o attachment    - an ActivityObject of an attached notice
 * o attributedTo  - an ActivityObject of someone/something this AO is 
 *                   attributed to
 * o audience      - identifies one of more entities that are the intended
                     recipient of the object
 * o content       - If the content is in just one language, this is a string 
 *                   containing the entire content.  Defaults to HTML - we will
                     need to strip this probably to avoid exploits.
 * o contentMap    - If the content is in multiple languages, this is a series of
 *                   key/value pairs using standard il8n tags to divvy up the
 *                   content by language, ex:
 *
 *                       "en": "A <em>simple</em> note",
 *                       "es": "Una nota <em>sencilla</em>",
 * o context       - The context in which an action happens, in practical terms,
                     we'd probably use this to associate a notice with a group.
 * o name          - Plaintext name for the object, NO HTML (thank god)
 * o nameMap       - Same deal as contentMap, just for name (il8n maps IOW)
 * o endTime       - The date and time at which an object was published
 * o generator     - an ActivityObject describing the generator of the AS2O
 * o icon          - an ActivityObject containing an iconic representation of
 *                   this object.
 * o image         - an ActivityObject or ActivityObjects containing images 
 *                   associated with the AS2O.  For example, images included
 *                   in a notice.  We should reject blobs or "inline" images,
 *                   as this presents security issues, but URLs are fine.
 * o inReplyTo     - Either an ActivityObject or a link to a notice that this
 *                   ActivityObject represents a reply to - we should probably
 *                   use links here to keep things simple.  Don't forget to
 *                   create conversation_ids where they don't exist.
 * o location      - An ActivityObject describing the place where the AS2O
 *                   took place.  For example, location data for a where a
 *                   photograph was taken.
 * o preview       - An ActivityObject that provides a preview of the object,
 *                   such as a snippet of a song or a movie trailer.
 * o published     - The date and time at which the AS2O was published.
 * o replies       - A collection of ActivityObjects considered to be responses
 *                   to this ActivityObject.  We will probably make inclusion
 *                   of this a config option - it could help ease federation a
 *                   lot but is bandwidth-intensive.
 * o startTime     - The date and time describing the expected starting time of
 *                   an object (so for example, when a talk is supposed to start
 *                   when it describes a speaking engagement.)
 * o summary       - A HTML string containing the summary of the object.  We
 *                   will sanitize this on receipt.
 * o summaryMap    - As with contentMap or nameMap, but for summary
 * o tag           - One of more ActivityObjects representing hashtags for the AS20
 * o updated       - The date and time the AS2O was last updated
 * o url           - One of more ActivityObjects with links to this object.
 *                   So we could for example have the web-facing version, and also
 *                   the ATOM, XML, and YAML versions.
 * o to            - An ActivityObject or URL representing the primary recipient
 *                   of the message (so the direct mention, as it were, when one
 *                   exists)
 * o bto           - As to, but for a private context.  So in other words, don't
 *                   expose this notice publicly.
 * o cc            - An ActivityObject or link representing the secondary audience
 *                   for the object
 * o bcc           - As cc, but for a private context.
 * o mediaType     - String containing MIME media type of 'content'
 * o duration      - Duration of the content for music, video, etc.
 *
 * This class represents the parent object for most AS2 things, so be very
 * cautious about changes made here.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.


// ----------------------------------------------------------------------------
// Class: AS2Object
// Class holding the representation of an AS2 ActivityObject
class AS2Object extends Managed_DataObject {
   // Protected so you have to go through proper interfaces
   protected $uid;
   protected $name;
   protected $id;
   protected $type;
   protected $attachment;
   protected $attributedTo;
   protected $audience;
   protected $content;
   protected $context;
   protected $contentMap;
   protected $name;
   protected $nameMap;
   protected $endTime;
   protected $generator;
   protected $icon;
   protected $image;
   protected $inReplyTo;
   protected $location;
   protected $preview;
   protected $published;
   protected $replies;
   protected $startTime;
   protected $summary;
   protected $summaryMap;
   protected $tag;
   protected $updated;
   protected $url;
   protected $to;
   protected $bto;
   protected $cc;
   protected $bcc;
   protected $mediaType;
   protected $duration;
}


// END OF FILE
// ============================================================================
?>