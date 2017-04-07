<?php
/* ============================================================================
 * Title: Notice
 * Base superclass for notices
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
 * Base superclass for notices
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Zach Copley
 * o Garret Buell <terragb@gmail.com>
 * o Ori Avtalion
 * o mac65 <mac65@mac65.com>
 * o Robin Millette <robin@millette.info>
 * o Adrian Lang <mail@adrianlang.de>
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Brenda Wallace <shiny@cpan.org>
 * o Sarven Capadisli <csarven@status.net>
 * o Craig Andrews <candrews@integralblue.com>
 * o Toby Inkster <mail@tobyinkster.co.uk>
 * o Marcel van der Boom <marcel@hsdev.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o James Walker <walkah@walkah.net>
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Dan Scott <dan@coffeecode.net>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Joshua Judson Rosen <rozzin@geekspace.com>
 * o Hannes Mannerheim <h@nnesmannerhe.im>
 * o Chimo <chimo@chromic.org>
 * o Abjectio <kneh@member.fsf.org>
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


/**
 * Table Definition for notice
 */

/* We keep 200 notices, the max number of notices available per API request,
 * in the memcached cache. */

define('NOTICE_CACHE_WINDOW', CachingNoticeStream::CACHE_WINDOW);

define('MAX_BOXCARS', 128);

