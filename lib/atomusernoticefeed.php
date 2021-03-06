<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Class for building an in-memory Atom feed for a particular user's
 * timeline.
 *
 * @category  Feed
 * @package   postActiv
 * @author    Zach Copley
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Class for user notice feeds.  May contain a reference to the user.
 */
class AtomUserNoticeFeed extends AtomNoticeFeed
{
    protected $user;

    /**
     * Constructor
     *
     * @param User    $user    the user for the feed
     * @param User    $cur     the current authenticated user, if any
     * @param boolean $indent  flag to turn indenting on or off
     *
     * @return void
     */
    function __construct($user, $cur = null, $indent = true) {
        parent::__construct($cur, $indent);
        $this->user = $user;
        if (!empty($user)) {

            $profile = $user->getProfile();

            $ao = $profile->asActivityObject();
            
            array_push($ao->extra, $profile->profileInfo($this->scoped));

            $this->addAuthorRaw($ao->asString('author'));
        }

        // TRANS: Title in atom user notice feed. %s is a user name.
        $title      = sprintf(_("%s timeline"), $user->nickname);
        $this->setTitle($title);

        $sitename   = common_config('site', 'name');
        $subtitle   = sprintf(
            // TRANS: Message is used as a subtitle in atom user notice feed.
            // TRANS: %1$s is a user name, %2$s is a site name.
            _('Updates from %1$s on %2$s!'),
            $user->nickname, $sitename
        );
        $this->setSubtitle($subtitle);

        $this->setLogo($profile->avatarUrl(AVATAR_PROFILE_SIZE));

        $this->setUpdated('now');

        $this->addLink(
            common_local_url(
                'showstream',
                array('nickname' => $user->nickname)
            )
        );

        $self = common_local_url('ApiTimelineUser',
                                 array('id' => $user->id,
                                       'format' => 'atom'));
        $this->setId($self);
        $this->setSelfLink($self);

        $this->addLink(
            common_local_url('sup', null, null, $user->id),
            array(
                'rel' => 'http://api.friendfeed.com/2008/03#sup',
                'type' => 'application/json'
            )
        );
    }

    function getUser()
    {
        return $this->user;
    }

    function showSource()
    {
        return false;
    }

    function showAuthor()
    {
        return false;
    }
}
?>