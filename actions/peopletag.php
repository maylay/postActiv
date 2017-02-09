<?php
/* ============================================================================
 * Title: PeopleTag
 * Lists by a user
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
 * Lists by a user
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Zach Copley
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

require_once INSTALLDIR.'/lib/peopletaglist.php';
// cache 3 pages
define('PEOPLETAG_CACHE_WINDOW', PEOPLETAGS_PER_PAGE*3 + 1);

class PeopletagAction extends Action
{
    var $page = null;
    var $tag = null;

    function isReadOnly($args)
    {
        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Title for list page.
            // TRANS: %s is a list.
            return sprintf(_('Public list %s'), $this->tag);
        } else {
            // TRANS: Title for list page.
            // TRANS: %1$s is a list, %2$d is a page number.
            return sprintf(_('Public list %1$s, page %2$d'), $this->tag, $this->page);
        }
    }

    function prepare(array $args = array())
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        $tag_arg = $this->arg('tag');
        $tag = common_canonical_tag($tag_arg);

        // Permanent redirect on non-canonical nickname

        if ($tag_arg != $tag) {
            $args = array('tag' => $nickname);
            if ($this->page && $this->page != 1) {
                $args['page'] = $this->page;
            }
            common_redirect(common_local_url('peopletag', $args), 301);
        }
        $this->tag = $tag;

        return true;
    }

    function handle()
    {
        parent::handle();
        $this->showPage();
    }

    function showLocalNav()
    {
        $nav = new PublicGroupNav($this);
        $nav->show();
    }

    function showAnonymousMessage()
    {
        $notice =
          // TRANS: Message for anonymous users on list page.
          // TRANS: This message contains Markdown links in the form [description](link).
          _('Lists are how you sort similar ' .
            'people on %%site.name%%, a [micro-blogging]' .
            '(http://en.wikipedia.org/wiki/Micro-blogging) service ' .
            'based on the Free Software [StatusNet](http://status.net/) tool. ' .
            'You can then easily keep track of what they ' .
            'are doing by subscribing to the list\'s timeline.' );
        $this->elementStart('div', array('id' => 'anon_notice'));
        $this->raw(common_markup_to_html($notice));
        $this->elementEnd('div');
    }

    function showContent()
    {
        $offset = ($this->page-1) * PEOPLETAGS_PER_PAGE;
        $limit  = PEOPLETAGS_PER_PAGE + 1;

        $ptags = new Profile_list();
        $ptags->tag = $this->tag;

        $user = common_current_user();

        if (empty($user)) {
            $ckey = sprintf('profile_list:tag:%s', $this->tag);
            $ptags->private = false;
            $ptags->orderBy('profile_list.modified DESC');

            $c = Cache::instance();
            if ($offset+$limit <= PEOPLETAG_CACHE_WINDOW && !empty($c)) {
                $cached_ptags = Profile_list::getCached($ckey, $offset, $limit);
                if ($cached_ptags === false) {
                    $ptags->limit(0, PEOPLETAG_CACHE_WINDOW);
                    $ptags->find();

                    Profile_list::setCache($ckey, $ptags, $offset, $limit);
                } else {
                    $ptags = clone($cached_ptags);
                }
            } else {
                $ptags->limit($offset, $limit);
                $ptags->find();
            }
        } else {
            $ptags->whereAdd('(profile_list.private = false OR (' .
                             ' profile_list.tagger =' . $user->id .
                             ' AND profile_list.private = true) )');

            $ptags->orderBy('profile_list.modified DESC');
            $ptags->find();
        }

        $pl = new PeopletagList($ptags, $this);
        $cnt = $pl->show();

        $this->pagination($this->page > 1, $cnt > PEOPLETAGS_PER_PAGE,
                          $this->page, 'peopletag', array('tag' => $this->tag));
    }

    function showSections()
    {
    }
}
?>