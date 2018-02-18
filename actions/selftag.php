<?php
/* ============================================================================
 * Title: SelfTag
 * Action for showing profiles self-tagged with a given tag
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
 * Action for showing profiles self-tagged with a given tag
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikeal Nordfeldth <mmn@hethane.se>
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
 * This class outputs a paginated list of profiles self-tagged with a given tag
 */
class SelftagAction extends Action
{
    var $tag  = null;
    var $page = null;

    /**
     * For initializing members of the class.
     *
     * @param array $argarray misc. arguments
     *
     * @return boolean true
     */
    function prepare($argarray)
    {
        parent::prepare($argarray);

        $this->tag = $this->trimmed('tag');

        if (!common_valid_profile_tag($this->tag)) {
            // TRANS: Client error displayed when trying to list a profile with an invalid list.
            // TRANS: %s is the invalid list name.
            $this->clientError(sprintf(_('Not a valid list: %s.'),
                $this->tag));
            return;
        }

        $this->page = ($this->arg('page')) ? $this->arg('page') : 1;

        common_set_returnto($this->selfUrl());

        return true;
    }

    /**
     * Handler method
     *
     * @return boolean is read only action?
     */
    function handle()
    {
        parent::handle();
        $this->showPage();
    }

    /**
     * Whips up a query to get a list of profiles based on the provided
     * people tag and page, initalizes a ProfileList widget, and displays
     * it to the user.
     *
     * @return nothing
     */
    function showContent()
    {
        $profile = new Profile();

        $offset = ($this->page - 1) * PROFILES_PER_PAGE;
        $limit  = PROFILES_PER_PAGE + 1;

        if (common_config('db', 'type') == 'pgsql') {
            $lim = ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $lim = ' LIMIT ' . $offset . ', ' . $limit;
        }

        // XXX: memcached this

        $qry =  'SELECT profile.* ' .
                'FROM profile JOIN ( profile_tag, profile_list ) ' .
                'ON profile.id = profile_tag.tagger ' .
                'AND profile_tag.tagger = profile_list.tagger ' .
                'AND profile_list.tag = profile_tag.tag ' .
                'WHERE profile_tag.tagger = profile_tag.tagged ' .
                "AND profile_tag.tag = '%s' ";

        $user = common_current_user();
        if (empty($user)) {
            $qry .= 'AND profile_list.private = false ';
        } else {
            $qry .= 'AND (profile_list.tagger = ' . $user->id .
                    ' OR profile_list.private = false) ';
        }

        $qry .= 'ORDER BY profile_tag.modified DESC%s';

        $profile->query(sprintf($qry, $this->tag, $lim));

        $ptl = new SelfTagProfileList($profile, $this); // pass the ammunition
        $cnt = $ptl->show();

        $this->pagination($this->page > 1,
                          $cnt > PROFILES_PER_PAGE,
                          $this->page,
                          'selftag',
                          array('tag' => $this->tag));
    }

    /**
     * Returns the page title
     *
     * @return string page title
     */
    function title()
    {
        // TRANS: Page title for page showing self tags.
        // TRANS: %1$s is a tag, %2$d is a page number.
        return sprintf(_('Users self-tagged with %1$s, page %2$d'),
            $this->tag, $this->page);
    }
}

class SelfTagProfileList extends ProfileList
{
    function newListItem(Profile $target)
    {
        return new SelfTagProfileListItem($target, $this->action);
    }
}

class SelfTagProfileListItem extends ProfileListItem
{
    function linkAttributes()
    {
        $aAttrs = parent::linkAttributes();

        if (common_config('nofollow', 'selftag')) {
            $aAttrs['rel'] .= ' nofollow';
        }

        return $aAttrs;
    }

    function homepageAttributes()
    {
        $aAttrs = parent::linkAttributes();

        if (common_config('nofollow', 'selftag')) {
            $aAttrs['rel'] = 'nofollow';
        }

        return $aAttrs;
    }

    function showTags()
    {
        $selftags = new SelfTagsWidget($this->out, $this->profile, $this->profile);
        $selftags->show();

        $user = common_current_user();

        if (!empty($user) && $user->id != $this->profile->id &&
                $user->getProfile()->canTag($this->profile)) {
            $yourtags = new PeopleTagsWidget($this->out, $user, $this->profile);
            $yourtags->show();
        }
    }
}

// END OF FILE
// ============================================================================
?>