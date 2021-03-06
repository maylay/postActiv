<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Menu for public group of actions
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  Menu
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2008 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Menu for public group of actions
 *
 * @see      Widget
 */
class PublicGroupNav extends Menu
{

    var $actionName = null;

    /**
     * Show the menu
     *
     * @return void
     */
    function show()
    {
        $this->actionName = $this->action->trimmed('action');

        $this->action->elementStart('ul', array('class' => 'nav'));

        if (Event::handle('StartPublicGroupNav', array($this))) {
            if (!common_config('singleuser', 'enabled')) {
                // TRANS: Menu item in search group navigation panel.
                $this->out->menuItem(common_local_url('public'), _m('MENU','Public'),
                                     // TRANS: Menu item title in search group navigation panel.
                                     _('Public timeline'), $this->actionName == 'public', 'nav_timeline_public');
            }
            if (!common_config('public', 'localonly') || $this->action->getScoped() instanceof Profile) {
                // Allow network wide view if you're logged in
                // TRANS: Menu item in search group navigation panel.
                $this->out->menuItem(common_local_url('networkpublic'), _m('MENU','Network'),
                                     // TRANS: Menu item title in search group navigation panel.
                                     _('Network public timeline'), $this->actionName == 'networkpublic', 'nav_timeline_networkpublic');
            }

            // TRANS: Menu item in search group navigation panel.
            $this->out->menuItem(common_local_url('groups'), _m('MENU','Groups'),
                // TRANS: Menu item title in search group navigation panel.
                _('User groups'), $this->actionName == 'groups', 'nav_groups');

            if (count(common_config('nickname', 'featured')) > 0) {
                // TRANS: Menu item in search group navigation panel.
                $this->out->menuItem(common_local_url('featured'), _m('MENU','Featured'),
                    // TRANS: Menu item title in search group navigation panel.
                    _('Featured users'), $this->actionName == 'featured', 'nav_featured');
            }

            Event::handle('EndPublicGroupNav', array($this));
        }
        $this->action->elementEnd('ul');
    }
}
?>