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
 * List of featured users
 *
 * @category  Public
 * @package   postActiv
 * @author    Zach Copley <zach@copley.name>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Brenda Wallace <shiny@cpan.org>
 * @author    Seibrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://www.postactiv.com
 */

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