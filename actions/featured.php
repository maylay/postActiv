<?php
/* ============================================================================
 * Title: Featured
 * List of featured users
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
 * List of featured users
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Evan Prodromou
 * o Brenda Wallace <shiny@cpan.org>
 * o Seibrand Mazeland <s.mazeland@xs4all.nl>
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

require_once INSTALLDIR.'/lib/profilelist.php';
require_once INSTALLDIR.'/lib/publicgroupnav.php';

/**
 * List of featured users
 */
class FeaturedAction extends Action
{
    var $page = null;

    function isReadOnly($args)
    {
        return true;
    }

    function prepare(array $args = array())
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Page title for first page of featured users.
            return _('Featured users');
        } else {
            // TRANS: Page title for all but first page of featured users.
            // TRANS: %d is the page number being displayed.
            return sprintf(_('Featured users, page %d'), $this->page);
        }
    }

    function handle()
    {
        parent::handle();

        $this->showPage();
    }

    function showPageNotice()
    {
        $instr = $this->getInstructions();
        $output = common_markup_to_html($instr);
        $this->elementStart('div', 'instructions');
        $this->raw($output);
        $this->elementEnd('div');
    }

    function getInstructions()
    {
        // TRANS: Description on page displaying featured users.
        return sprintf(_('A selection of some great users on %s.'),
                       common_config('site', 'name'));
    }

    function showContent()
    {
        // XXX: Note I'm doing it this two-stage way because a raw query
        // with a JOIN was *not* working. --Zach

        $featured_nicks = common_config('nickname', 'featured');

        if (count($featured_nicks) > 0) {

            $quoted = array();

            foreach ($featured_nicks as $nick) {
                $quoted[] = "'$nick'";
            }

            $user = new User;
            $user->whereAdd(sprintf('nickname IN (%s)', implode(',', $quoted)));
            $user->limit(($this->page - 1) * PROFILES_PER_PAGE, PROFILES_PER_PAGE + 1);
            $user->orderBy(common_database_tablename('user') .'.nickname ASC');

            $user->find();

            $profile_ids = array();

            while ($user->fetch()) {
                $profile_ids[] = $user->id;
            }

            $profile = new Profile;
            $profile->whereAdd(sprintf('profile.id IN (%s)', implode(',', $profile_ids)));
            $profile->orderBy('nickname ASC');

            $cnt = $profile->find();

            if ($cnt > 0) {
                $featured = new ProfileList($profile, $this);
                $featured->show();
            }

            $profile->free();

            $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                              $this->page, 'featured');
        }
    }
}
?>