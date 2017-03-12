<?php
/* ============================================================================
 * Title: APITimelineFriends
 * Show the friends timeline
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
 * Show the friends timeline
 *
 * Returns the 20 most recent statuses posted by the authenticating
 * user and that user's friends. This is the equivalent of "You and
 * friends" page in the web interface.
 *
 * URL patterns:
 * o /api/statuses/friends_timeline.:format
 * o /api/statuses/friends_timeline/:id.:format
 *
 * Formats:
 * o xml
 * o json
 * o rss
 * o atom
 *
 * ID:
 * o username
 * o user id
 *
 * HTTP Method:
 * o GET
 *
 * Requires Authentication:
 * Sometimes (see: @ref authentication)
 *
 * o user_id     - (Optional) Specifies a user by ID
 * o screen_name - (Optional) Specifies a user by screename (nickname)
 * o since_id    - (Optional) Returns only statuses with an ID greater
 *   than (that is, more recent than) the specified ID.
 * o max_id      - (Optional) Returns only statuses with an ID less than
 *   (that is, older than) or equal to the specified ID.
 * o count       - (Optional) Specifies the number of statuses to retrieve.
 * o page        - (Optional) Specifies the page of results to retrieve.
 *
 * Usage notes:
 * o The URL pattern is relative to the @ref apiroot.
 * o The XML response uses <a href="http://georss.org/Main_Page">GeoRSS</a>
 *   to encode the latitude and longitude (see example response below <georss:point>).
 *
 * Example usage:
 *     curl http://myinstance.com/api/statuses/friends_timeline/maiyannah.xml?count=1&page=2
 *
 * Example response:
 *     <?xml version="1.0"?>
 *     <statuses type="array">
 *       <status>
 *         <text>back from the !yul !drupal meet with Evolving Web folk, @anarcat, @webchick and others, and an interesting refresher on SQL indexing</text>
 *         <truncated>false</truncated>
 *         <created_at>Wed Mar 31 01:33:02 +0000 2010</created_at>
 *         <in_reply_to_status_id/>
 *         <source>&lt;a href="http://somesourcecode.net/microblog/"&gt;mbpidgin&lt;/a&gt;</source>
 *         <id>26674201</id>
 *         <in_reply_to_user_id/>
 *         <in_reply_to_screen_name/>
 *         <geo/>
 *         <favorited>false</favorited>
 *         <user>
 *           <id>246</id>
 *           <name>Mark</name>
 *           <screen_name>lambic</screen_name>
 *           <location>Montreal, Canada</location>
 *           <description>Geek</description>
 *           <profile_image_url>http://avatar.identi.ca/246-48-20080702141545.png</profile_image_url>
 *           <url>http://lambic.co.uk</url>
 *           <protected>false</protected>
 *           <followers_count>73</followers_count>
 *           <profile_background_color>#F0F2F5</profile_background_color>
 *           <profile_text_color/>
 *           <profile_link_color>#002E6E</profile_link_color>
 *           <profile_sidebar_fill_color>#CEE1E9</profile_sidebar_fill_color>
 *           <profile_sidebar_border_color/>
 *           <friends_count>58</friends_count>
 *           <created_at>Wed Jul 02 14:12:15 +0000 2008</created_at>
 *           <favourites_count>2</favourites_count>
 *           <utc_offset>-14400</utc_offset>
 *           <time_zone>US/Eastern</time_zone>
 *           <profile_background_image_url/>
 *           <profile_background_tile>false</profile_background_tile>
 *           <statuses_count>933</statuses_count>
 *           <following>false</following>
 *           <notifications>false</notifications>
 *         </user>
 *       </status>
 *     </statuses>
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Evan Prodromou
 * o Robin Millette <robin@millette.info>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Bob Mottram <bob@robotics.co.uk>
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
 * Returns the most recent notices (default 20) posted by the target user.
 * This is the equivalent of 'You and friends' page accessed via Web.
 */
