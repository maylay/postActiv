<?php
/* ============================================================================
 * Title: Tag
 * Action class to contain Tag actions.
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
 * Action class to contain Tag actions.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Evan Prodromou
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Zach Copley
 * o Sarven Capadisli
 * o Adrian Lang <mail@adrianlang.de>
 * o Meitar Moscovitz <meitarm@gmail.com>
 * o Robin Millette <robin@millette.info>
 * o Craig Andrews <candrews@integralblue.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Julien C <chaumond@gmail.com>
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

// ============================================================================
// Class: TagAction
// Action class to display a hashtag and posts under it
//
// Variables:
// o notice - current notice
// o tag - current tag
// o page - current page
class TagAction extends ManagedAction {
   var $notice;
   var $tag;
   var $page;

   // -------------------------------------------------------------------------
   // Function: prepare
   // Prepares a tag for display based on our current notice/page
   //
   // Parameters:
   // o args - passed to parent prepare()
   //
   // Returns:
   // true, unless an error has been encounterd
   //
   // Error Conditions:
   // o no valid tag data, throws a client exception
   // o tag contains upperclase characters, redirects
   // o page is out of bounds, 404s
   protected function prepare(array $args=array()) {
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


   // -------------------------------------------------------------------------
   // Function: title
   // Returns the title of the page for the tag display
   function title() {
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


   // -------------------------------------------------------------------------
   // Function: getFeeds
   // Returns an array of the various feeds for the tag display page
   function getFeeds() {
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


   // -------------------------------------------------------------------------
   // Function: showContent
   // Shows the notices containing this page, paginated according to the
   // current settings for pagination.
   //
   // Parameters:
   // o none
   //
   // Returns:
   // o void
   protected function showContent() {
      if(Event::handle('StartTagShowContent', array($this))) {
         $nl = new PrimaryNoticeList($this->notice, $this, array('show_n'=>NOTICES_PER_PAGE));
         $cnt = $nl->show();
         $this->pagination($this->page > 1, $cnt > NOTICES_PER_PAGE,
                           $this->page, 'tag', array('tag' => $this->tag));
         Event::handle('EndTagShowContent', array($this));
      }
   }


   // -------------------------------------------------------------------------
   // Function: isReadOnly
   // Returns whether this action class is read-only (yes)
   //
   // Parameters:
   // o array $args - ignored
   //
   // Returns:
   // o boolean true
   function isReadOnly($args) {
      return true;
   }
}

// END OF FILE
// ============================================================================
?>