<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 *
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
 * An activity verb in class form, and the related scaffolding.
 *
 * This file also now consolidates the ActivityContext, ActivityImporter,
 * ActivityMover, ActivitySink, and ActivitySource classes, formerly at
 * /lib/<class>.php
 *
 * Activity abstracts the class for an activity verb.
 * ActivityContext contains information of the context of the activity verb.
 * ActivityImporter abstracts a means that is importing activity verbs
 * into the system as part of a user's timeline.
 * ActivityMover abstracts the means to transport activity verbs.
 * ActivitySink abstracts a class to receive activity verbs.
 * ActivitySource abstracts a class to represent the source of a received
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
 *
 * PHP version 5
 *
 * @category  OStatus
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Zach Copley <zach@status.net>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2010 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * An activity in the ActivityStrea.ms world
 *
 * An activity is kind of like a sentence: someone did something
 * to something else.
 *
 * 'someone' is the 'actor'; 'did something' is the verb;
 * 'something else' is the object.
 */
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
    /**
     * Turns a regular old Atom <entry> into a magical activity
     *
     * @param DOMElement $entry Atom entry to poke at
     * @param DOMElement $feed  Atom feed, for context
     */
    function __construct($entry = null, $feed = null)
    {
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

        if ($entry->namespaceURI == Activity::ATOM &&
            $entry->localName == 'entry') {
            $this->_fromAtomEntry($entry, $feed);
        } else if ($entry->namespaceURI == Activity::RSS &&
                   $entry->localName == 'item') {
            $this->_fromRssItem($entry, $feed);
        } else if ($entry->namespaceURI == Activity::SPEC &&
                   $entry->localName == 'object') {
            $this->_fromAtomEntry($entry, $feed);
        } else {
            // Low level exception. No need for i18n.
            throw new Exception("Unknown DOM element: {$entry->namespaceURI} {$entry->localName}");
        }
    }

    function _fromAtomEntry($entry, $feed)
    {
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
                if (!empty($objectType) && $objectType == ActivityObject::ACTIVITY) {
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

    function _fromRssItem($item, $channel)
    {
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

    /**
     * Returns an Atom <entry> based on this activity
     *
     * @return DOMElement Atom entry
     */

    function toAtomEntry()
    {
        return null;
    }

    /**
     * Returns an array based on this activity suitable
     * for encoding as a JSON object
     *
     * @return array $activity
     */

    function asArray()
    {
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
                    'lon' => $loc->lon
                );

                $name = $loc->getName();

                if ($name) {
                    $activity['location']['displayName'] = $name;
                }
                    
                $url = $loc->getURL();

                if ($url) {
                    $activity['location']['url'] = $url;
                }
            }

            $activity['to']      = $this->context->getToArray();

            $ctxarr = $this->context->asArray();

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
            'url' => common_root_url()
        );

        $activity['provider'] = $provider;

        // target
        if (!empty($this->target)) {
            $activity['target'] = $this->target->asArray();
        }

        // title
        $activity['title'] = $this->title;

        // updated <-- Optional. Should we use this to indicate the time we r
        //             eceived a remote notice? Probably not.

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

    function asString($namespace=false, $author=true, $source=false)
    {
        $xs = new XMLStringer(true);
        $this->outputTo($xs, $namespace, $author, $source);
        return $xs->getString();
    }

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

    private function _child($element, $tag, $namespace=self::SPEC)
    {
        return ActivityUtils::child($element, $tag, $namespace);
    }

    /**
     * For consistency, we'll always output UTC rather than local time.
     * Note that clients *should* accept any timezone we give them as long
     * as it's properly formatted.
     *
     * @param int $tm Unix timestamp
     * @return string
     */
    static function iso8601Date($tm)
    {
        $dateStr = date('d F Y H:i:s', $tm);
        $d = new DateTime($dateStr, new DateTimeZone('UTC'));
        return $d->format('c');
    }
}

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
    const OBJECTTYPE = 'ostatus:object-type';   // FIXME: Undocumented!
    const CONVERSATION = 'ostatus:conversation';

    const POINT     = 'point';

    const MENTIONED    = 'mentioned';

    const ATTN_PUBLIC  = 'http://activityschema.org/collection/public';

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

    /**
     * Parse location given as a GeoRSS-simple point, if provided.
     * http://www.georss.org/simple
     *
     * @param feed item $entry
     * @return mixed Location or false
     */
    function getLocation($dom)
    {
        $points = $dom->getElementsByTagNameNS(self::GEORSS, self::POINT);

        for ($i = 0; $i < $points->length; $i++) {
            $point = $points->item($i)->textContent;
            return self::locationFromPoint($point);
        }

        return null;
    }

    // XXX: Move to ActivityUtils or Location?
    static function locationFromPoint($point)
    {
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

    /**
     * Returns context (StatusNet stuff) as an array suitable for serializing
     * in JSON. Right now context stuff is an extension to Activity.
     *
     * @return array the context
     */

    function asArray()
    {
        $context = array();

        $context['inReplyTo']    = $this->getInReplyToArray();
        $context['conversation'] = $this->conversation;

        return array_filter($context);
    }

    /**
     * Returns an array of arrays representing Activity Objects (intended to be
     * serialized in JSON) that represent WHO the Activity is supposed to
     * be received by. This is not really specified but appears in an example
     * of the current spec as an extension. We might want to figure out a JSON
     * serialization for OStatus and use that to express mentions instead.
     *
     * XXX: People's ideas on how to do this are all over the place
     *
     * @return array the array of recipients
     */

    function getToArray()
    {
        $tos = array();

        foreach ($this->attention as $attnUrl => $attnType) {
            $to = array(
                'objectType' => $attnType,  // can be empty
                'id'         => $attnUrl,
            );
            $tos[] = $to;
        }

        return $tos;
    }

    /**
     * Return an array for the notices this notice is a reply to 
     * suitable for serializing as JSON note objects.
     *
     * @return array the array of notes
     */

     function getInReplyToArray()
     {
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

class ActivityImporter extends QueueHandler
{
    private $trusted = false;

    /**
     * Function comment
     *
     * @param
     *
     * @return
     */
    function handle($data)
    {
        list($user, $author, $activity, $trusted) = $data;

        $this->trusted = $trusted;

        $done = null;

        try {
            if (Event::handle('StartImportActivity',
                              array($user, $author, $activity, $trusted, &$done))) {
                switch ($activity->verb) {
                case ActivityVerb::FOLLOW:
                    $this->subscribeProfile($user, $author, $activity);
                    break;
                case ActivityVerb::JOIN:
                    $this->joinGroup($user, $activity);
                    break;
                case ActivityVerb::POST:
                    $this->postNote($user, $author, $activity);
                    break;
                default:
                    // TRANS: Client exception thrown when using an unknown verb for the activity importer.
                    throw new ClientException(sprintf(_("Unknown verb: \"%s\"."),$activity->verb));
                }
                Event::handle('EndImportActivity',
                              array($user, $author, $activity, $trusted));
                $done = true;
            }
        } catch (Exception $e) {
            common_log(LOG_ERR, $e->getMessage());
            $done = true;
        }
        return $done;
    }

    function subscribeProfile($user, $author, $activity)
    {
        $profile = $user->getProfile();

        if ($activity->objects[0]->id == $author->id) {
            if (!$this->trusted) {
                // TRANS: Client exception thrown when trying to force a subscription for an untrusted user.
                throw new ClientException(_('Cannot force subscription for untrusted user.'));
            }

            $other = $activity->actor;
            $otherUser = User::getKV('uri', $other->id);

            if (!$otherUser instanceof User) {
                // TRANS: Client exception thrown when trying to force a remote user to subscribe.
                throw new Exception(_('Cannot force remote user to subscribe.'));
            }

            $otherProfile = $otherUser->getProfile();

            // XXX: don't do this for untrusted input!

            Subscription::ensureStart($otherProfile, $profile);
        } else if (empty($activity->actor)
                   || $activity->actor->id == $author->id) {

            $other = $activity->objects[0];

            try {
                $otherProfile = Profile::fromUri($other->id);
                // TRANS: Client exception thrown when trying to subscribe to an unknown profile.
            } catch (UnknownUriException $e) {
                // Let's convert it to a client exception instead of server.
                throw new ClientException(_('Unknown profile.'));
            }

            Subscription::ensureStart($profile, $otherProfile);
        } else {
            // TRANS: Client exception thrown when trying to import an event not related to the importing user.
            throw new Exception(_('This activity seems unrelated to our user.'));
        }
    }

    function joinGroup($user, $activity)
    {
        // XXX: check that actor == subject

        $uri = $activity->objects[0]->id;

        $group = User_group::getKV('uri', $uri);

        if (!$group instanceof User_group) {
            $oprofile = Ostatus_profile::ensureActivityObjectProfile($activity->objects[0]);
            if (!$oprofile->isGroup()) {
                // TRANS: Client exception thrown when trying to join a remote group that is not a group.
                throw new ClientException(_('Remote profile is not a group!'));
            }
            $group = $oprofile->localGroup();
        }

        assert(!empty($group));

        if ($user->isMember($group)) {
            // TRANS: Client exception thrown when trying to join a group the importing user is already a member of.
            throw new ClientException(_("User is already a member of this group."));
        }

        $user->joinGroup($group);
    }

    // XXX: largely cadged from Ostatus_profile::processNote()

    function postNote($user, $author, $activity)
    {
        $note = $activity->objects[0];

        $sourceUri = $note->id;

        $notice = Notice::getKV('uri', $sourceUri);

        if ($notice instanceof Notice) {

            common_log(LOG_INFO, "Notice {$sourceUri} already exists.");

            if ($this->trusted) {

                $profile = $notice->getProfile();

                $uri = $profile->getUri();

                if ($uri === $author->id) {
                    common_log(LOG_INFO, sprintf('Updating notice author from %s to %s', $author->id, $user->getUri()));
                    $orig = clone($notice);
                    $notice->profile_id = $user->id;
                    $notice->update($orig);
                    return;
                } else {
                    // TRANS: Client exception thrown when trying to import a notice by another user.
                    // TRANS: %1$s is the source URI of the notice, %2$s is the URI of the author.
                    throw new ClientException(sprintf(_('Already know about notice %1$s and '.
                                                        ' it has a different author %2$s.'),
                                                      $sourceUri, $uri));
                }
            } else {
                // TRANS: Client exception thrown when trying to overwrite the author information for a non-trusted user during import.
                throw new ClientException(_('Not overwriting author info for non-trusted user.'));
            }
        }

        // Use summary as fallback for content

        if (!empty($note->content)) {
            $sourceContent = $note->content;
        } else if (!empty($note->summary)) {
            $sourceContent = $note->summary;
        } else if (!empty($note->title)) {
            $sourceContent = $note->title;
        } else {
            // @fixme fetch from $sourceUrl?
            // TRANS: Client exception thrown when trying to import a notice without content.
            // TRANS: %s is the notice URI.
            throw new ClientException(sprintf(_('No content for notice %s.'),$sourceUri));
        }

        // Get (safe!) HTML and text versions of the content

        $rendered = common_purify($sourceContent);
        $content = common_strip_html($rendered);

        $shortened = $user->shortenLinks($content);

        $options = array('is_local' => Notice::LOCAL_PUBLIC,
                         'uri' => $sourceUri,
                         'rendered' => $rendered,
                         'replies' => array(),
                         'groups' => array(),
                         'tags' => array(),
                         'urls' => array(),
                         'distribute' => false);

        // Check for optional attributes...

        if (!empty($activity->time)) {
            $options['created'] = common_sql_date($activity->time);
        }

        if ($activity->context) {
            // Any individual or group attn: targets?

            list($options['groups'], $options['replies']) = $this->filterAttention($activity->context->attention);

            // Maintain direct reply associations
            // @fixme what about conversation ID?
            if (!empty($activity->context->replyToID)) {
                $orig = Notice::getKV('uri', $activity->context->replyToID);
                if ($orig instanceof Notice) {
                    $options['reply_to'] = $orig->id;
                }
            }

            $location = $activity->context->location;

            if ($location) {
                $options['lat'] = $location->lat;
                $options['lon'] = $location->lon;
                if ($location->location_id) {
                    $options['location_ns'] = $location->location_ns;
                    $options['location_id'] = $location->location_id;
                }
            }
        }

        // Atom categories <-> hashtags

        foreach ($activity->categories as $cat) {
            if ($cat->term) {
                $term = common_canonical_tag($cat->term);
                if ($term) {
                    $options['tags'][] = $term;
                }
            }
        }

        // Atom enclosures -> attachment URLs
        foreach ($activity->enclosures as $href) {
            // @fixme save these locally or....?
            $options['urls'][] = $href;
        }

        common_log(LOG_INFO, "Saving notice {$options['uri']}");

        $saved = Notice::saveNew($user->id,
                                 $content,
                                 'restore', // TODO: restore the actual source
                                 $options);

        return $saved;
    }

    protected function filterAttention(array $attn)
    {
        $groups = array();  // TODO: context->attention
        $replies = array(); // TODO: context->attention

        foreach ($attn as $recipient=>$type) {

            // Is the recipient a local user?

            $user = User::getKV('uri', $recipient);

            if ($user instanceof User) {
                // TODO: @fixme sender verification, spam etc?
                $replies[] = $recipient;
                continue;
            }

            // Is the recipient a remote group?
            $oprofile = Ostatus_profile::ensureProfileURI($recipient);

            if ($oprofile) {
                if (!$oprofile->isGroup()) {
                    // may be canonicalized or something
                    $replies[] = $oprofile->uri;
                }
                continue;
            }

            // Is the recipient a local group?
            // TODO: @fixme uri on user_group isn't reliable yet
            // $group = User_group::getKV('uri', $recipient);
            $id = OStatusPlugin::localGroupFromUrl($recipient);

            if ($id) {
                $group = User_group::getKV('id', $id);
                if ($group) {
                    // Deliver to all members of this local group if allowed.
                    $profile = $sender->localProfile();
                    if ($profile->isMember($group)) {
                        $groups[] = $group->id;
                    } else {
                        common_log(LOG_INFO, "Skipping reply to local group {$group->nickname} as sender {$profile->id} is not a member");
                    }
                    continue;
                } else {
                    common_log(LOG_INFO, "Skipping reply to bogus group $recipient");
                }
            }
        }

        return array($groups, $replies);
    }
}

class ActivityMover extends QueueHandler
{
    function transport()
    {
        return 'actmove';
    }

    function handle($data)
    {
        list ($act, $sink, $userURI, $remoteURI) = $data;

        $user   = User::getKV('uri', $userURI);
        try {
            $remote = Profile::fromUri($remoteURI);
        } catch (UnknownUriException $e) {
            // Don't retry. It's hard to tell whether it's because of
            // lookup failures or because the URI is permanently gone.
            // If we knew it was temporary, we'd return false here.
            return true;
        }

        try {
            $this->moveActivity($act, $sink, $user, $remote);
        } catch (ClientException $cex) {
            $this->log(LOG_WARNING,
                       $cex->getMessage());
            // "don't retry me"
            return true;
        } catch (ServerException $sex) {
            $this->log(LOG_WARNING,
                       $sex->getMessage());
            // "retry me" (because we think the server might handle it next time)
            return false;
        } catch (Exception $ex) {
            $this->log(LOG_WARNING,
                       $ex->getMessage());
            // "don't retry me"
            return true;
        }
    }

    function moveActivity($act, $sink, $user, $remote)
    {
        if (empty($user)) {
            // TRANS: Exception thrown if a non-existing user is provided. %s is a user ID.
            throw new Exception(sprintf(_('No such user "%s".'),$act->actor->id));
        }

        switch ($act->verb) {
/*        case ActivityVerb::FAVORITE:
            $this->log(LOG_INFO,
                       "Moving favorite of {$act->objects[0]->id} by ".
                       "{$act->actor->id} to {$remote->nickname}.");
            // push it, then delete local
            $sink->postActivity($act);
            $notice = Notice::getKV('uri', $act->objects[0]->id);
            if (!empty($notice)) {
                $fave = Fave::pkeyGet(array('user_id' => $user->id,
                                            'notice_id' => $notice->id));
                $fave->delete();
            }
            break;*/
        case ActivityVerb::POST:
            $this->log(LOG_INFO,
                       "Moving notice {$act->objects[0]->id} by ".
                       "{$act->actor->id} to {$remote->nickname}.");
            // XXX: send a reshare, not a post
            $sink->postActivity($act);
            $notice = Notice::getKV('uri', $act->objects[0]->id);
            if (!empty($notice)) {
                $notice->deleteAs($user->getProfile(), false);
            }
            break;
        case ActivityVerb::JOIN:
            $this->log(LOG_INFO,
                       "Moving group join of {$act->objects[0]->id} by ".
                       "{$act->actor->id} to {$remote->nickname}.");
            $sink->postActivity($act);
            $group = User_group::getKV('uri', $act->objects[0]->id);
            if (!empty($group)) {
                $user->leaveGroup($group);
            }
            break;
        case ActivityVerb::FOLLOW:
            if ($act->actor->id === $user->getUri()) {
                $this->log(LOG_INFO,
                           "Moving subscription to {$act->objects[0]->id} by ".
                           "{$act->actor->id} to {$remote->nickname}.");
                $sink->postActivity($act);
                try {
                    $other = Profile::fromUri($act->objects[0]->id);
                    Subscription::cancel($user->getProfile(), $other);
                } catch (UnknownUriException $e) {
                    // Can't cancel subscription if we don't know who to alert
                }
            } else {
                $otherUser = User::getKV('uri', $act->actor->id);
                if (!empty($otherUser)) {
                    $this->log(LOG_INFO,
                               "Changing sub to {$act->objects[0]->id}".
                               "by {$act->actor->id} to {$remote->nickname}.");
                    $otherProfile = $otherUser->getProfile();
                    Subscription::ensureStart($otherProfile, $remote);
                    Subscription::cancel($otherProfile, $user->getProfile());
                } else {
                    $this->log(LOG_NOTICE,
                               "Not changing sub to {$act->objects[0]->id}".
                               "by remote {$act->actor->id} ".
                               "to {$remote->nickname}.");
                }
            }
            break;
        }
    }

    /**
     * Log some data
     *
     * Add a header for our class so we know who did it.
     *
     * @param int    $level   Log level, like LOG_ERR or LOG_INFO
     * @param string $message Message to log
     *
     * @return void
     */
    protected function log($level, $message)
    {
        common_log($level, "ActivityMover: " . $message);
    }
}

class ActivitySink
{
    protected $svcDocUrl   = null;
    protected $username    = null;
    protected $password    = null;
    protected $collections = array();

    function __construct($svcDocUrl, $username, $password)
    {
        $this->svcDocUrl = $svcDocUrl;
        $this->username  = $username;
        $this->password  = $password;

        $this->_parseSvcDoc();
    }

    private function _parseSvcDoc()
    {
        $client   = new HTTPClient();
        $response = $client->get($this->svcDocUrl);

        if ($response->getStatus() != 200) {
            throw new Exception("Can't get {$this->svcDocUrl}; response status " . $response->getStatus());
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

    private function _addCollection($verb, $url)
    {
        if (array_key_exists($verb, $this->collections)) {
            $this->collections[$verb][] = $url;
        } else {
            $this->collections[$verb] = array($url);
        }
        return;
    }

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

    function postToCollection($url, $activity)
    {
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

class ActivitySource
{
    public $id;
    public $title;
    public $icon;
    public $updated;
    public $links;
}
?>