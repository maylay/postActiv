<?php
/* ============================================================================
 * Title: All
 * Retrieve all the different friends timeline formats (RSS, JSON, etc)
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
 * Retrieve all the different friends timeline formats (RSS, JSON, etc)
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Sarven Capadisli
 * o Adrian Lang <mail@adrianlang.de>
 * o Zach Copley
 * o Meitar Moscovitz <meitarm@gmail.com>
 * o Robin Millette <robin@millette.info>
 * o Jeffery To <jeffery.to@gmail.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Brenda Wallace <shiny@cpan.org>
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
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


class AllAction extends ShowstreamAction
{
    public function getStream()
    {
        if ($this->scoped instanceof Profile && $this->scoped->isLocal() && $this->scoped->getUser()->streamModeOnly()) {
            $stream = new InboxNoticeStream($this->target, $this->scoped);
        } else {
            $stream = new ThreadingInboxNoticeStream($this->target, $this->scoped);
        }

        return $stream;
    }

    function title()
    {
        if (!empty($this->scoped) && $this->scoped->sameAs($this->target)) {
            // TRANS: Title of a user's own start page.
            return _('Home timeline');
        } else {
            // TRANS: Title of another user's start page.
            // TRANS: %s is the other user's name.
            return sprintf(_("%s's home timeline"), $this->target->getBestName());
        }
    }

    function getFeeds()
    {
        return array(
            new Feed(Feed::JSON,
                common_local_url(
                    'ApiTimelineFriends', array(
                        'format' => 'as',
                        'id' => $this->target->getNickname()
                    )
                ),
                // TRANS: %s is user nickname.
                sprintf(_('Feed for friends of %s (Activity Streams JSON)'), $this->target->getNickname())),
            new Feed(Feed::RSS1,
                common_local_url(
                    'allrss', array(
                        'nickname' =>
                        $this->target->getNickname())
                ),
                // TRANS: %s is user nickname.
                sprintf(_('Feed for friends of %s (RSS 1.0)'), $this->target->getNickname())),
            new Feed(Feed::RSS2,
                common_local_url(
                    'ApiTimelineFriends', array(
                        'format' => 'rss',
                        'id' => $this->target->getNickname()
                    )
                ),
                // TRANS: %s is user nickname.
                sprintf(_('Feed for friends of %s (RSS 2.0)'), $this->target->getNickname())),
            new Feed(Feed::ATOM,
                common_local_url(
                    'ApiTimelineFriends', array(
                        'format' => 'atom',
                        'id' => $this->target->getNickname()
                    )
                ),
                // TRANS: %s is user nickname.
                sprintf(_('Feed for friends of %s (Atom)'), $this->target->getNickname()))
        );
    }

    function showEmptyListMessage()
    {
        // TRANS: Empty list message. %s is a user nickname.
        $message = sprintf(_('This is the timeline for %s and friends but no one has posted anything yet.'), $this->target->getNickname()) . ' ';

        if (common_logged_in()) {
            if ($this->target->id === $this->scoped->id) {
                // TRANS: Encouragement displayed on logged in user's empty timeline.
                // TRANS: This message contains Markdown links. Keep "](" together.
                $message .= _('Try subscribing to more people, [join a group](%%action.groups%%) or post something yourself.');
            } else {
                // TRANS: %1$s is user nickname, %2$s is user nickname, %2$s is user nickname prefixed with "@".
                // TRANS: This message contains Markdown links. Keep "](" together.
                $message .= sprintf(_('You can try to [nudge %1$s](../%2$s) from their profile or [post something to them](%%%%action.newnotice%%%%?status_textarea=%3$s).'), $this->target->getNickname(), $this->target->getNickname(), '@' . $this->target->getNickname());
            }
        } else {
            // TRANS: Encouragement displayed on empty timeline user pages for anonymous users.
            // TRANS: %s is a user nickname. This message contains Markdown links. Keep "](" together.
            $message .= sprintf(_('Why not [register an account](%%%%action.register%%%%) and then nudge %s or post a notice to them.'), $this->target->getNickname());
        }

        $this->elementStart('div', 'guide');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    }

    function showContent()
    {
        if (Event::handle('StartShowAllContent', array($this))) {
            if ($this->scoped instanceof Profile && $this->scoped->isLocal() && $this->scoped->getUser()->streamModeOnly()) {
                $nl = new PrimaryNoticeList($this->notice, $this, array('show_n'=>NOTICES_PER_PAGE));
            } else {
                $nl = new ThreadedNoticeList($this->notice, $this, $this->scoped);
            }

            $cnt = $nl->show();

            if (0 == $cnt) {
                $this->showEmptyListMessage();
            }

            $this->pagination(
                $this->page > 1, $cnt > NOTICES_PER_PAGE,
                $this->page, 'all', array('nickname' => $this->target->getNickname())
            );

            Event::handle('EndShowAllContent', array($this));
        }
    }

    function showSections()
    {
        // Show invite button, as long as site isn't closed, and
        // we have a logged in user.
        if (common_config('invite', 'enabled') && !common_config('site', 'closed') && common_logged_in()) {
            if (!common_config('site', 'private')) {
                $ibs = new InviteButtonSection(
                    $this,
                    // TRANS: Button text for inviting more users to the StatusNet instance.
                    // TRANS: Less business/enterprise-oriented language for public sites.
                    _m('BUTTON', 'Send invite')
                );
            } else {
                $ibs = new InviteButtonSection($this);
            }
            $ibs->show();
        }
    }
}

class ThreadingInboxNoticeStream extends ThreadingNoticeStream
{
    function __construct(Profile $target, Profile $scoped=null)
    {
        parent::__construct(new InboxNoticeStream($target, $scoped));
    }
}

// END OF FILE
// ============================================================================
?>