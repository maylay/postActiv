<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: PeopleSearch
 * People search action class.
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
 * People search action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Robin Millette <robin@millette.info>
 * o Sarven Capadisli
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

require_once INSTALLDIR.'/lib/searchaction.php';
require_once INSTALLDIR.'/lib/profilelist.php';

/**
 * People search action class.
 */
class PeoplesearchAction extends SearchAction
{
    function getInstructions()
    {
        // TRANS: Instructions for the "People search" page.
        // TRANS: %%site.name%% is the name of the StatusNet site.
        return _('Search for people on %%site.name%% by their name, location, or interests. ' .
                  'Separate the terms by spaces; they must be 3 characters or more.');
    }

    function title()
    {
        // TRANS: Title of a page where users can search for other users.
        return _('People search');
    }

    function showResults($q, $page)
    {
        $profile = new Profile();
        $search_engine = $profile->getSearchEngine('profile');
        $search_engine->set_sort_mode('chron');
        // Ask for an extra to see if there's more.
        $search_engine->limit((($page-1)*PROFILES_PER_PAGE), PROFILES_PER_PAGE + 1);
        if (false === $search_engine->query($q)) {
            $cnt = 0;
        }
        else {
            $cnt = $profile->find();
        }
        if ($cnt > 0) {
            $terms = preg_split('/[\s,]+/', $q);
            $results = new PeopleSearchResults($profile, $terms, $this);
            $results->show();
            $profile->free();
            $this->pagination($page > 1, $cnt > PROFILES_PER_PAGE,
                          $page, 'peoplesearch', array('q' => $q));

        } else {
            // TRANS: Message on the "People search" page where a query has no results.
            $this->element('p', 'error', _('No results.'));
            $this->searchSuggestions($q);
            $profile->free();
        }
    }

    function showScripts()
    {
        parent::showScripts();
        $this->autofocus('q');
    }
}

/**
 * People search results class
 *
 * Derivative of ProfileList with specialization for highlighting search terms.
 *
 * @category Widget
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @author   Robin Millette <millette@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * @see PeoplesearchAction
 */

class PeopleSearchResults extends ProfileList
{
    var $terms = null;
    var $pattern = null;

    function __construct($profile, $terms, $action)
    {
        parent::__construct($profile, $action);

        $this->terms = array_map('preg_quote',
                                 array_map('htmlspecialchars', $terms));

        $this->pattern = '/('.implode('|',$terms).')/i';
    }

    function newProfileItem($profile)
    {
        return new PeopleSearchResultItem($profile, $this->action);
    }
}

class PeopleSearchResultItem extends ProfileListItem
{
    function highlight($text)
    {
        return preg_replace($this->pattern, '<strong>\\1</strong>', htmlspecialchars($text));
    }
}

// END OF FILE
// ============================================================================
?>