<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: ShowStream
 * User profile page
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
 * When I created this page, "show stream" seemed like the best name for it.
 * Now, it seems like a really bad name.
 *
 * It shows a stream of the user's posts, plus lots of profile info, links
 * to subscriptions and stuff, etc.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Matthew Gregg <matthew.gregg@gmail.com>
 * o Sarven Capadisli
 * o Robin Millette <robin@millette.info>
 * o Zach Copley
 * o Adrian Lang <mail@adrianlang.de>
 * o Sean Murphy <sgmurphy@gmail.com>
 * o Meitar Moscovitz <meitarm@gmail.com>
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Jeffery To <jeffery.to@gmail.com>
 * o Christopher Vollick <psycotica0@gmail.com>
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Craig Andrews <candrews@integralblue.com>
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

/**
 * User profile page
 */
class ShowstreamAction extends NoticestreamAction
{
    public function getStream()
    {
        if (empty($this->tag)) {
            $stream = new ProfileNoticeStream($this->target, $this->scoped);
        } else {
            $stream = new TaggedProfileNoticeStream($this->target, $this->tag, $this->scoped);
        }

        return $stream;
    }

    function title()
    {
        $base = $this->target->getFancyName();
        if (!empty($this->tag)) {
            if ($this->page == 1) {
                // TRANS: Page title showing tagged notices in one user's timeline.
                // TRANS: %1$s is the username, %2$s is the hash tag.
                return sprintf(_('Notices by %1$s tagged %2$s'), $base, $this->tag);
            } else {
                // TRANS: Page title showing tagged notices in one user's timeline.
                // TRANS: %1$s is the username, %2$s is the hash tag, %3$d is the page number.
                return sprintf(_('Notices by %1$s tagged %2$s, page %3$d'), $base, $this->tag, $this->page);
            }
        } else {
            if ($this->page == 1) {
                return sprintf(_('Notices by %s'), $base);
            } else {
                // TRANS: Extended page title showing tagged notices in one user's timeline.
                // TRANS: %1$s is the username, %2$d is the page number.
                return sprintf(_('Notices by %1$s, page %2$d'),
                               $base,
                               $this->page);
            }
        }
    }

    protected function showContent()
    {
        $this->showNotices();
    }

    function showProfileBlock()
    {
        $block = new AccountProfileBlock($this, $this->target);
        $block->show();
    }

    function showPageNoticeBlock()
    {
        return;
    }

    function getFeeds()
    {
        if (!empty($this->tag)) {
            return array(new Feed(Feed::RSS1,
                                  common_local_url('userrss',
                                                   array('nickname' => $this->target->getNickname(),
                                                         'tag' => $this->tag)),
                                  // TRANS: Title for link to notice feed.
                                  // TRANS: %1$s is a user nickname, %2$s is a hashtag.
                                  sprintf(_('Notice feed for %1$s tagged %2$s (RSS 1.0)'),
                                          $this->target->getNickname(), $this->tag)));
        }

        if (!$this->target->isLocal()) {
            // remote profiles at least have Atom, but we can't guarantee anything else
            return array(
                     new Feed(Feed::ATOM,
                              $this->target->getAtomFeed(),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              sprintf(_('Notice feed for %s (Atom)'),
                                      $this->target->getNickname()))
                     );
        }

        return array(new Feed(Feed::JSON,
                              common_local_url('ApiTimelineUser',
                                               array(
                                                    'id' => $this->target->getID(),
                                                    'format' => 'as')),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              sprintf(_('Notice feed for %s (Activity Streams JSON)'),
                                      $this->target->getNickname())),
                     new Feed(Feed::RSS1,
                              common_local_url('userrss',
                                               array('nickname' => $this->target->getNickname())),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              sprintf(_('Notice feed for %s (RSS 1.0)'),
                                      $this->target->getNickname())),
                     new Feed(Feed::RSS2,
                              common_local_url('ApiTimelineUser',
                                               array(
                                                    'id' => $this->target->getID(),
                                                    'format' => 'rss')),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              sprintf(_('Notice feed for %s (RSS 2.0)'),
                                      $this->target->getNickname())),
                     new Feed(Feed::ATOM,
                              $this->target->getAtomFeed(),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              sprintf(_('Notice feed for %s (Atom)'),
                                      $this->target->getNickname())),
                     new Feed(Feed::FOAF,
                              common_local_url('foaf', array('nickname' =>
                                                             $this->target->getNickname())),
                              // TRANS: Title for link to notice feed. FOAF stands for Friend of a Friend.
                              // TRANS: More information at http://www.foaf-project.org. %s is a user nickname.
                              sprintf(_('FOAF for %s'), $this->target->getNickname())));
    }