class Notice extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'notice';                          // table name
    public $id;                              // int(4)  primary_key not_null
    public $profile_id;                      // int(4)  multiple_key not_null
    public $uri;                             // varchar(191)  unique_key   not 255 because utf8mb4 takes more space
    public $content;                         // text
    public $rendered;                        // text
    public $url;                             // varchar(191)   not 255 because utf8mb4 takes more space
    public $created;                         // datetime  multiple_key not_null default_0000-00-00%2000%3A00%3A00
    public $modified;                        // timestamp   not_null default_CURRENT_TIMESTAMP
    public $reply_to;                        // int(4)
    public $is_local;                        // int(4)
    public $source;                          // varchar(32)
    public $conversation;                    // int(4)
    public $repeat_of;                       // int(4)
    public $verb;                            // varchar(191)   not 255 because utf8mb4 takes more space
    public $object_type;                     // varchar(191)   not 255 because utf8mb4 takes more space
    public $scope;                           // int(4)

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        $def = array(
            'fields' => array(
                'id' => array('type' => 'serial', 'not null' => true, 'description' => 'unique identifier'),
                'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'who made the update'),
                'uri' => array('type' => 'varchar', 'length' => 191, 'description' => 'universally unique identifier, usually a tag URI'),
                'content' => array('type' => 'text', 'description' => 'update content', 'collate' => 'utf8mb4_general_ci'),
                'rendered' => array('type' => 'text', 'description' => 'HTML version of the content'),
                'url' => array('type' => 'varchar', 'length' => 191, 'description' => 'URL of any attachment (image, video, bookmark, whatever)'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
                'reply_to' => array('type' => 'int', 'description' => 'notice replied to (usually a guess)'),
                'is_local' => array('type' => 'int', 'size' => 'tiny', 'default' => 0, 'description' => 'notice was generated by a user'),
                'source' => array('type' => 'varchar', 'length' => 32, 'description' => 'source of comment, like "web", "im", or "clientname"'),
                'conversation' => array('type' => 'int', 'description' => 'the local numerical conversation id'),
                'repeat_of' => array('type' => 'int', 'description' => 'notice this is a repeat of'),
                'object_type' => array('type' => 'varchar', 'length' => 191, 'description' => 'URI representing activity streams object type', 'default' => null),
                'verb' => array('type' => 'varchar', 'length' => 191, 'description' => 'URI representing activity streams verb', 'default' => 'http://activitystrea.ms/schema/1.0/post'),
                'scope' => array('type' => 'int',
                                 'description' => 'bit map for distribution scope; 0 = everywhere; 1 = this server only; 2 = addressees; 4 = followers; null = default'),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'notice_uri_key' => array('uri'),
            ),
            'foreign keys' => array(
                'notice_profile_id_fkey' => array('profile', array('profile_id' => 'id')),
                'notice_reply_to_fkey' => array('notice', array('reply_to' => 'id')),
                'notice_conversation_fkey' => array('conversation', array('conversation' => 'id')), # note... used to refer to notice.id
                'notice_repeat_of_fkey' => array('notice', array('repeat_of' => 'id')), # @fixme: what about repeats of deleted notices?
            ),
            'indexes' => array(
                'notice_created_id_is_local_idx' => array('created', 'id', 'is_local'),
                'notice_profile_id_idx' => array('profile_id', 'created', 'id'),
                'notice_repeat_of_created_id_idx' => array('repeat_of', 'created', 'id'),
                'notice_conversation_created_id_idx' => array('conversation', 'created', 'id'),
                'notice_object_type_idx' => array('object_type'),
                'notice_verb_idx' => array('verb'),
                'notice_profile_id_verb_idx' => array('profile_id', 'verb'),
                'notice_url_idx' => array('url'),   // Qvitter wants this
                'notice_replyto_idx' => array('reply_to')
            )
        );

        if (common_config('search', 'type') == 'fulltext') {
            $def['fulltext indexes'] = array('content' => array('content'));
        }

        return $def;
    }

    /* Notice types */
    const LOCAL_PUBLIC    =  1;
    const REMOTE          =  0;
    const LOCAL_NONPUBLIC = -1;
    const GATEWAY         = -2;

    const PUBLIC_SCOPE    = 0; // Useful fake constant
    const SITE_SCOPE      = 1;
    const ADDRESSEE_SCOPE = 2;
    const GROUP_SCOPE     = 4;
    const FOLLOWER_SCOPE  = 8;

    protected $_profile = array();

    /**
     * Will always return a profile, if anything fails it will
     * (through _setProfile) throw a NoProfileException.
     */
    public function getProfile()
    {
        if (!isset($this->_profile[$this->profile_id])) {
            // We could've sent getKV directly to _setProfile, but occasionally we get
            // a "false" (instead of null), likely because it indicates a cache miss.
            $profile = Profile::getKV('id', $this->profile_id);
            $this->_setProfile($profile instanceof Profile ? $profile : null);
        }
        return $this->_profile[$this->profile_id];
    }

    public function _setProfile(Profile $profile=null)
    {
        if (!$profile instanceof Profile) {
            throw new NoProfileException($this->profile_id);
        }
        $this->_profile[$this->profile_id] = $profile;
    }

    public function deleteAs(Profile $actor, $delete_event=true)
    {
        if (!$this->getProfile()->sameAs($actor) && !$actor->hasRight(Right::DELETEOTHERSNOTICE)) {
            throw new AuthorizationException(_('You are not allowed to delete another user\'s notice.'));
        }

        $result = null;
        if (!$delete_event || Event::handle('DeleteNoticeAsProfile', array($this, $actor, &$result))) {
            // If $delete_event is true, we run the event. If the Event then 
            // returns false it is assumed everything was handled properly 
            // and the notice was deleted.
            $result = $this->delete();
        }
        return $result;
    }

    protected function deleteRelated()
    {
        if (Event::handle('NoticeDeleteRelated', array($this))) {
            // Clear related records
            $this->clearReplies();
            $this->clearLocation();
            $this->clearRepeats();
            $this->clearTags();
            $this->clearGroupInboxes();
            $this->clearFiles();
            $this->clearAttentions();
            // NOTE: we don't clear queue items
        }
    }

    public function delete($useWhere=false)
    {
        $this->deleteRelated();

        $result = parent::delete($useWhere);

        $this->blowOnDelete();
        return $result;
    }

    public function getUri()
    {
        return $this->uri;
    }

    /*
     * Get a Notice object by URI. Will call external plugins for help
     * using the event StartGetNoticeFromURI.
     *
     * @param string $uri A unique identifier for a resource (notice in this case)
     */
    static function fromUri($uri)
    {
        $notice = null;

        if (Event::handle('StartGetNoticeFromUri', array($uri, &$notice))) {
            $notice = Notice::getKV('uri', $uri);
            Event::handle('EndGetNoticeFromUri', array($uri, $notice));
        }

        if (!$notice instanceof Notice) {
            throw new UnknownUriException($uri);
        }

        return $notice;
    }

    /*
     * @param $root boolean If true, link to just the conversation root.
     *
     * @return URL to conversation
     */
    public function getConversationUrl($anchor=true)
    {
        return Conversation::getUrlFromNotice($this, $anchor);
    }

    /*
     * Get the local representation URL of this notice.
     */
    public function getLocalUrl()
    {
        return common_local_url('shownotice', array('notice' => $this->id), null, null, false);
    }

    public function getTitle($imply=true)
    {
        $title = null;
        if (Event::handle('GetNoticeTitle', array($this, &$title)) && $imply) {
            // TRANS: Title of a notice posted without a title value.
            // TRANS: %1$s is a user name, %2$s is the notice creation date/time.
            $title = sprintf(_('%1$s\'s status on %2$s'),
                             $this->getProfile()->getFancyName(),
                             common_exact_date($this->created));
        }
        return $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getRendered()
    {
        // we test $this->id because if it's not inserted yet, we can't update the field
        if (!empty($this->id) && (is_null($this->rendered) || $this->rendered === '')) {
            // update to include rendered content on-the-fly, so we don't have to have a fix-up script in upgrade.php
            common_debug('Rendering notice '.$this->getID().' as it had no rendered HTML content.');
            $orig = clone($this);
            $this->rendered = common_render_content($this->getContent(),
                                                    $this->getProfile(),
                                                    $this->hasParent() ? $this->getParent() : null);
            $this->update($orig);
        }
        return $this->rendered;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getVerb($make_relative=false)
    {
        return ActivityUtils::resolveUri($this->verb, $make_relative);
    }

    public function isVerb(array $verbs)
    {
        return ActivityUtils::compareVerbs($this->getVerb(), $verbs);
    }

    /*
     * Get the original representation URL of this notice.
     *
     * @param boolean $fallback     Whether to fall back to generate a local URL or throw InvalidUrlException
     */
    public function getUrl($fallback=false)
    {
        // The risk is we start having empty urls and non-http uris...
        // and we can't really handle any other protocol right now.
        switch (true) {
        case $this->isLocal():
            return common_local_url('shownotice', array('notice' => $this->getID()), null, null, false);
        case common_valid_http_url($this->url): // should we allow non-http/https URLs?
            return $this->url;
        case common_valid_http_url($this->uri): // Sometimes we only have the URI for remote posts.
            return $this->uri;
        case $fallback:
            // let's generate a valid link to our locally available notice on demand
            return common_local_url('shownotice', array('notice' => $this->getID()), null, null, false);
        default:
            throw new InvalidUrlException($this->url);
        }
    }

    public function getObjectType($canonical=false) {
        if (is_null($this->object_type) || $this->object_type==='') {
            throw new NoObjectTypeException($this);
        }
        return ActivityUtils::resolveUri($this->object_type, $canonical);
    }

    public function isObjectType(array $types)
    {
        try {
            return ActivityUtils::compareTypes($this->getObjectType(), $types);
        } catch (NoObjectTypeException $e) {
            return false;
        }
    }

    /**
     * Extract #hashtags from this notice's content and save them to the database.
     */
    function saveTags()
    {
        /* extract all #hastags */
        $count = preg_match_all('/(?:^|\s)#([\pL\pN_\-\.]{1,64})/u', strtolower($this->content), $match);
        if (!$count) {
            return true;
        }

        /* Add them to the database */
        return $this->saveKnownTags($match[1]);
    }

    /**
     * Record the given set of hash tags in the db for this notice.
     * Given tag strings will be normalized and checked for dupes.
     */
    function saveKnownTags($hashtags)
    {
        //turn each into their canonical tag
        //this is needed to remove dupes before saving e.g. #hash.tag = #hashtag
        for($i=0; $i<count($hashtags); $i++) {
            /* elide characters we don't want in the tag */
            $hashtags[$i] = common_canonical_tag($hashtags[$i]);
        }

        foreach(array_unique($hashtags) as $hashtag) {
            $this->saveTag($hashtag);
            self::blow('profile:notice_ids_tagged:%d:%s', $this->profile_id, $hashtag);
        }
        return true;
    }

    /**
     * Record a single hash tag as associated with this notice.
     * Tag format and uniqueness must be validated by caller.
     */
    function saveTag($hashtag)
    {
        $tag = new Notice_tag();
        $tag->notice_id = $this->id;
        $tag->tag = $hashtag;
        $tag->created = $this->created;
        $id = $tag->insert();

        if (!$id) {
            // TRANS: Server exception. %s are the error details.
            throw new ServerException(sprintf(_('Database error inserting hashtag: %s.'),
                                              $last_error->message));
            return;
        }

        // if it's saved, blow its cache
        $tag->blowCache(false);
    }

    /**
     * Save a new notice and push it out to subscribers' inboxes.
     * Poster's permissions are checked before sending.
     *
     * @param int $profile_id Profile ID of the poster
     * @param string $content source message text; links may be shortened
     *                        per current user's preference
     * @param string $source source key ('web', 'api', etc)
     * @param array $options Associative array of optional properties:
     *              string 'created' timestamp of notice; defaults to now
     *              int 'is_local' source/gateway ID, one of:
     *                  Notice::LOCAL_PUBLIC    - Local, ok to appear in public timeline
     *                  Notice::REMOTE          - Sent from a remote service;
     *                                            hide from public timeline but show in
     *                                            local "and friends" timelines
     *                  Notice::LOCAL_NONPUBLIC - Local, but hide from public timeline
     *                  Notice::GATEWAY         - From another non-OStatus service;
     *                                            will not appear in public views
     *              float 'lat' decimal latitude for geolocation
     *              float 'lon' decimal longitude for geolocation
     *              int 'location_id' geoname identifier
     *              int 'location_ns' geoname namespace to interpret location_id
     *              int 'reply_to'; notice ID this is a reply to
     *              int 'repeat_of'; notice ID this is a repeat of
     *              string 'uri' unique ID for notice; a unique tag uri (can be url or anything too)
     *              string 'url' permalink to notice; defaults to local notice URL
     *              string 'rendered' rendered HTML version of content
     *              array 'replies' list of profile URIs for reply delivery in
     *                              place of extracting @-replies from content.
     *              array 'groups' list of group IDs to deliver to, in place of
     *                              extracting ! tags from content
     *              array 'tags' list of hashtag strings to save with the notice
     *                           in place of extracting # tags from content
     *              array 'urls' list of attached/referred URLs to save with the
     *                           notice in place of extracting links from content
     *              boolean 'distribute' whether to distribute the notice, default true
     *              string 'object_type' URL of the associated object type (default ActivityObject::NOTE)
     *              string 'verb' URL of the associated verb (default ActivityVerb::POST)
     *              int 'scope' Scope bitmask; default to SITE_SCOPE on private sites, 0 otherwise
     *
     * @fixme tag override
     *
     * @return Notice
     * @throws ClientException
     */
    static function saveNew($profile_id, $content, $source, array $options=null) {
        $defaults = array('uri' => null,
                          'url' => null,
                          'conversation' => null,   // URI of conversation
                          'reply_to' => null,       // This will override convo URI if the parent is known
                          'repeat_of' => null,      // This will override convo URI if the repeated notice is known
                          'scope' => null,
                          'distribute' => true,
                          'object_type' => null,
                          'verb' => null);

        if (!empty($options) && is_array($options)) {
            $options = array_merge($defaults, $options);
            extract($options);
        } else {
            extract($defaults);
        }

        if (!isset($is_local)) {
            $is_local = Notice::LOCAL_PUBLIC;
        }

        $profile = Profile::getKV('id', $profile_id);
        if (!$profile instanceof Profile) {
            // TRANS: Client exception thrown when trying to save a notice for an unknown user.
            throw new ClientException(_('Problem saving notice. Unknown user.'));
        }

        $user = User::getKV('id', $profile_id);
        if ($user instanceof User) {
            // Use the local user's shortening preferences, if applicable.
            $final = $user->shortenLinks($content);
        } else {
            $final = common_shorten_links($content);
        }

        if (Notice::contentTooLong($final)) {
            // TRANS: Client exception thrown if a notice contains too many characters.
            throw new ClientException(_('Problem saving notice. Too long.'));
        }

        if (common_config('throttle', 'enabled') && !Notice::checkEditThrottle($profile_id)) {
            common_log(LOG_WARNING, 'Excessive posting by profile #' . $profile_id . '; throttled.');
            // TRANS: Client exception thrown when a user tries to post too many notices in a given time frame.
            throw new ClientException(_('Too many notices too fast; take a breather '.
                                        'and post again in a few minutes.'));
        }

        if (common_config('site', 'dupelimit') > 0 && !Notice::checkDupes($profile_id, $final)) {
            common_log(LOG_WARNING, 'Dupe posting by profile #' . $profile_id . '; throttled.');
            // TRANS: Client exception thrown when a user tries to post too many duplicate notices in a given time frame.
            throw new ClientException(_('Too many duplicate messages too quickly;'.
                                        ' take a breather and post again in a few minutes.'));
        }

        if (!$profile->hasRight(Right::NEWNOTICE)) {
            common_log(LOG_WARNING, "Attempted post from user disallowed to post: " . $profile->nickname);

            // TRANS: Client exception thrown when a user tries to post while being banned.
            throw new ClientException(_('You are banned from posting notices on this site.'), 403);
        }

        $notice = new Notice();
        $notice->profile_id = $profile_id;

        if ($source && in_array($source, common_config('public', 'autosource'))) {
            $notice->is_local = Notice::LOCAL_NONPUBLIC;
        } else {
            $notice->is_local = $is_local;
        }

        if (!empty($created)) {
            $notice->created = $created;
        } else {
            $notice->created = common_sql_now();
        }

        if (!$notice->isLocal()) {
            // Only do these checks for non-local notices. Local notices will generate these values later.
            if (empty($uri)) {
                throw new ServerException('No URI for remote notice. Cannot accept that.');
            }
        }

        $notice->content = $final;

        $notice->source = $source;
        $notice->uri = $uri;
        $notice->url = $url;

        // Get the groups here so we can figure out replies and such
        if (!isset($groups)) {
            $groups = User_group::idsFromText($notice->content, $profile);
        }

        $reply = null;

        // Handle repeat case

        if (!empty($options['repeat_of'])) {

            // Check for a private one

            $repeat = Notice::getByID($options['repeat_of']);

            if ($profile->sameAs($repeat->getProfile())) {
                // TRANS: Client error displayed when trying to repeat an own notice.
                throw new ClientException(_('You cannot repeat your own notice.'));
            }

            if ($repeat->scope != Notice::SITE_SCOPE &&
                $repeat->scope != Notice::PUBLIC_SCOPE) {
                // TRANS: Client error displayed when trying to repeat a non-public notice.
                throw new ClientException(_('Cannot repeat a private notice.'), 403);
            }

            if (!$repeat->inScope($profile)) {
                // The generic checks above should cover this, but let's be sure!
                // TRANS: Client error displayed when trying to repeat a notice you cannot access.
                throw new ClientException(_('Cannot repeat a notice you cannot read.'), 403);
            }

            if ($profile->hasRepeated($repeat)) {
                // TRANS: Client error displayed when trying to repeat an already repeated notice.
                throw new ClientException(_('You already repeated that notice.'));
            }

            $notice->repeat_of = $repeat->id;
            $notice->conversation = $repeat->conversation;
        } else {
            $reply = null;

            // If $reply_to is specified, we check that it exists, and then
            // return it if it does
            if (!empty($reply_to)) {
                $reply = Notice::getKV('id', $reply_to);
            } elseif (in_array($source, array('xmpp', 'mail', 'sms'))) {
                // If the source lacks capability of sending the "reply_to"
                // metadata, let's try to find an inline replyto-reference.
                $reply = self::getInlineReplyTo($profile, $final);
            }

            if ($reply instanceof Notice) {
                if (!$reply->inScope($profile)) {
                    // TRANS: Client error displayed when trying to reply to a notice a the target has no access to.
                    // TRANS: %1$s is a user nickname, %2$d is a notice ID (number).
                    throw new ClientException(sprintf(_('%1$s has no access to notice %2$d.'),
                                                      $profile->nickname, $reply->id), 403);
                }

                // If it's a repeat, the reply_to should be to the original
                if ($reply->isRepeat()) {
                    $notice->reply_to = $reply->repeat_of;
                } else {
                    $notice->reply_to = $reply->id;
                }
                // But the conversation ought to be the same :)
                $notice->conversation = $reply->conversation;

                // If the original is private to a group, and notice has
                // no group specified, make it to the same group(s)

                if (empty($groups) && ($reply->scope & Notice::GROUP_SCOPE)) {
                    $groups = array();
                    $replyGroups = $reply->getGroups();
                    foreach ($replyGroups as $group) {
                        if ($profile->isMember($group)) {
                            $groups[] = $group->id;
                        }
                    }
                }

                // Scope set below
            }

            // If we don't know the reply, we might know the conversation!
            // This will happen if a known remote user replies to an
            // unknown remote user - within a known conversation.
            if (empty($notice->conversation) and !empty($options['conversation'])) {
                $conv = Conversation::getKV('uri', $options['conversation']);
                if ($conv instanceof Conversation) {
                    common_debug('Conversation stitched together from (probably) a reply to unknown remote user. Activity creation time ('.$notice->created.') should maybe be compared to conversation creation time ('.$conv->created.').');
                } else {
                    // Conversation entry with specified URI was not found, so we must create it.
                    common_debug('Conversation URI not found, so we will create it with the URI given in the options to Notice::saveNew: '.$options['conversation']);
                    // The insert in Conversation::create throws exception on failure
                    $conv = Conversation::create($options['conversation'], $notice->created);
                }
                $notice->conversation = $conv->getID();
                unset($conv);
            }
        }

        // If it's not part of a conversation, it's the beginning of a new conversation.
        if (empty($notice->conversation)) {
            $conv = Conversation::create();
            $notice->conversation = $conv->getID();
            unset($conv);
        }


        $notloc = new Notice_location();
        if (!empty($lat) && !empty($lon)) {
            $notloc->lat = $lat;
            $notloc->lon = $lon;
        }

        if (!empty($location_ns) && !empty($location_id)) {
            $notloc->location_id = $location_id;
            $notloc->location_ns = $location_ns;
        }

        if (!empty($rendered)) {
            $notice->rendered = $rendered;
        } else {
            $notice->rendered = common_render_content($final,
                                                      $notice->getProfile(),
                                                      $notice->hasParent() ? $notice->getParent() : null);
        }

        if (empty($verb)) {
            if ($notice->isRepeat()) {
                $notice->verb        = ActivityVerb::SHARE;
                $notice->object_type = ActivityObject::ACTIVITY;
            } else {
                $notice->verb        = ActivityVerb::POST;
            }
        } else {
            $notice->verb = $verb;
        }

        if (empty($object_type)) {
            $notice->object_type = (empty($notice->reply_to)) ? ActivityObject::NOTE : ActivityObject::COMMENT;
        } else {
            $notice->object_type = $object_type;
        }

        if (is_null($scope) && $reply instanceof Notice) {
            $notice->scope = $reply->scope;
        } else {
            $notice->scope = $scope;
        }

        $notice->scope = self::figureOutScope($profile, $groups, $notice->scope);

        if (Event::handle('StartNoticeSave', array(&$notice))) {

            // XXX: some of these functions write to the DB

            try {
                $notice->insert();  // throws exception on failure, if successful we have an ->id

                if (($notloc->lat && $notloc->lon) || ($notloc->location_id && $notloc->location_ns)) {
                    $notloc->notice_id = $notice->getID();
                    $notloc->insert();  // store the notice location if it had any information
                }
            } catch (Exception $e) {
                // Let's test if we managed initial insert, which would imply
                // failing on some update-part (check 'insert()'). Delete if
                // something had been stored to the database.
                if (!empty($notice->id)) {
                    $notice->delete();
                }
                throw $e;
            }
        }

        // Only save 'attention' and metadata stuff (URLs, tags...) stuff if
        // the activityverb is a POST (since stuff like repeat, favorite etc.
        // reasonably handle notifications themselves.
        if (ActivityUtils::compareVerbs($notice->verb, array(ActivityVerb::POST))) {
            if (isset($replies)) {
                $notice->saveKnownReplies($replies);
            } else {
                $notice->saveReplies();
            }

            if (isset($tags)) {
                $notice->saveKnownTags($tags);
            } else {
                $notice->saveTags();
            }

            // Note: groups may save tags, so must be run after tags are saved
            // to avoid errors on duplicates.
            // Note: groups should always be set.

            $notice->saveKnownGroups($groups);

            if (isset($urls)) {
                $notice->saveKnownUrls($urls);
            } else {
                $notice->saveUrls();
            }
        }

        if ($distribute) {
            // Prepare inbox delivery, may be queued to background.
            $notice->distribute();
        }

        return $notice;
    }

    static function saveActivity(Activity $act, Profile $actor, array $options=array())
    {
        // First check if we're going to let this Activity through from the specific actor
        if (!$actor->hasRight(Right::NEWNOTICE)) {
            common_log(LOG_WARNING, "Attempted post from user disallowed to post: " . $actor->getNickname());

            // TRANS: Client exception thrown when a user tries to post while being banned.
            throw new ClientException(_m('You are banned from posting notices on this site.'), 403);
        }
        if (common_config('throttle', 'enabled') && !self::checkEditThrottle($actor->id)) {
            common_log(LOG_WARNING, 'Excessive posting by profile #' . $actor->id . '; throttled.');
            // TRANS: Client exception thrown when a user tries to post too many notices in a given time frame.
            throw new ClientException(_m('Too many notices too fast; take a breather '.
                                        'and post again in a few minutes.'));
        }

        // Get ActivityObject properties
        $actobj = null;
        if (!empty($act->id)) {
            // implied object
            $options['uri'] = $act->id;
            $options['url'] = $act->link;
        } else {
            $actobj = count($act->objects)===1 ? $act->objects[0] : null;
            if (!is_null($actobj) && !empty($actobj->id)) {
                $options['uri'] = $actobj->id;
                if ($actobj->link) {
                    $options['url'] = $actobj->link;
                } elseif (preg_match('!^https?://!', $actobj->id)) {
                    $options['url'] = $actobj->id;
                }
            }
        }

        $defaults = array(
                          'groups'   => array(),
                          'is_local' => $actor->isLocal() ? self::LOCAL_PUBLIC : self::REMOTE,
                          'mentions' => array(),
                          'reply_to' => null,
                          'repeat_of' => null,
                          'scope' => null,
                          'source' => 'unknown',
                          'tags' => array(),
                          'uri' => null,
                          'url' => null,
                          'urls' => array(),
                          'distribute' => true);

        // options will have default values when nothing has been supplied
        $options = array_merge($defaults, $options);
        foreach (array_keys($defaults) as $key) {
            // Only convert the keynames we specify ourselves from 'defaults' array into variables
            $$key = $options[$key];
        }
        extract($options, EXTR_SKIP);

        // dupe check
        $stored = new Notice();
        if (!empty($uri) && !ActivityUtils::compareVerbs($act->verb, array(ActivityVerb::DELETE))) {
            $stored->uri = $uri;
            if ($stored->find()) {
                common_debug('cannot create duplicate Notice URI: '.$stored->uri);
                // I _assume_ saving a Notice with a colliding URI means we're really trying to
                // save the same notice again...
                throw new AlreadyFulfilledException('Notice URI already exists');
            }
        }

        // NOTE: Sandboxed users previously got all the notices _during_
        // sandbox period set to to is_local=Notice::LOCAL_NONPUBLIC here.
        // Since then we have started just filtering _when_ it gets shown
        // instead of creating a mixed jumble of differently scoped notices.

        if ($source && in_array($source, common_config('public', 'autosource'))) {
            $stored->is_local = Notice::LOCAL_NONPUBLIC;
        } else {
            $stored->is_local = intval($is_local);
        }

        if (!$stored->isLocal()) {
            // Only do these checks for non-local notices. Local notices will generate these values later.
            if (!common_valid_http_url($url)) {
                common_debug('Bad notice URL: ['.$url.'], URI: ['.$uri.']. Cannot link back to original! This is normal for shared notices etc.');
            }
            if (empty($uri)) {
                throw new ServerException('No URI for remote notice. Cannot accept that.');
            }
        }

        $stored->profile_id = $actor->getID();
        $stored->source = $source;
        $stored->uri = $uri;
        $stored->url = $url;
        $stored->verb = $act->verb;

        // we use mb_strlen because it _might_ be that the content is just the string "0"...
        $content = mb_strlen($act->content) ? $act->content : $act->summary;
        if (mb_strlen($content)===0 && !is_null($actobj)) {
            $content = mb_strlen($actobj->content) ? $actobj->content : $actobj->summary;
        }
        // Strip out any bad HTML from $content. URI.Base is used to sort out relative URLs.
        $stored->rendered = common_purify($content, ['URI.Base' => $stored->url ?: null]);
        $stored->content  = common_strip_html($stored->getRendered(), true, true);
        if (trim($stored->content) === '') {
            // TRANS: Error message when the plain text content of a notice has zero length.
            throw new ClientException(_('Empty notice content, will not save this.'));
        }
        unset($content);    // garbage collect

        // Maybe a missing act-time should be fatal if the actor is not local?
        if (!empty($act->time)) {
            $stored->created = common_sql_date($act->time);
        } else {
            $stored->created = common_sql_now();
        }

        $reply = null;  // this will store the in-reply-to Notice if found
        $replyUris = [];    // this keeps a list of possible URIs to look up
        if ($act->context instanceof ActivityContext && !empty($act->context->replyToID)) {
            $replyUris[] = $act->context->replyToID;
        }
        if ($act->target instanceof ActivityObject && !empty($act->target->id)) {
            $replyUris[] = $act->target->id;
        }
        foreach (array_unique($replyUris) as $replyUri) {
            $reply = self::getKV('uri', $replyUri);
            // Only do remote fetching if we're not a private site
            if (!common_config('site', 'private') && !$reply instanceof Notice) {
                // the URI is the object we're looking for, $actor is a
                // Profile that surely knows of it and &$reply where it
                // will be stored when fetched
                Event::handle('FetchRemoteNotice', array($replyUri, $actor, &$reply));
            }
            // we got what we're in-reply-to now, so let's move on
            if ($reply instanceof Notice) {
                break;
            }
            // otherwise reset whatever we might've gotten from the event
            $reply = null;
        }
        unset($replyUris);  // garbage collect

        if ($reply instanceof Notice) {
            if (!$reply->inScope($actor)) {
                // TRANS: Client error displayed when trying to reply to a notice a the target has no access to.
                // TRANS: %1$s is a user nickname, %2$d is a notice ID (number).
                throw new ClientException(sprintf(_m('%1$s has no right to reply to notice %2$d.'), $actor->getNickname(), $reply->id), 403);
            }

            $stored->reply_to     = $reply->id;
            $stored->conversation = $reply->conversation;

            // If the original is private to a group, and notice has no group specified,
            // make it to the same group(s)
            if (empty($groups) && ($reply->scope & Notice::GROUP_SCOPE)) {
                $replyGroups = $reply->getGroups();
                foreach ($replyGroups as $group) {
                    if ($actor->isMember($group)) {
                        $groups[] = $group->id;
                    }
                }
            }

            if (is_null($scope)) {
                $scope = $reply->scope;
            }
        } else {
            // If we don't know the reply, we might know the conversation!
            // This will happen if a known remote user replies to an
            // unknown remote user - within a known conversation.
            if (empty($stored->conversation) and !empty($act->context->conversation)) {
                $conv = Conversation::getKV('uri', $act->context->conversation);
                if ($conv instanceof Conversation) {
                    common_debug('Conversation stitched together from (probably) a reply activity to unknown remote user. Activity creation time ('.$stored->created.') should maybe be compared to conversation creation time ('.$conv->created.').');
                } else {
                    // Conversation entry with specified URI was not found, so we must create it.
                    common_debug('Conversation URI not found, so we will create it with the URI given in the context of the activity: '.$act->context->conversation);
                    // The insert in Conversation::create throws exception on failure
                    $conv = Conversation::create($act->context->conversation, $stored->created);
                }
                $stored->conversation = $conv->getID();
                unset($conv);
            }
        }
        unset($reply);  // garbage collect

        // If it's not part of a conversation, it's the beginning of a new conversation.
        if (empty($stored->conversation)) {
            $conv = Conversation::create();
            $stored->conversation = $conv->getID();
            unset($conv);
        }

        $notloc = null;
        if ($act->context instanceof ActivityContext) {
            if ($act->context->location instanceof Location) {
                $notloc = Notice_location::fromLocation($act->context->location);
            }
        } else {
            $act->context = new ActivityContext();
        }

        if (array_key_exists(ActivityContext::ATTN_PUBLIC, $act->context->attention)) {
            $stored->scope = Notice::PUBLIC_SCOPE;
            // TODO: maybe we should actually keep this? if the saveAttentions thing wants to use it...
            unset($act->context->attention[ActivityContext::ATTN_PUBLIC]);
        } else {
            $stored->scope = self::figureOutScope($actor, $groups, $scope);
        }

        foreach ($act->categories as $cat) {
            if ($cat->term) {
                $term = common_canonical_tag($cat->term);
                if (!empty($term)) {
                    $tags[] = $term;
                }
            }
        }

        foreach ($act->enclosures as $href) {
            // @todo FIXME: Save these locally or....?
            $urls[] = $href;
        }

        if (ActivityUtils::compareVerbs($stored->verb, array(ActivityVerb::POST))) {
            if (empty($act->objects[0]->type)) {
                // Default type for the post verb is 'note', but we know it's
                // a 'comment' if it is in reply to something.
                $stored->object_type = empty($stored->reply_to) ? ActivityObject::NOTE : ActivityObject::COMMENT;
            } else {
                //TODO: Is it safe to always return a relative URI? The
                // JSON version of ActivityStreams always use it, so we
                // should definitely be able to handle it...
                $stored->object_type = ActivityUtils::resolveUri($act->objects[0]->type, true);
            }
        }

        if (Event::handle('StartNoticeSave', array(&$stored))) {
            // XXX: some of these functions write to the DB

            try {
                $result = $stored->insert();    // throws exception on error

                if ($notloc instanceof Notice_location) {
                    $notloc->notice_id = $stored->getID();
                    $notloc->insert();
                }

                $orig = clone($stored); // for updating later in this try clause

                $object = null;
                Event::handle('StoreActivityObject', array($act, $stored, $options, &$object));
                if (empty($object)) {
                    throw new NoticeSaveException('Unsuccessful call to StoreActivityObject '._ve($stored->getUri()) . ': '._ve($act->asString()));
                }
                unset($object);

                // If something changed in the Notice during StoreActivityObject
                $stored->update($orig);
            } catch (Exception $e) {
                if (empty($stored->id)) {
                    common_debug('Failed to save stored object entry in database ('.$e->getMessage().')');
                } else {
                    common_debug('Failed to store activity object in database ('.$e->getMessage().'), deleting notice id '.$stored->id);
                    $stored->delete();
                }
                throw $e;
            }
        }
        unset($notloc); // garbage collect

        if (!$stored instanceof Notice) {
            throw new ServerException('StartNoticeSave did not give back a Notice.');
        } elseif (empty($stored->id)) {
            throw new ServerException('Supposedly saved Notice has no ID.');
        }

        // Only save 'attention' and metadata stuff (URLs, tags...) stuff if
        // the activityverb is a POST (since stuff like repeat, favorite etc.
        // reasonably handle notifications themselves.
        if (ActivityUtils::compareVerbs($stored->verb, array(ActivityVerb::POST))) {

            if (!empty($tags)) {
                $stored->saveKnownTags($tags);
            } else {
                $stored->saveTags();
            }

            // Note: groups may save tags, so must be run after tags are saved
            // to avoid errors on duplicates.
            $stored->saveAttentions($act->context->attention);

            if (!empty($urls)) {
                $stored->saveKnownUrls($urls);
            } else {
                $stored->saveUrls();
            }
        }

        if ($distribute) {
            // Prepare inbox delivery, may be queued to background.
            $stored->distribute();
        }

        return $stored;
    }

    static public function figureOutScope(Profile $actor, array $groups, $scope=null) {
        $scope = is_null($scope) ? self::defaultScope() : intval($scope);

        // For private streams
        try {
            $user = $actor->getUser();
            // FIXME: We can't do bit comparison with == (Legacy StatusNet thing. Let's keep it for now.)
            if ($user->private_stream && ($scope === Notice::PUBLIC_SCOPE || $scope === Notice::SITE_SCOPE)) {
                $scope |= Notice::FOLLOWER_SCOPE;
            }
        } catch (NoSuchUserException $e) {
            // TODO: Not a local user, so we don't know about scope preferences... yet!
        }

        // Force the scope for private groups
        foreach ($groups as $group_id) {
            try {
                $group = User_group::getByID($group_id);
                if ($group->force_scope) {
                    $scope |= Notice::GROUP_SCOPE;
                    break;
                }
            } catch (Exception $e) {
                common_log(LOG_ERR, 'Notice figureOutScope threw exception: '.$e->getMessage());
            }
        }

        return $scope;
    }

    function blowOnInsert($conversation = false)
    {
        $this->blowStream('profile:notice_ids:%d', $this->profile_id);

        if ($this->isPublic()) {
            $this->blowStream('public');
            $this->blowStream('networkpublic');
        }

        if ($this->conversation) {
            self::blow('notice:list-ids:conversation:%s', $this->conversation);
            self::blow('conversation:notice_count:%d', $this->conversation);
        }

        if ($this->isRepeat()) {
            // XXX: we should probably only use one of these
            $this->blowStream('notice:repeats:%d', $this->repeat_of);
            self::blow('notice:list-ids:repeat_of:%d', $this->repeat_of);
        }

        $original = Notice::getKV('id', $this->repeat_of);

        if ($original instanceof Notice) {
            $originalUser = User::getKV('id', $original->profile_id);
            if ($originalUser instanceof User) {
                $this->blowStream('user:repeats_of_me:%d', $originalUser->id);
            }
        }

        $profile = Profile::getKV($this->profile_id);

        if ($profile instanceof Profile) {
            $profile->blowNoticeCount();
        }

        $ptags = $this->getProfileTags();
        foreach ($ptags as $ptag) {
            $ptag->blowNoticeStreamCache();
        }
    }

    /**
     * Clear cache entries related to this notice at delete time.
     * Necessary to avoid breaking paging on public, profile timelines.
     */
    function blowOnDelete()
    {
        $this->blowOnInsert();

        self::blow('profile:notice_ids:%d;last', $this->profile_id);

        if ($this->isPublic()) {
            self::blow('public;last');
            self::blow('networkpublic;last');
        }

        self::blow('fave:by_notice', $this->id);

        if ($this->conversation) {
            // In case we're the first, will need to calc a new root.
            self::blow('notice:conversation_root:%d', $this->conversation);
        }

        $ptags = $this->getProfileTags();
        foreach ($ptags as $ptag) {
            $ptag->blowNoticeStreamCache(true);
        }
    }

    function blowStream()
    {
        $c = self::memcache();

        if (empty($c)) {
            return false;
        }

        $args = func_get_args();
        $format = array_shift($args);
        $keyPart = vsprintf($format, $args);
        $cacheKey = Cache::key($keyPart);
        $c->delete($cacheKey);

        // delete the "last" stream, too, if this notice is
        // older than the top of that stream

        $lastKey = $cacheKey.';last';

        $lastStr = $c->get($lastKey);

        if ($lastStr !== false) {
            $window     = explode(',', $lastStr);
            $lastID     = $window[0];
            $lastNotice = Notice::getKV('id', $lastID);
            if (!$lastNotice instanceof Notice // just weird
                || strtotime($lastNotice->created) >= strtotime($this->created)) {
                $c->delete($lastKey);
            }
        }
    }

    /** save all urls in the notice to the db
     *
     * follow redirects and save all available file information
     * (mimetype, date, size, oembed, etc.)
     *
     * @return void
     */
    function saveUrls() {
        if (common_config('attachments', 'process_links')) {
            common_replace_urls_callback($this->content, array($this, 'saveUrl'), $this);
        }
    }

    /**
     * Save the given URLs as related links/attachments to the db
     *
     * follow redirects and save all available file information
     * (mimetype, date, size, oembed, etc.)
     *
     * @return void
     */
    function saveKnownUrls($urls)
    {
        if (common_config('attachments', 'process_links')) {
            // @fixme validation?
            foreach (array_unique($urls) as $url) {
                $this->saveUrl($url, $this);
            }
        }
    }

    /**
     * @private callback
     */
    function saveUrl($url, Notice $notice) {
        try {
            File::processNew($url, $notice);
        } catch (ServerException $e) {
            // Could not save URL. Log it?
        }
    }

    static function checkDupes($profile_id, $content) {
        $profile = Profile::getKV($profile_id);
        if (!$profile instanceof Profile) {
            return false;
        }
        $notice = $profile->getNotices(0, CachingNoticeStream::CACHE_WINDOW);
        if (!empty($notice)) {
            $last = 0;
            while ($notice->fetch()) {
                if (time() - strtotime($notice->created) >= common_config('site', 'dupelimit')) {
                    return true;
                } else if ($notice->content == $content) {
                    return false;
                }
            }
        }
        // If we get here, oldest item in cache window is not
        // old enough for dupe limit; do direct check against DB
        $notice = new Notice();
        $notice->profile_id = $profile_id;
        $notice->content = $content;
        $threshold = common_sql_date(time() - common_config('site', 'dupelimit'));
        $notice->whereAdd(sprintf("created > '%s'", $notice->escape($threshold)));

        $cnt = $notice->count();
        return ($cnt == 0);
    }

    static function checkEditThrottle($profile_id) {
        $profile = Profile::getKV($profile_id);
        if (!$profile instanceof Profile) {
            return false;
        }
        // Get the Nth notice
        $notice = $profile->getNotices(common_config('throttle', 'count') - 1, 1);
        if ($notice && $notice->fetch()) {
            // If the Nth notice was posted less than timespan seconds ago
            if (time() - strtotime($notice->created) <= common_config('throttle', 'timespan')) {
                // Then we throttle
                return false;
            }
        }
        // Either not N notices in the stream, OR the Nth was not posted within timespan seconds
        return true;
    }

	protected $_attachments = array();

    function attachments() {
		if (isset($this->_attachments[$this->id])) {
            return $this->_attachments[$this->id];
        }

        $f2ps = File_to_post::listGet('post_id', array($this->id));
		$ids = array();
		foreach ($f2ps[$this->id] as $f2p) {
            $ids[] = $f2p->file_id;
        }

        return $this->_setAttachments(File::multiGet('id', $ids)->fetchAll());
    }

	public function _setAttachments(array $attachments)
	{
	    return $this->_attachments[$this->id] = $attachments;
	}

    static function publicStream($offset=0, $limit=20, $since_id=null, $max_id=null)
    {
        $stream = new PublicNoticeStream();
        return $stream->getNotices($offset, $limit, $since_id, $max_id);
    }

    static function conversationStream($id, $offset=0, $limit=20, $since_id=null, $max_id=null, Profile $scoped=null)
    {
        $stream = new ConversationNoticeStream($id, $scoped);
        return $stream->getNotices($offset, $limit, $since_id, $max_id);
    }

    /**
     * Is this notice part of an active conversation?
     *
     * @return boolean true if other messages exist in the same
     *                 conversation, false if this is the only one
     */
    function hasConversation()
    {
        if (empty($this->conversation)) {
            // this notice is not part of a conversation apparently
            // FIXME: all notices should have a conversation value, right?
            return false;
        }

        //FIXME: Get the Profile::current() stuff some other way
        // to avoid confusion between queue processing and session.
        $notice = self::conversationStream($this->conversation, 1, 1, null, null, Profile::current());

        // if our "offset 1, limit 1" query got a result, return true else false
        return $notice->N > 0;
    }

    /**
     * Grab the earliest notice from this conversation.
     *
     * @return Notice or null
     */
    function conversationRoot($profile=-1)
    {
        // XXX: can this happen?

        if (empty($this->conversation)) {
            return null;
        }

        // Get the current profile if not specified

        if (is_int($profile) && $profile == -1) {
            $profile = Profile::current();
        }

        // If this notice is out of scope, no root for you!

        if (!$this->inScope($profile)) {
            return null;
        }

        // If this isn't a reply to anything, then it's its own
        // root if it's the earliest notice in the conversation:

        if (empty($this->reply_to)) {
            $root = new Notice;
            $root->conversation = $this->conversation;
            $root->orderBy('notice.created ASC');
            $root->find(true);  // true means "fetch first result"
            $root->free();
            return $root;
        }

        if (is_null($profile)) {
            $keypart = sprintf('notice:conversation_root:%d:null', $this->id);
        } else {
            $keypart = sprintf('notice:conversation_root:%d:%d',
                               $this->id,
                               $profile->id);
        }

        $root = self::cacheGet($keypart);

        if ($root !== false && $root->inScope($profile)) {
            return $root;
        }

        $last = $this;
        while (true) {
            try {
                $parent = $last->getParent();
                if ($parent->inScope($profile)) {
                    $last = $parent;
                    continue;
                }
            } catch (NoParentNoticeException $e) {
                // Latest notice has no parent
            } catch (NoResultException $e) {
                // Notice was not found, so we can't go further up in the tree.
                // FIXME: Maybe we should do this in a more stable way where deleted
                // notices won't break conversation chains?
            }
            // No parent, or parent out of scope
            $root = $last;
            break;
        }

        self::cacheSet($keypart, $root);

        return $root;
    }

    /**
     * Pull up a full list of local recipients who will be getting
     * this notice in their inbox. Results will be cached, so don't
     * change the input data wily-nilly!
     *
     * @param array $groups optional list of Group objects;
     *              if left empty, will be loaded from group_inbox records
     * @param array $recipient optional list of reply profile ids
     *              if left empty, will be loaded from reply records
     * @return array associating recipient user IDs with an inbox source constant
     */
    function whoGets(array $groups=null, array $recipients=null)
    {
        $c = self::memcache();

        if (!empty($c)) {
            $ni = $c->get(Cache::key('notice:who_gets:'.$this->id));
            if ($ni !== false) {
                return $ni;
            }
        }

        if (is_null($recipients)) {
            $recipients = $this->getReplies();
        }

        $ni = array();

        // Give plugins a chance to add folks in at start...
        if (Event::handle('StartNoticeWhoGets', array($this, &$ni))) {

            $users = $this->getSubscribedUsers();
            foreach ($users as $id) {
                $ni[$id] = NOTICE_INBOX_SOURCE_SUB;
            }

            if (is_null($groups)) {
                $groups = $this->getGroups();
            }
            foreach ($groups as $group) {
                $users = $group->getUserMembers();
                foreach ($users as $id) {
                    if (!array_key_exists($id, $ni)) {
                        $ni[$id] = NOTICE_INBOX_SOURCE_GROUP;
                    }
                }
            }

            $ptAtts = $this->getAttentionsFromProfileTags();
            foreach ($ptAtts as $key=>$val) {
                if (!array_key_exists($key, $ni)) {
                    $ni[$key] = $val;
                }
            }

            foreach ($recipients as $recipient) {
                if (!array_key_exists($recipient, $ni)) {
                    $ni[$recipient] = NOTICE_INBOX_SOURCE_REPLY;
                }
            }

            // Exclude any deleted, non-local, or blocking recipients.
            $profile = $this->getProfile();
            $originalProfile = null;
            if ($this->isRepeat()) {
                // Check blocks against the original notice's poster as well.
                $original = Notice::getKV('id', $this->repeat_of);
                if ($original instanceof Notice) {
                    $originalProfile = $original->getProfile();
                }
            }

            foreach ($ni as $id => $source) {
                try {
                    $user = User::getKV('id', $id);
                    if (!$user instanceof User ||
                        $user->hasBlocked($profile) ||
                        ($originalProfile && $user->hasBlocked($originalProfile))) {
                        unset($ni[$id]);
                    }
                } catch (UserNoProfileException $e) {
                    // User doesn't have a profile; invalid; skip them.
                    unset($ni[$id]);
                }
            }

            // Give plugins a chance to filter out...
            Event::handle('EndNoticeWhoGets', array($this, &$ni));
        }

        if (!empty($c)) {
            // XXX: pack this data better
            $c->set(Cache::key('notice:who_gets:'.$this->id), $ni);
        }

        return $ni;
    }

    function getSubscribedUsers()
    {
        $user = new User();

        if(common_config('db','quote_identifiers'))
          $user_table = '"user"';
        else $user_table = 'user';

        $qry =
          'SELECT id ' .
          'FROM '. $user_table .' JOIN subscription '.
          'ON '. $user_table .'.id = subscription.subscriber ' .
          'WHERE subscription.subscribed = %d ';

        $user->query(sprintf($qry, $this->profile_id));

        $ids = array();

        while ($user->fetch()) {
            $ids[] = $user->id;
        }

        $user->free();

        return $ids;
    }

    function getProfileTags()
    {
        $ptags   = array();
        try {
            $profile = $this->getProfile();
            $list    = $profile->getOtherTags($profile);

            while($list->fetch()) {
                $ptags[] = clone($list);
            }
        } catch (Exception $e) {
            common_log(LOG_ERR, "Error during Notice->getProfileTags() for id=={$this->getID()}: {$e->getMessage()}");
        }

        return $ptags;
    }

    public function getAttentionsFromProfileTags()
    {
        $ni = array();
        $ptags = $this->getProfileTags();
        foreach ($ptags as $ptag) {
            $users = $ptag->getUserSubscribers();
            foreach ($users as $id) {
                $ni[$id] = NOTICE_INBOX_SOURCE_PROFILE_TAG;
            }
        }
        return $ni;
    }

    /**
     * Record this notice to the given group inboxes for delivery.
     * Overrides the regular parsing of !group markup.
     *
     * @param string $group_ids
     * @fixme might prefer URIs as identifiers, as for replies?
     *        best with generalizations on user_group to support
     *        remote groups better.
     */
    function saveKnownGroups(array $group_ids)
    {
        $groups = array();
        foreach (array_unique($group_ids) as $id) {
            $group = User_group::getKV('id', $id);
            if ($group instanceof User_group) {
                common_log(LOG_DEBUG, "Local delivery to group id $id, $group->nickname");
                $result = $this->addToGroupInbox($group);
                if (!$result) {
                    common_log_db_error($gi, 'INSERT', __FILE__);
                }

                if (common_config('group', 'addtag')) {
                    // we automatically add a tag for every group name, too

                    $tag = Notice_tag::pkeyGet(array('tag' => common_canonical_tag($group->nickname),
                                                     'notice_id' => $this->id));

                    if (is_null($tag)) {
                        $this->saveTag($group->nickname);
                    }
                }

                $groups[] = clone($group);
            } else {
                common_log(LOG_ERR, "Local delivery to group id $id skipped, doesn't exist");
            }
        }

        return $groups;
    }

    function addToGroupInbox(User_group $group)
    {
        $gi = Group_inbox::pkeyGet(array('group_id' => $group->id,
                                         'notice_id' => $this->id));

        if (!$gi instanceof Group_inbox) {

            $gi = new Group_inbox();

            $gi->group_id  = $group->id;
            $gi->notice_id = $this->id;
            $gi->created   = $this->created;

            $result = $gi->insert();

            if (!$result) {
                common_log_db_error($gi, 'INSERT', __FILE__);
                // TRANS: Server exception thrown when an update for a group inbox fails.
                throw new ServerException(_('Problem saving group inbox.'));
            }

            self::blow('user_group:notice_ids:%d', $gi->group_id);
        }

        return true;
    }

    function saveAttentions(array $uris)
    {
        foreach ($uris as $uri=>$type) {
            try {
                $target = Profile::fromUri($uri);
            } catch (UnknownUriException $e) {
                common_log(LOG_WARNING, "Unable to determine profile for URI '$uri'");
                continue;
            }

            try {
                $this->saveAttention($target);
            } catch (AlreadyFulfilledException $e) {
                common_debug('Attention already exists: '.var_export($e->getMessage(),true));
            } catch (Exception $e) {
                common_log(LOG_ERR, "Could not save notice id=={$this->getID()} attention for profile id=={$target->getID()}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Saves an attention for a profile (user or group) which means
     * it shows up in their home feed and such.
     */
    function saveAttention(Profile $target, $reason=null)
    {
        if ($target->isGroup()) {
            // FIXME: Make sure we check (for both local and remote) users are in the groups they send to!

            // legacy notification method, will still be in use for quite a while I think
            $this->addToGroupInbox($target->getGroup());
        } else {
            if ($target->hasBlocked($this->getProfile())) {
                common_log(LOG_INFO, "Not saving reply to profile {$target->id} ($uri) from sender {$sender->id} because of a block.");
                return false;
            }
        }

        if ($target->isLocal()) {
            // legacy notification method, will still be in use for quite a while I think
            $this->saveReply($target->getID());
        }

        $att = Attention::saveNew($this, $target, $reason);
        return true;
    }

    /**
     * Save reply records indicating that this notice needs to be
     * delivered to the local users with the given URIs.
     *
     * Since this is expected to be used when saving foreign-sourced
     * messages, we won't deliver to any remote targets as that's the
     * source service's responsibility.
     *
     * Mail notifications etc will be handled later.
     *
     * @param array  $uris   Array of unique identifier URIs for recipients
     */
    function saveKnownReplies(array $uris)
    {
        if (empty($uris)) {
            return;
        }

        $sender = $this->getProfile();

        foreach (array_unique($uris) as $uri) {
            try {
                $profile = Profile::fromUri($uri);
            } catch (UnknownUriException $e) {
                common_log(LOG_WARNING, "Unable to determine profile for URI '$uri'");
                continue;
            }

            if ($profile->hasBlocked($sender)) {
                common_log(LOG_INFO, "Not saving reply to profile {$profile->id} ($uri) from sender {$sender->id} because of a block.");
                continue;
            }

            $this->saveReply($profile->getID());
            self::blow('reply:stream:%d', $profile->getID());
        }
    }

    /**
     * Pull @-replies from this message's content in StatusNet markup format
     * and save reply records indicating that this message needs to be
     * delivered to those users.
     *
     * Mail notifications to local profiles will be sent later.
     *
     * @return array of integer profile IDs
     */

    function saveReplies()
    {
        $sender = $this->getProfile();

        $replied = array();

        // If it's a reply, save for the replied-to author
        try {
            $parent = $this->getParent();
            $parentauthor = $parent->getProfile();
            $this->saveReply($parentauthor->getID());
            $replied[$parentauthor->getID()] = 1;
            self::blow('reply:stream:%d', $parentauthor->getID());
        } catch (NoParentNoticeException $e) {
            // Not a reply, since it has no parent!
            $parent = null;
        } catch (NoResultException $e) {
            // Parent notice was probably deleted
            $parent = null;
        }

        // @todo ideally this parser information would only
        // be calculated once.

        $mentions = common_find_mentions($this->content, $sender, $parent);

        foreach ($mentions as $mention) {

            foreach ($mention['mentioned'] as $mentioned) {

                // skip if they're already covered
                if (array_key_exists($mentioned->id, $replied)) {
                    continue;
                }

                // Don't save replies from blocked profile to local user
                if ($mentioned->hasBlocked($sender)) {
                    continue;
                }

                $this->saveReply($mentioned->id);
                $replied[$mentioned->id] = 1;
                self::blow('reply:stream:%d', $mentioned->id);
            }
        }

        $recipientIds = array_keys($replied);

        return $recipientIds;
    }

    function saveReply($profileId)
    {
        $reply = new Reply();

        $reply->notice_id  = $this->id;
        $reply->profile_id = $profileId;
        $reply->modified   = $this->created;

        $reply->insert();

        return $reply;
    }

    protected $_attentionids = array();

    /**
     * Pull the complete list of known activity context attentions for this notice.
     *
     * @return array of integer profile ids (also group profiles)
     */
    function getAttentionProfileIDs()
    {
        if (!isset($this->_attentionids[$this->getID()])) {
            $atts = Attention::multiGet('notice_id', array($this->getID()));
            // (array)null means empty array
            $this->_attentionids[$this->getID()] = (array)$atts->fetchAll('profile_id');
        }
        return $this->_attentionids[$this->getID()];
    }

    protected $_replies = array();

    /**
     * Pull the complete list of @-mentioned profile IDs for this notice.
     *
     * @return array of integer profile ids
     */
    function getReplies()
    {
        if (!isset($this->_replies[$this->getID()])) {
            $mentions = Reply::multiGet('notice_id', array($this->getID()));
            $this->_replies[$this->getID()] = $mentions->fetchAll('profile_id');
        }
        return $this->_replies[$this->getID()];
    }

    function _setReplies($replies)
    {
        $this->_replies[$this->getID()] = $replies;
    }

    /**
     * Pull the complete list of @-reply targets for this notice.
     *
     * @return array of Profiles
     */
    function getAttentionProfiles()
    {
        $ids = array_unique(array_merge($this->getReplies(), $this->getGroupProfileIDs(), $this->getAttentionProfileIDs()));

        $profiles = Profile::multiGet('id', (array)$ids);

        return $profiles->fetchAll();
    }

    /**
     * Send e-mail notifications to local @-reply targets.
     *
     * Replies must already have been saved; this is expected to be run
     * from the distrib queue handler.
     */
    function sendReplyNotifications()
    {
        // Don't send reply notifications for repeats
        if ($this->isRepeat()) {
            return array();
        }

        $recipientIds = $this->getReplies();
        if (Event::handle('StartNotifyMentioned', array($this, &$recipientIds))) {
            require_once INSTALLDIR.'/lib/mail.php';

            foreach ($recipientIds as $recipientId) {
                try {
                    $user = User::getByID($recipientId);
                    mail_notify_attn($user->getProfile(), $this);
                } catch (NoResultException $e) {
                    // No such user
                }
            }
            Event::handle('EndNotifyMentioned', array($this, $recipientIds));
        }
    }

    /**
     * Pull list of Profile IDs of groups this notice addresses.
     *
     * @return array of Group _profile_ IDs
     */

    function getGroupProfileIDs()
    {
        $ids = array();

		foreach ($this->getGroups() as $group) {
		    $ids[] = $group->profile_id;
		}

        return $ids;
    }

    /**
     * Pull list of groups this notice needs to be delivered to,
     * as previously recorded by saveKnownGroups().
     *
     * @return array of Group objects
     */

    protected $_groups = array();

    function getGroups()
    {
        // Don't save groups for repeats

        if (!empty($this->repeat_of)) {
            return array();
        }

        if (isset($this->_groups[$this->id])) {
            return $this->_groups[$this->id];
        }

        $gis = Group_inbox::listGet('notice_id', array($this->id));

        $ids = array();

		foreach ($gis[$this->id] as $gi) {
		    $ids[] = $gi->group_id;
		}

		$groups = User_group::multiGet('id', $ids);
		$this->_groups[$this->id] = $groups->fetchAll();
		return $this->_groups[$this->id];
    }

    function _setGroups($groups)
    {
        $this->_groups[$this->id] = $groups;
    }

    /**
     * Convert a notice into an activity for export.
     *
     * @param Profile $scoped   The currently logged in/scoped profile
     *
     * @return Activity activity object representing this Notice.
     */

    function asActivity(Profile $scoped=null)
    {
        $act = self::cacheGet(Cache::codeKey('notice:as-activity:'.$this->id));

        if ($act instanceof Activity) {
            return $act;
        }
        $act = new Activity();

        if (Event::handle('StartNoticeAsActivity', array($this, $act, $scoped))) {

            $act->id      = $this->uri;
            $act->time    = strtotime($this->created);
            try {
                $act->link    = $this->getUrl();
            } catch (InvalidUrlException $e) {
                // The notice is probably a share or similar, which don't
                // have a representational URL of their own.
            }
            $act->content = common_xml_safe_str($this->getRendered());

            $profile = $this->getProfile();

            $act->actor            = $profile->asActivityObject();
            $act->actor->extra[]   = $profile->profileInfo($scoped);

            $act->verb = $this->verb;

            if (!$this->repeat_of) {
                $act->objects[] = $this->asActivityObject();
            }

            // XXX: should this be handled by default processing for object entry?

            // Categories

            $tags = $this->getTags();

            foreach ($tags as $tag) {
                $cat       = new AtomCategory();
                $cat->term = $tag;

                $act->categories[] = $cat;
            }

            // Enclosures
            // XXX: use Atom Media and/or File activity objects instead

            $attachments = $this->attachments();

            foreach ($attachments as $attachment) {
                // Include local attachments in Activity
                if (!empty($attachment->filename)) {
                    $act->enclosures[] = $attachment->getEnclosure();
                }
            }

            $ctx = new ActivityContext();

            try {
                $reply = $this->getParent();
                $ctx->replyToID  = $reply->getUri();
                $ctx->replyToUrl = $reply->getUrl(true);    // true for fallback to local URL, less messy
            } catch (NoParentNoticeException $e) {
                // This is not a reply to something
            } catch (NoResultException $e) {
                // Parent notice was probably deleted
            }

            try {
                $ctx->location = Notice_location::locFromStored($this);
            } catch (ServerException $e) {
                $ctx->location = null;
            }

            $conv = null;

            if (!empty($this->conversation)) {
                $conv = Conversation::getKV('id', $this->conversation);
                if ($conv instanceof Conversation) {
                    $ctx->conversation = $conv->uri;
                }
            }

            // This covers the legacy getReplies and getGroups too which get their data
            // from entries stored via Notice::saveNew (which we want to move away from)...
            foreach ($this->getAttentionProfiles() as $target) {
                // User and group profiles which get the attention of this notice
                $ctx->attention[$target->getUri()] = $target->getObjectType();
            }

            switch ($this->scope) {
            case Notice::PUBLIC_SCOPE:
                $ctx->attention[ActivityContext::ATTN_PUBLIC] = ActivityObject::COLLECTION;
                break;
            case Notice::FOLLOWER_SCOPE:
                $surl = common_local_url("subscribers", array('nickname' => $profile->nickname));
                $ctx->attention[$surl] = ActivityObject::COLLECTION;
                break;
            }

            $act->context = $ctx;

            $source = $this->getSource();

            if ($source instanceof Notice_source) {
                $act->generator = ActivityObject::fromNoticeSource($source);
            }

            // Source

            $atom_feed = $profile->getAtomFeed();

            if (!empty($atom_feed)) {

                $act->source = new ActivitySource();

                // XXX: we should store the actual feed ID

                $act->source->id = $atom_feed;

                // XXX: we should store the actual feed title

                $act->source->title = $profile->getBestName();

                $act->source->links['alternate'] = $profile->profileurl;
                $act->source->links['self']      = $atom_feed;

                $act->source->icon = $profile->avatarUrl(AVATAR_PROFILE_SIZE);

                $notice = $profile->getCurrentNotice();

                if ($notice instanceof Notice) {
                    $act->source->updated = self::utcDate($notice->created);
                }

                $user = User::getKV('id', $profile->id);

                if ($user instanceof User) {
                    $act->source->links['license'] = common_config('license', 'url');
                }
            }

            if ($this->isLocal()) {
                $act->selfLink = common_local_url('ApiStatusesShow', array('id' => $this->id,
                                                                           'format' => 'atom'));
                $act->editLink = $act->selfLink;
            }

            Event::handle('EndNoticeAsActivity', array($this, $act, $scoped));
        }

        self::cacheSet(Cache::codeKey('notice:as-activity:'.$this->id), $act);

        return $act;
    }

    // This has gotten way too long. Needs to be sliced up into functional bits
    // or ideally exported to a utility class.

    function asAtomEntry($namespace=false,
                         $source=false,
                         $author=true,
                         Profile $scoped=null)
    {
        $act = $this->asActivity($scoped);
        $act->extra[] = $this->noticeInfo($scoped);
        return $act->asString($namespace, $author, $source);
    }

    /**
     * Extra notice info for atom entries
     *
     * Clients use some extra notice info in the atom stream.
     * This gives it to them.
     *
     * @param Profile $scoped   The currently logged in/scoped profile
     *
     * @return array representation of <statusnet:notice_info> element
     */

    function noticeInfo(Profile $scoped=null)
    {
        // local notice ID (useful to clients for ordering)

        $noticeInfoAttr = array('local_id' => $this->id);

        // notice source

        $ns = $this->getSource();

        if ($ns instanceof Notice_source) {
            $noticeInfoAttr['source'] =  $ns->code;
            if (!empty($ns->url)) {
                $noticeInfoAttr['source_link'] = $ns->url;
                if (!empty($ns->name)) {
                    $noticeInfoAttr['source'] = $ns->name;
                }
            }
        }

        // favorite and repeated

        if ($scoped instanceof Profile) {
            $noticeInfoAttr['repeated'] = ($scoped->hasRepeated($this)) ? "true" : "false";
        }

        if (!empty($this->repeat_of)) {
            $noticeInfoAttr['repeat_of'] = $this->repeat_of;
        }

        Event::handle('StatusNetApiNoticeInfo', array($this, &$noticeInfoAttr, $scoped));

        return array('statusnet:notice_info', $noticeInfoAttr, null);
    }

    /**
     * Returns an XML string fragment with a reference to a notice as an
     * Activity Streams noun object with the given element type.
     *
     * Assumes that 'activity' namespace has been previously defined.
     *
     * @param string $element one of 'subject', 'object', 'target'
     * @return string
     */

    function asActivityNoun($element)
    {
        $noun = $this->asActivityObject();
        return $noun->asString('activity:' . $element);
    }

    public function asActivityObject()
    {
        $object = new ActivityObject();

        if (Event::handle('StartActivityObjectFromNotice', array($this, &$object))) {
            $object->type    = $this->object_type ?: ActivityObject::NOTE;
            $object->id      = $this->getUri();
            //FIXME: = $object->title ?: sprintf(... because we might get a title from StartActivityObjectFromNotice
            $object->title   = sprintf('New %1$s by %2$s', ActivityObject::canonicalType($object->type), $this->getProfile()->getNickname());
            $object->content = $this->getRendered();
            $object->link    = $this->getUrl();

            $object->extra[] = array('status_net', array('notice_id' => $this->id));

            Event::handle('EndActivityObjectFromNotice', array($this, &$object));
        }

        if (!$object instanceof ActivityObject) {
            common_log(LOG_ERR, 'Notice asActivityObject created something else for uri=='._ve($this->getUri()).': '._ve($object));
            throw new ServerException('Notice asActivityObject created something else.');
        }

        return $object;
    }

    /**
     * Determine which notice, if any, a new notice is in reply to.
     *
     * For conversation tracking, we try to see where this notice fits
     * in the tree. Beware that this may very well give false positives
     * and add replies to wrong threads (if there have been newer posts
     * by the same user as we're replying to).
     *
     * @param Profile $sender     Author profile
     * @param string  $content    Final notice content
     *
     * @return integer ID of replied-to notice, or null for not a reply.
     */

    static function getInlineReplyTo(Profile $sender, $content)
    {
        // Is there an initial @ or T?
        if (preg_match('/^T ([A-Z0-9]{1,64}) /', $content, $match)
                || preg_match('/^@([a-z0-9]{1,64})\s+/', $content, $match)) {
            $nickname = common_canonical_nickname($match[1]);
        } else {
            return null;
        }

        // Figure out who that is.
        $recipient = common_relative_profile($sender, $nickname, common_sql_now());

        if ($recipient instanceof Profile) {
            // Get their last notice
            $last = $recipient->getCurrentNotice();
            if ($last instanceof Notice) {
                return $last;
            }
            // Maybe in the future we want to handle something else below
            // so don't return getCurrentNotice() immediately.
        }

        return null;
    }

    static function maxContent()
    {
        $contentlimit = common_config('notice', 'contentlimit');
        // null => use global limit (distinct from 0!)
        if (is_null($contentlimit)) {
            $contentlimit = common_config('site', 'textlimit');
        }
        return $contentlimit;
    }

    static function contentTooLong($content)
    {
        $contentlimit = self::maxContent();
        return ($contentlimit > 0 && !empty($content) && (mb_strlen($content) > $contentlimit));
    }

    /**
     * Convenience function for posting a repeat of an existing message.
     *
     * @param Profile $repeater Profile which is doing the repeat
     * @param string $source: posting source key, eg 'web', 'api', etc
     * @return Notice
     *
     * @throws Exception on failure or permission problems
     */
    function repeat(Profile $repeater, $source)
    {
        $author = $this->getProfile();

        // TRANS: Message used to repeat a notice. RT is the abbreviation of 'retweet'.
        // TRANS: %1$s is the repeated user's name, %2$s is the repeated notice.
        $content = sprintf(_('RT @%1$s %2$s'),
                           $author->getNickname(),
                           $this->content);

        $maxlen = self::maxContent();
        if ($maxlen > 0 && mb_strlen($content) > $maxlen) {
            // Web interface and current Twitter API clients will
            // pull the original notice's text, but some older
            // clients and RSS/Atom feeds will see this trimmed text.
            //
            // Unfortunately this is likely to lose tags or URLs
            // at the end of long notices.
            $content = mb_substr($content, 0, $maxlen - 4) . ' ...';
        }


        // Scope is same as this one's
        return self::saveNew($repeater->id,
                             $content,
                             $source,
                             array('repeat_of' => $this->id,
                                   'scope' => $this->scope));
    }

    // These are supposed to be in chron order!

    function repeatStream($limit=100)
    {
        $cache = Cache::instance();

        if (empty($cache)) {
            $ids = $this->_repeatStreamDirect($limit);
        } else {
            $idstr = $cache->get(Cache::key('notice:repeats:'.$this->id));
            if ($idstr !== false) {
            	if (empty($idstr)) {
            		$ids = array();
            	} else {
                	$ids = explode(',', $idstr);
            	}
            } else {
                $ids = $this->_repeatStreamDirect(100);
                $cache->set(Cache::key('notice:repeats:'.$this->id), implode(',', $ids));
            }
            if ($limit < 100) {
                // We do a max of 100, so slice down to limit
                $ids = array_slice($ids, 0, $limit);
            }
        }

        return NoticeStream::getStreamByIds($ids);
    }

    function _repeatStreamDirect($limit)
    {
        $notice = new Notice();

        $notice->selectAdd(); // clears it
        $notice->selectAdd('id');

        $notice->repeat_of = $this->id;

        $notice->orderBy('created, id'); // NB: asc!

        if (!is_null($limit)) {
            $notice->limit(0, $limit);
        }

        return $notice->fetchAll('id');
    }

    static function locationOptions($lat, $lon, $location_id, $location_ns, $profile = null)
    {
        $options = array();

        if (!empty($location_id) && !empty($location_ns)) {
            $options['location_id'] = $location_id;
            $options['location_ns'] = $location_ns;

            $location = Location::fromId($location_id, $location_ns);

            if ($location instanceof Location) {
                $options['lat'] = $location->lat;
                $options['lon'] = $location->lon;
            }

        } else if (!empty($lat) && !empty($lon)) {
            $options['lat'] = $lat;
            $options['lon'] = $lon;

            $location = Location::fromLatLon($lat, $lon);

            if ($location instanceof Location) {
                $options['location_id'] = $location->location_id;
                $options['location_ns'] = $location->location_ns;
            }
        } else if (!empty($profile)) {
            if (isset($profile->lat) && isset($profile->lon)) {
                $options['lat'] = $profile->lat;
                $options['lon'] = $profile->lon;
            }

            if (isset($profile->location_id) && isset($profile->location_ns)) {
                $options['location_id'] = $profile->location_id;
                $options['location_ns'] = $profile->location_ns;
            }
        }

        return $options;
    }

    function clearAttentions()
    {
        $att = new Attention();
        $att->notice_id = $this->getID();

        if ($att->find()) {
            while ($att->fetch()) {
                // Can't do delete() on the object directly since it won't remove all of it
                $other = clone($att);
                $other->delete();
            }
        }
    }

    function clearReplies()
    {
        $replyNotice = new Notice();
        $replyNotice->reply_to = $this->id;

        //Null any notices that are replies to this notice

        if ($replyNotice->find()) {
            while ($replyNotice->fetch()) {
                $orig = clone($replyNotice);
                $replyNotice->reply_to = null;
                $replyNotice->update($orig);
            }
        }

        // Reply records

        $reply = new Reply();
        $reply->notice_id = $this->id;

        if ($reply->find()) {
            while($reply->fetch()) {
                self::blow('reply:stream:%d', $reply->profile_id);
                $reply->delete();
            }
        }

        $reply->free();
    }

    function clearLocation()
    {
        $loc = new Notice_location();
        $loc->notice_id = $this->id;

        if ($loc->find()) {
            $loc->delete();
        }
    }

    function clearFiles()
    {
        $f2p = new File_to_post();

        $f2p->post_id = $this->id;

        if ($f2p->find()) {
            while ($f2p->fetch()) {
                $f2p->delete();
            }
        }
        // FIXME: decide whether to delete File objects
        // ...and related (actual) files
    }

    function clearRepeats()
    {
        $repeatNotice = new Notice();
        $repeatNotice->repeat_of = $this->id;

        //Null any notices that are repeats of this notice

        if ($repeatNotice->find()) {
            while ($repeatNotice->fetch()) {
                $orig = clone($repeatNotice);
                $repeatNotice->repeat_of = null;
                $repeatNotice->update($orig);
            }
        }
    }

    function clearTags()
    {
        $tag = new Notice_tag();
        $tag->notice_id = $this->id;

        if ($tag->find()) {
            while ($tag->fetch()) {
                self::blow('profile:notice_ids_tagged:%d:%s', $this->profile_id, Cache::keyize($tag->tag));
                self::blow('profile:notice_ids_tagged:%d:%s;last', $this->profile_id, Cache::keyize($tag->tag));
                self::blow('notice_tag:notice_ids:%s', Cache::keyize($tag->tag));
                self::blow('notice_tag:notice_ids:%s;last', Cache::keyize($tag->tag));
                $tag->delete();
            }
        }

        $tag->free();
    }

    function clearGroupInboxes()
    {
        $gi = new Group_inbox();

        $gi->notice_id = $this->id;

        if ($gi->find()) {
            while ($gi->fetch()) {
                self::blow('user_group:notice_ids:%d', $gi->group_id);
                $gi->delete();
            }
        }

        $gi->free();
    }

    function distribute()
    {
        // We always insert for the author so they don't
        // have to wait
        Event::handle('StartNoticeDistribute', array($this));

        // If there's a failure, we want to _force_
        // distribution at this point.
        try {
            $json = json_encode((object)array('id' => $this->getID(),
                                              'type' => 'Notice',
                                              ));
            $qm = QueueManager::get();
            $qm->enqueue($json, 'distrib');
        } catch (Exception $e) {
            // If the exception isn't transient, this
            // may throw more exceptions as DQH does
            // its own enqueueing. So, we ignore them!
            try {
                $handler = new DistribQueueHandler();
                $handler->handle($this);
            } catch (Exception $e) {
                common_log(LOG_ERR, "emergency redistribution resulted in " . $e->getMessage());
            }
            // Re-throw so somebody smarter can handle it.
            throw $e;
        }
    }

    function insert()
    {
        $result = parent::insert();

        if ($result === false) {
            common_log_db_error($this, 'INSERT', __FILE__);
            // TRANS: Server exception thrown when a stored object entry cannot be saved.
            throw new ServerException('Could not save Notice');
        }

        // Profile::hasRepeated() abuses pkeyGet(), so we
        // have to clear manually
        if (!empty($this->repeat_of)) {
            $c = self::memcache();
            if (!empty($c)) {
                $ck = self::multicacheKey('Notice',
                                          array('profile_id' => $this->profile_id,
                                                'repeat_of' => $this->repeat_of));
                $c->delete($ck);
            }
        }

        // Update possibly ID-dependent columns: URI, conversation
        // (now that INSERT has added the notice's local id)
        $orig = clone($this);
        $changed = false;

        // We can only get here if it's a local notice, since remote notices
        // should've bailed out earlier due to lacking a URI.
        if (empty($this->uri)) {
            $this->uri = sprintf('%s%s=%d:%s=%s',
                                TagURI::mint(),
                                'noticeId', $this->id,
                                'objectType', $this->getObjectType(true));
            $changed = true;
        }

        if ($changed && $this->update($orig) === false) {
            common_log_db_error($notice, 'UPDATE', __FILE__);
            // TRANS: Server exception thrown when a notice cannot be updated.
            throw new ServerException(_('Problem saving notice.'));
        }

        $this->blowOnInsert();

        return $result;
    }

    /**
     * Get the source of the notice
     *
     * @return Notice_source $ns A notice source object. 'code' is the only attribute
     *                           guaranteed to be populated.
     */
    function getSource()
    {
        if (empty($this->source)) {
            return false;
        }

        $ns = new Notice_source();
        switch ($this->source) {
        case 'web':
        case 'xmpp':
        case 'mail':
        case 'omb':
        case 'system':
        case 'api':
            $ns->code = $this->source;
            break;
        default:
            $ns = Notice_source::getKV($this->source);
            if (!$ns) {
                $ns = new Notice_source();
                $ns->code = $this->source;
                $app = Oauth_application::getKV('name', $this->source);
                if ($app) {
                    $ns->name = $app->name;
                    $ns->url  = $app->source_url;
                }
            }
            break;
        }

        return $ns;
    }

    /**
     * Determine whether the notice was locally created
     *
     * @return boolean locality
     */

    public function isLocal()
    {
        $is_local = intval($this->is_local);
        return ($is_local === self::LOCAL_PUBLIC || $is_local === self::LOCAL_NONPUBLIC);
    }

    public function getScope()
    {
        return intval($this->scope);
    }

    public function isRepeat()
    {
        return !empty($this->repeat_of);
    }

    public function isRepeated()
    {
        $n = new Notice();
        $n->repeat_of = $this->getID();
        return $n->find() && $n->N > 0;
    }

    /**
     * Get the list of hash tags saved with this notice.
     *
     * @return array of strings
     */
    public function getTags()
    {
        $tags = array();

        $keypart = sprintf('notice:tags:%d', $this->id);

        $tagstr = self::cacheGet($keypart);

        if ($tagstr !== false) {
            $tags = explode(',', $tagstr);
        } else {
            $tag = new Notice_tag();
            $tag->notice_id = $this->id;
            if ($tag->find()) {
                while ($tag->fetch()) {
                    $tags[] = $tag->tag;
                }
            }
            self::cacheSet($keypart, implode(',', $tags));
        }

        return $tags;
    }

    static private function utcDate($dt)
    {
        $dateStr = date('d F Y H:i:s', strtotime($dt));
        $d = new DateTime($dateStr, new DateTimeZone('UTC'));
        return $d->format(DATE_W3C);
    }

    /**
     * Look up the creation timestamp for a given notice ID, even
     * if it's been deleted.
     *
     * @param int $id
     * @return mixed string recorded creation timestamp, or false if can't be found
     */
    public static function getAsTimestamp($id)
    {
        if (empty($id)) {
            throw new EmptyPkeyValueException('Notice', 'id');
        }

        $timestamp = null;
        if (Event::handle('GetNoticeSqlTimestamp', array($id, &$timestamp))) {
            // getByID throws exception if $id isn't found
            $notice = Notice::getByID($id);
            $timestamp = $notice->created;
        }

        if (empty($timestamp)) {
            throw new ServerException('No timestamp found for Notice with id=='._ve($id));
        }
        return $timestamp;
    }

    /**
     * Build an SQL 'where' fragment for timestamp-based sorting from a since_id
     * parameter, matching notices posted after the given one (exclusive).
     *
     * If the referenced notice can't be found, will return false.
     *
     * @param int $id
     * @param string $idField
     * @param string $createdField
     * @return mixed string or false if no match
     */
    public static function whereSinceId($id, $idField='id', $createdField='created')
    {
        try {
            $since = Notice::getAsTimestamp($id);
        } catch (Exception $e) {
            return false;
        }
        return sprintf("($createdField = '%s' and $idField > %d) or ($createdField > '%s')", $since, $id, $since);
    }

    /**
     * Build an SQL 'where' fragment for timestamp-based sorting from a since_id
     * parameter, matching notices posted after the given one (exclusive), and
     * if necessary add it to the data object's query.
     *
     * @param DB_DataObject $obj
     * @param int $id
     * @param string $idField
     * @param string $createdField
     * @return mixed string or false if no match
     */
    public static function addWhereSinceId(DB_DataObject $obj, $id, $idField='id', $createdField='created')
    {
        $since = self::whereSinceId($id, $idField, $createdField);
        if ($since) {
            $obj->whereAdd($since);
        }
    }

    /**
     * Build an SQL 'where' fragment for timestamp-based sorting from a max_id
     * parameter, matching notices posted before the given one (inclusive).
     *
     * If the referenced notice can't be found, will return false.
     *
     * @param int $id
     * @param string $idField
     * @param string $createdField
     * @return mixed string or false if no match
     */
    public static function whereMaxId($id, $idField='id', $createdField='created')
    {
        try {
            $max = Notice::getAsTimestamp($id);
        } catch (Exception $e) {
            return false;
        }
        return sprintf("($createdField < '%s') or ($createdField = '%s' and $idField <= %d)", $max, $max, $id);
    }

    /**
     * Build an SQL 'where' fragment for timestamp-based sorting from a max_id
     * parameter, matching notices posted before the given one (inclusive), and
     * if necessary add it to the data object's query.
     *
     * @param DB_DataObject $obj
     * @param int $id
     * @param string $idField
     * @param string $createdField
     * @return mixed string or false if no match
     */
    public static function addWhereMaxId(DB_DataObject $obj, $id, $idField='id', $createdField='created')
    {
        $max = self::whereMaxId($id, $idField, $createdField);
        if ($max) {
            $obj->whereAdd($max);
        }
    }

    public function isPublic()
    {
        $is_local = intval($this->is_local);
        return !($is_local === Notice::LOCAL_NONPUBLIC || $is_local === Notice::GATEWAY);
    }

    /**
     * Check that the given profile is allowed to read, respond to, or otherwise
     * act on this notice.
     *
     * The $scope member is a bitmask of scopes, representing a logical AND of the
     * scope requirement. So, 0x03 (Notice::ADDRESSEE_SCOPE | Notice::SITE_SCOPE) means
     * "only visible to people who are mentioned in the notice AND are users on this site."
     * Users on the site who are not mentioned in the notice will not be able to see the
     * notice.
     *
     * @param Profile $profile The profile to check; pass null to check for public/unauthenticated users.
     *
     * @return boolean whether the profile is in the notice's scope
     */
    function inScope($profile)
    {
        if (is_null($profile)) {
            $keypart = sprintf('notice:in-scope-for:%d:null', $this->id);
        } else {
            $keypart = sprintf('notice:in-scope-for:%d:%d', $this->id, $profile->id);
        }

        $result = self::cacheGet($keypart);

        if ($result === false) {
            $bResult = false;
            if (Event::handle('StartNoticeInScope', array($this, $profile, &$bResult))) {
                $bResult = $this->_inScope($profile);
                Event::handle('EndNoticeInScope', array($this, $profile, &$bResult));
            }
            $result = ($bResult) ? 1 : 0;
            self::cacheSet($keypart, $result, 0, 300);
        }

        return ($result == 1) ? true : false;
    }

    protected function _inScope($profile)
    {
        $scope = is_null($this->scope) ? self::defaultScope() : $this->getScope();

        if ($scope === 0 && !$this->getProfile()->isPrivateStream()) { // Not scoping, so it is public.
            return !$this->isHiddenSpam($profile);
        }

        // If there's scope, anon cannot be in scope
        if (empty($profile)) {
            return false;
        }

        // Author is always in scope
        if ($this->profile_id == $profile->id) {
            return true;
        }

        // Only for users on this site
        if (($scope & Notice::SITE_SCOPE) && !$profile->isLocal()) {
            return false;
        }

        // Only for users mentioned in the notice
        if ($scope & Notice::ADDRESSEE_SCOPE) {

            $reply = Reply::pkeyGet(array('notice_id' => $this->id,
                                         'profile_id' => $profile->id));

            if (!$reply instanceof Reply) {
                return false;
            }
        }

        // Only for members of the given group
        if ($scope & Notice::GROUP_SCOPE) {

            // XXX: just query for the single membership

            $groups = $this->getGroups();

            $foundOne = false;

            foreach ($groups as $group) {
                if ($profile->isMember($group)) {
                    $foundOne = true;
                    break;
                }
            }

            if (!$foundOne) {
                return false;
            }
        }

        if ($scope & Notice::FOLLOWER_SCOPE || $this->getProfile()->isPrivateStream()) {

            if (!Subscription::exists($profile, $this->getProfile())) {
                return false;
            }
        }

        return !$this->isHiddenSpam($profile);
    }

    function isHiddenSpam($profile) {

        // Hide posts by silenced users from everyone but moderators.

        if (common_config('notice', 'hidespam')) {

            try {
                $author = $this->getProfile();
            } catch(Exception $e) {
                // If we can't get an author, keep it hidden.
                // XXX: technically not spam, but, whatever.
                return true;
            }

            if ($author->hasRole(Profile_role::SILENCED)) {
                if (!$profile instanceof Profile || (($profile->id !== $author->id) && (!$profile->hasRight(Right::REVIEWSPAM)))) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasParent()
    {
        try {
            $this->getParent();
        } catch (NoParentNoticeException $e) {
            return false;
        }
        return true;
    }

    public function getParent()
    {
        $reply_to_id = null;

        if (empty($this->reply_to)) {
            throw new NoParentNoticeException($this);
        }

        // The reply_to ID in the table Notice could exist with a number
        // however, the replied to notice might not exist in the database.
        // Thus we need to catch the exception and throw the NoParentNoticeException else
        // the timeline will not display correctly.
        try {
            $reply_to_id = self::getByID($this->reply_to);
        } catch(Exception $e){
            throw new NoParentNoticeException($this);
        }

        return $reply_to_id;
    }

    /**
     * Magic function called at serialize() time.
     *
     * We use this to drop a couple process-specific references
     * from DB_DataObject which can cause trouble in future
     * processes.
     *
     * @return array of variable names to include in serialization.
     */

    function __sleep()
    {
        $vars = parent::__sleep();
        $skip = array('_profile', '_groups', '_attachments', '_faves', '_replies', '_repeats');
        return array_diff($vars, $skip);
    }

    static function defaultScope()
    {
    	$scope = common_config('notice', 'defaultscope');
    	if (is_null($scope)) {
    		if (common_config('site', 'private')) {
    			$scope = 1;
    		} else {
    			$scope = 0;
    		}
    	}
    	return $scope;
    }

	static function fillProfiles($notices)
	{
		$map = self::getProfiles($notices);
		foreach ($notices as $entry=>$notice) {
            try {
    			if (array_key_exists($notice->profile_id, $map)) {
	    			$notice->_setProfile($map[$notice->profile_id]);
		    	}
            } catch (NoProfileException $e) {
                common_log(LOG_WARNING, "Failed to fill profile in Notice with non-existing entry for profile_id: {$e->profile_id}");
                unset($notices[$entry]);
            }
		}

		return array_values($map);
	}

	static function getProfiles(&$notices)
	{
		$ids = array();
		foreach ($notices as $notice) {
			$ids[] = $notice->profile_id;
		}
		$ids = array_unique($ids);
		return Profile::pivotGet('id', $ids);
	}

	static function fillGroups(&$notices)
	{
        $ids = self::_idsOf($notices);
        $gis = Group_inbox::listGet('notice_id', $ids);
        $gids = array();

		foreach ($gis as $id => $gi) {
		    foreach ($gi as $g)
		    {
		        $gids[] = $g->group_id;
		    }
		}

		$gids = array_unique($gids);
		$group = User_group::pivotGet('id', $gids);
		foreach ($notices as $notice)
		{
			$grps = array();
			$gi = $gis[$notice->id];
			foreach ($gi as $g) {
			    $grps[] = $group[$g->group_id];
			}
		    $notice->_setGroups($grps);
		}
	}

    static function _idsOf(array &$notices)
    {
		$ids = array();
		foreach ($notices as $notice) {
			$ids[$notice->id] = true;
		}
		return array_keys($ids);
    }

    static function fillAttachments(&$notices)
    {
        $ids = self::_idsOf($notices);
        $f2pMap = File_to_post::listGet('post_id', $ids);
		$fileIds = array();
		foreach ($f2pMap as $noticeId => $f2ps) {
            foreach ($f2ps as $f2p) {
                $fileIds[] = $f2p->file_id;
            }
        }

        $fileIds = array_unique($fileIds);
		$fileMap = File::pivotGet('id', $fileIds);
		foreach ($notices as $notice)
		{
			$files = array();
			$f2ps = $f2pMap[$notice->id];
			foreach ($f2ps as $f2p) {
                if (!isset($fileMap[$f2p->file_id])) {
                    // We have probably deleted value from fileMap since
                    // it as a NULL entry (see the following elseif).
                    continue;
                } elseif (is_null($fileMap[$f2p->file_id])) {
                    // If the file id lookup returned a NULL value, it doesn't
                    // exist in our file table! So this is a remnant file_to_post
                    // entry that is no longer valid and should be removed.
                    common_debug('ATTACHMENT deleting f2p for post_id='.$f2p->post_id.' file_id='.$f2p->file_id);
                    $f2p->delete();
                    unset($fileMap[$f2p->file_id]);
                    continue;
                }
			    $files[] = $fileMap[$f2p->file_id];
			}
		    $notice->_setAttachments($files);
		}
    }

    static function fillReplies(&$notices)
    {
        $ids = self::_idsOf($notices);
        $replyMap = Reply::listGet('notice_id', $ids);
        foreach ($notices as $notice) {
            $replies = $replyMap[$notice->id];
            $ids = array();
            foreach ($replies as $reply) {
                $ids[] = $reply->profile_id;
            }
            $notice->_setReplies($ids);
        }
    }

    static public function beforeSchemaUpdate()
    {
        $table = strtolower(get_called_class());
        $schema = Schema::get();
        $schemadef = $schema->getTableDef($table);

        // 2015-09-04 We move Notice location data to Notice_location
        // First we see if we have to do this at all
        if (isset($schemadef['fields']['lat'])
                && isset($schemadef['fields']['lon'])
                && isset($schemadef['fields']['location_id'])
                && isset($schemadef['fields']['location_ns'])) {
            // Then we make sure the Notice_location table is created!
            $schema->ensureTable('notice_location', Notice_location::schemaDef());

            // Then we continue on our road to migration!
            echo "\nFound old $table table, moving location data to 'notice_location' table... (this will probably take a LONG time, but can be aborted and continued)";

            $notice = new Notice();
            $notice->query(sprintf('SELECT id, lat, lon, location_id, location_ns FROM %1$s ' .
                                 'WHERE lat IS NOT NULL ' .
                                    'OR lon IS NOT NULL ' .
                                    'OR location_id IS NOT NULL ' .
                                    'OR location_ns IS NOT NULL',
                                 $schema->quoteIdentifier($table)));
            print "\nFound {$notice->N} notices with location data, inserting";
            while ($notice->fetch()) {
                $notloc = Notice_location::getKV('notice_id', $notice->id);
                if ($notloc instanceof Notice_location) {
                    print "-";
                    continue;
                }
                $notloc = new Notice_location();
                $notloc->notice_id = $notice->id;
                $notloc->lat= $notice->lat;
                $notloc->lon= $notice->lon;
                $notloc->location_id= $notice->location_id;
                $notloc->location_ns= $notice->location_ns;
                $notloc->insert();
                print ".";
            }
            print "\n";
        }

        /**
         *  Make sure constraints are met before upgrading, if foreign keys
         *  are not already in use.
         *  2016-03-31
         */
        if (!isset($schemadef['foreign keys'])) {
            $newschemadef = self::schemaDef();
            printfnq("\nConstraint checking Notice table...\n");
            /**
             *  Improve typing and make sure no NULL values in any id-related columns are 0
             *  2016-03-31
             */
            foreach (['reply_to', 'repeat_of'] as $field) {
                $notice = new Notice(); // reset the object
                $notice->query(sprintf('UPDATE %1$s SET %2$s=NULL WHERE %2$s=0', $notice->escapedTableName(), $field));
                // Now we're sure that no Notice entries have repeat_of=0, only an id > 0 or NULL
                unset($notice);
            }

            /**
             *  This Will find foreign keys which do not fulfill the constraints and fix
             *  where appropriate, such as delete when "repeat_of" ID not found in notice.id
             *  or set to NULL for "reply_to" in the same case.
             *  2016-03-31
             *
             *  XXX: How does this work if we would use multicolumn foreign keys?
             */
            foreach (['reply_to' => 'reset', 'repeat_of' => 'delete', 'profile_id' => 'delete'] as $field=>$action) {
                $notice = new Notice();

                $fkeyname = $notice->tableName().'_'.$field.'_fkey';
                assert(isset($newschemadef['foreign keys'][$fkeyname]));
                assert($newschemadef['foreign keys'][$fkeyname]);

                $foreign_key = $newschemadef['foreign keys'][$fkeyname];
                $fkeytable = $foreign_key[0];
                assert(isset($foreign_key[1][$field]));
                $fkeycol   = $foreign_key[1][$field];

                printfnq("* {$fkeyname} ({$field} => {$fkeytable}.{$fkeycol})\n");

                // NOTE: Above we set all repeat_of to NULL if they were 0, so this really gets them all.
                $notice->whereAdd(sprintf('%1$s NOT IN (SELECT %2$s FROM %3$s)', $field, $fkeycol, $fkeytable));
                if ($notice->find()) {
                    printfnq("\tFound {$notice->N} notices with {$field} NOT IN notice.id, {$action}ing...");
                    switch ($action) {
                    case 'delete':  // since it's a directly dependant notice for an unknown ID we don't want it in our DB
                        while ($notice->fetch()) {
                            $notice->delete();
                        }
                        break;
                    case 'reset':   // just set it to NULL to be compatible with our constraints, if it was related to an unknown ID
                        $ids = [];
                        foreach ($notice->fetchAll('id') as $id) {
                            settype($id, 'int');
                            $ids[] = $id;
                        }
                        unset($notice);
                        $notice = new Notice();
                        $notice->query(sprintf('UPDATE %1$s SET %2$s=NULL WHERE id IN (%3$s)',
                                            $notice->escapedTableName(),
                                            $field,
                                            implode(',', $ids)));
                        break;
                    default:
                        throw new ServerException('The programmer sucks, invalid action name when fixing table.');
                    }
                    printfnq("DONE.\n");
                }
                unset($notice);
            }
        }
    }
}

// END OF FILE
// ============================================================================
?>