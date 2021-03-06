<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Show a single bookmark
 * 
 * PHP version 5
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
 * @category  Bookmark
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2010 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * Show a single bookmark, with associated information
 *
 * @category  Bookmark
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2010 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class ShowbookmarkAction extends ShownoticeAction
{
    protected $bookmark = null;

    function getNotice()
    {
        $this->id = $this->trimmed('id');

        $this->bookmark = Bookmark::getKV('id', $this->id);

        if (empty($this->bookmark)) {
            // TRANS: Client exception thrown when referring to a non-existing bookmark.
            throw new ClientException(_m('No such bookmark.'), 404);
        }

        $notice = Notice::getKV('uri', $this->bookmark->uri);

        if (empty($notice)) {
            // Did we used to have it, and it got deleted?
            // TRANS: Client exception thrown when referring to a non-existing bookmark.
            throw new ClientException(_m('No such bookmark.'), 404);
        }

        return $notice;
    }

    /**
     * Title of the page
     *
     * Used by Action class for layout.
     *
     * @return string page tile
     */
    function title()
    {
        // TRANS: Title for bookmark.
        // TRANS: %1$s is a user nickname, %2$s is a bookmark title.
        return sprintf(_m('%1$s\'s bookmark for "%2$s"'),
                       $this->user->nickname,
                       $this->bookmark->title);
    }

    /**
     * Overload page title display to show bookmark link
     *
     * @return void
     */
    function showPageTitle()
    {
        $this->elementStart('h1');
        $this->element('a',
                       array('href' => $this->bookmark->url),
                       $this->bookmark->title);
        $this->elementEnd('h1');
    }
}
