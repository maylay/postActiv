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
 * Class for building an in-memory Atom feed for a particular list's
 * timeline.
 *
 * @category  Feed
 * @package   StatusNet
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
 * Class for list notice feeds.  May contain a reference to the list.
 */
class AtomListNoticeFeed extends AtomNoticeFeed
{
    private $list;
    private $tagger;

    /**
     * Constructor
     *
     * @param List    $list    the list for the feed
     * @param User    $cur     the current authenticated user, if any
     * @param boolean $indent  flag to turn indenting on or off
     *
     * @return void
     */
    function __construct($list, $cur = null, $indent = true) {
        parent::__construct($cur, $indent);
        $this->list = $list;
        $this->tagger = Profile::getKV('id', $list->tagger);

        // TRANS: Title in atom list notice feed. %1$s is a list name, %2$s is a tagger's nickname.
        $title = sprintf(_('Timeline for people in list %1$s by %2$s'), $list->tag, $this->tagger->nickname);
        $this->setTitle($title);

        $sitename   = common_config('site', 'name');
        $subtitle   = sprintf(
            // TRANS: Message is used as a subtitle in atom list notice feed.
            // TRANS: %1$s is a tagger's nickname, %2$s is a list name, %3$s is a site name.
            _('Updates from %1$s\'s list %2$s on %3$s!'),
            $this->tagger->nickname,
            $list->tag,
            $sitename
        );
        $this->setSubtitle($subtitle);

        $avatar = $this->tagger->avatarUrl(AVATAR_PROFILE_SIZE);
        $this->setLogo($avatar);

        $this->setUpdated('now');

        $self = common_local_url('ApiTimelineList',
                                 array('user' => $this->tagger->nickname,
                                       'id' => $list->tag,
                                       'format' => 'atom'));
        $this->setId($self);
        $this->setSelfLink($self);

        $ao = ActivityObject::fromPeopletag($this->list);

        $this->addAuthorRaw($ao->asString('author'));

        $this->addLink($this->list->getUri());
    }

    function getList()
    {
        return $this->list;
    }
}
?>