class ApiTimelineFriendsAction extends ApiBareAuthAction
{
    var $notices  = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     *
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);
        $this->target = $this->getTargetProfile($this->arg('id'));

        if (!($this->target instanceof Profile)) {
            // TRANS: Client error displayed when requesting dents of a user and friends for a user that does not exist.
            $this->clientError(_('No such user.'), 404);
        }

        $this->notices = $this->getNotices();

        return true;
    }

    /**
     * Handle the request
     *
     * Just show the notices
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();
        $this->showTimeline();
    }

    /**
     * Show the timeline of notices
     *
     * @return void
     */
    function showTimeline()
    {
        $sitename   = common_config('site', 'name');
        // TRANS: Title of API timeline for a user and friends.
        // TRANS: %s is a username.
        $title      = sprintf(_("%s and friends"), $this->target->nickname);
        $taguribase = TagURI::base();
        $id         = "tag:$taguribase:FriendsTimeline:" . $this->target->id;

        $subtitle = sprintf(
            // TRANS: Message is used as a subtitle. %1$s is a user nickname, %2$s is a site name.
            _('Updates from %1$s and friends on %2$s!'),
            $this->target->nickname,
            $sitename
        );

        $logo = $this->target->avatarUrl(AVATAR_PROFILE_SIZE);
        $link = common_local_url('all',
                    array('nickname' => $this->target->nickname));
        $self = $this->getSelfUri();

        switch($this->format) {
        case 'xml':
            $this->showXmlTimeline($this->notices);
            break;
        case 'rss':

            $this->showRssTimeline(
                $this->notices,
                $title,
                $link,
                $subtitle,
                null,
                $logo,
                $self
            );
            break;
        case 'atom':
            header('Content-Type: application/atom+xml; charset=utf-8');

            $atom = new AtomNoticeFeed($this->auth_user);

            $atom->setId($id);
            $atom->setTitle($title);
            $atom->setSubtitle($subtitle);
            $atom->setLogo($logo);
            $atom->setUpdated('now');
            $atom->addLink($link);
            $atom->setSelfLink($self);

            $atom->addEntryFromNotices($this->notices);

            $this->raw($atom->getString());

            break;
        case 'json':
            $this->showJsonTimeline($this->notices);
            break;
        case 'as':
            header('Content-Type: ' . ActivityStreamJSONDocument::CONTENT_TYPE);
            $doc = new ActivityStreamJSONDocument($this->auth_user, $title);
            $doc->addLink($link, 'alternate', 'text/html');
            $doc->addItemsFromNotices($this->notices);
            $this->raw($doc->asString());
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }

    /**
     * Get notices
     *
     * @return array notices
     */
    function getNotices()
    {
        $notices = array();

        $stream = new InboxNoticeStream($this->target, $this->scoped);

        $notice = $stream->getNotices(($this->page-1) * $this->count,
                                      $this->count,
                                      $this->since_id,
                                      $this->max_id);

        while ($notice->fetch()) {
            $notices[] = clone($notice);
        }

        return $notices;
    }

    /**
     * Is this action read only?
     *
     * @param array $args other arguments
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    /**
     * When was this feed last modified?
     *
     * @return string datestamp of the latest notice in the stream
     */
    function lastModified()
    {
        if (!empty($this->notices) && (count($this->notices) > 0)) {
            return strtotime($this->notices[0]->created);
        }

        return null;
    }

    /**
     * An entity tag for this stream
     *
     * Returns an Etag based on the action name, language, user ID, and
     * timestamps of the first and last notice in the timeline
     *
     * @return string etag
     */
    function etag()
    {
        if (!empty($this->notices) && (count($this->notices) > 0)) {
            $last = count($this->notices) - 1;

            return '"' . implode(
                                 ':',
                                 array($this->arg('action'),
                                       common_user_cache_hash($this->auth_user),
                                       common_language(),
                                       $this->target->id,
                                       strtotime($this->notices[0]->created),
                                       strtotime($this->notices[$last]->created))
                                 )
              . '"';
        }

        return null;
    }
}

// END OF FILE
// ============================================================================
?>