    public function extraHeaders()
    {
        parent::extraHeaders();
        // Publish all the rel="me" in the HTTP headers on our main profile page
        if (get_class($this) == 'ShowstreamAction') {
            foreach ($this->target->getRelMes() as $relMe) {
                header('Link: <'.htmlspecialchars($relMe['href']).'>; rel="me"', false);
            }
        }
    }

    function extraHead()
    {
        if ($this->target->bio) {
            $this->element('meta', array('name' => 'description',
                                         'content' => $this->target->getDescription()));
        }

        $rsd = common_local_url('rsd',
                                array('nickname' => $this->target->getNickname()));

        // RSD, http://tales.phrasewise.com/rfc/rsd
        $this->element('link', array('rel' => 'EditURI',
                                     'type' => 'application/rsd+xml',
                                     'href' => $rsd));

        if ($this->page != 1) {
            $this->element('link', array('rel' => 'canonical',
                                         'href' => $this->target->getUrl()));
        }
    }

    function showEmptyListMessage()
    {
        // TRANS: First sentence of empty list message for a timeline. $1%s is a user nickname.
        $message = sprintf(_('This is the timeline for %1$s, but %1$s hasn\'t posted anything yet.'), $this->target->getNickname()) . ' ';

        if ($this->scoped instanceof Profile) {
            if ($this->target->getID() === $this->scoped->getID()) {
                // TRANS: Second sentence of empty list message for a stream for the user themselves.
                $message .= _('Seen anything interesting recently? You haven\'t posted any notices yet, now would be a good time to start :)');
            } else {
                // TRANS: Second sentence of empty  list message for a non-self timeline. %1$s is a user nickname, %2$s is a part of a URL.
                // TRANS: This message contains a Markdown link. Keep "](" together.
                $message .= sprintf(_('You can try to nudge %1$s or [post something to them](%%%%action.newnotice%%%%?status_textarea=%2$s).'), $this->target->getNickname(), '@' . $this->target->getNickname());
            }
        }
        else {
            // TRANS: Second sentence of empty message for anonymous users. %s is a user nickname.
            // TRANS: This message contains a Markdown link. Keep "](" together.
            $message .= sprintf(_('Why not [register an account](%%%%action.register%%%%) and then nudge %s or post a notice to them.'), $this->target->getNickname());
        }

        $this->elementStart('div', 'guide');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    }

    function showNotices()
    {
        $pnl = new PrimaryNoticeList($this->notice, $this);
        $cnt = $pnl->show();
        if (0 == $cnt) {
            $this->showEmptyListMessage();
        }

        // either nickname or id will be used, depending on which action (showstream, userbyid...)
        $args = array('nickname' => $this->target->getNickname(), 'id' => $this->target->getID());
        if (!empty($this->tag))
        {
            $args['tag'] = $this->tag;
        }
        $this->pagination($this->page>1, $cnt>NOTICES_PER_PAGE, $this->page,
                          $this->getActionName(), $args);
    }

    function showAnonymousMessage()
    {
        if (!(common_config('site','closed') || common_config('site','inviteonly'))) {
            // TRANS: Announcement for anonymous users showing a timeline if site registrations are open.
            // TRANS: This message contains a Markdown link. Keep "](" together.
            $m = sprintf(_('**%s** has an account on %%%%site.name%%%%, a [micro-blogging](http://en.wikipedia.org/wiki/Micro-blogging) service ' .
                           'based on the Free Software [StatusNet](http://status.net/) tool. ' .
                           '[Join now](%%%%action.register%%%%) to follow **%s**\'s notices and many more! ([Read more](%%%%doc.help%%%%))'),
                         $this->target->getNickname(), $this->target->getNickname());
        } else {
            // TRANS: Announcement for anonymous users showing a timeline if site registrations are closed or invite only.
            // TRANS: This message contains a Markdown link. Keep "](" together.
            $m = sprintf(_('**%s** has an account on %%%%site.name%%%%, a [micro-blogging](http://en.wikipedia.org/wiki/Micro-blogging) service ' .
                           'based on the Free Software [StatusNet](http://status.net/) tool.'),
                         $this->target->getNickname(), $this->target->getNickname());
        }
        $this->elementStart('div', array('id' => 'anon_notice'));
        $this->raw(common_markup_to_html($m));
        $this->elementEnd('div');
    }

    function noticeFormOptions()
    {
        $options = parent::noticeFormOptions();

        if (!$this->scoped instanceof Profile || !$this->scoped->sameAs($this->target)) {
            $options['to_profile'] =  $this->target;
        }

        return $options;
    }
}

// END OF FILE
// ============================================================================
?>
