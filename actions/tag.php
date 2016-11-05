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
 * PHP version 5
 *
 * @category  Actions
 * @package   postActiv
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Evan Prodromou
 * @author    Ciaran Gultneiks <ciaran@ciarang.com>
 * @author    Zach Copley
 * @author    Sarven Capadisli
 * @author    Adrian Lang <mail@adrianlang.de>
 * @author    Meitar Moscovitz <meitarm@gmail.com>
 * @author    Robin Millette <robin@millette.info>
 * @author    Craig Andrews <candrews@integralblue.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Julien Chaumond <chaumond@gmail.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

// @todo FIXME: documentation missing.
class TagAction extends ManagedAction
{
    var $notice;
    var $tag;
    var $page;

    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $taginput = $this->trimmed('tag');
        $this->tag = common_canonical_tag($taginput);

        if (empty($this->tag)) {
            throw new ClientException(_('No valid tag data.'));
        }

        // after common_canonical_tag we have a lowercase, no-specials tag string
        if ($this->tag !== $taginput) {
            common_redirect(common_local_url('tag', array('tag' => $this->tag)), 301);
        }

        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        common_set_returnto($this->selfUrl());

        $this->notice = Notice_tag::getStream($this->tag)->getNotices(($this->page-1)*NOTICES_PER_PAGE,
                                                                       NOTICES_PER_PAGE + 1);

        if($this->page > 1 && $this->notice->N == 0){
            // TRANS: Client error when page not found (404).
            $this->clientError(_('No such page.'), 404);
        }

        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Title for first page of notices with tags.
            // TRANS: %s is the tag.
            return sprintf(_('Notices tagged with %s'), $this->tag);
        } else {
            // TRANS: Title for all but the first page of notices with tags.
            // TRANS: %1$s is the tag, %2$d is the page number.
            return sprintf(_('Notices tagged with %1$s, page %2$d'),
                           $this->tag,
                           $this->page);
        }
    }

    function getFeeds()
    {
        return array(new Feed(Feed::JSON,
                              common_local_url('ApiTimelineTag',
                                               array('format' => 'as',
                                                     'tag' => $this->tag)),
                              // TRANS: Link label for feed on "notices with tag" page.
                              // TRANS: %s is the tag the feed is for.
                              sprintf(_('Notice feed for tag %s (Activity Streams JSON)'),
                                      $this->tag)),
                     new Feed(Feed::RSS1,
                              common_local_url('tagrss',
                                               array('tag' => $this->tag)),
                              // TRANS: Link label for feed on "notices with tag" page.
                              // TRANS: %s is the tag the feed is for.
                              sprintf(_('Notice feed for tag %s (RSS 1.0)'),
                                      $this->tag)),
                     new Feed(Feed::RSS2,
                              common_local_url('ApiTimelineTag',
                                               array('format' => 'rss',
                                                     'tag' => $this->tag)),
                              // TRANS: Link label for feed on "notices with tag" page.
                              // TRANS: %s is the tag the feed is for.
                              sprintf(_('Notice feed for tag %s (RSS 2.0)'),
                                      $this->tag)),
                     new Feed(Feed::ATOM,
                              common_local_url('ApiTimelineTag',
                                               array('format' => 'atom',
                                                     'tag' => $this->tag)),
                              // TRANS: Link label for feed on "notices with tag" page.
                              // TRANS: %s is the tag the feed is for.
                              sprintf(_('Notice feed for tag %s (Atom)'),
                                      $this->tag)));
    }

    protected function showContent()
    {
        if(Event::handle('StartTagShowContent', array($this))) {

            $nl = new PrimaryNoticeList($this->notice, $this, array('show_n'=>NOTICES_PER_PAGE));

            $cnt = $nl->show();

            $this->pagination($this->page > 1, $cnt > NOTICES_PER_PAGE,
                              $this->page, 'tag', array('tag' => $this->tag));

            Event::handle('EndTagShowContent', array($this));
        }
    }

    function isReadOnly($args)
    {
        return true;
    }
}
?>