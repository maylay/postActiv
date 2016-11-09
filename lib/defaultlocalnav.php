<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Default local nav
 *
 * @category  Menu
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Default menu
 */
class DefaultLocalNav extends Menu
{
    function show()
    {
        $user = common_current_user();

        $this->action->elementStart('ul', array('id' => 'nav_local_default'));

        if (Event::handle('StartDefaultLocalNav', array($this, $user))) {

            if (!empty($user)) {
                $pn = new PersonalGroupNav($this->action);
                // TRANS: Menu item in default local navigation panel.
                $this->submenu(_m('MENU','Home'), $pn);
            }

            $bn = new PublicGroupNav($this->action);
            // TRANS: Menu item in default local navigation panel.
            $this->submenu(_m('MENU','Public'), $bn);

            if (!empty($user)) {
                $sn = new GroupsNav($this->action, $user);
                if ($sn->haveGroups()) {
                    // TRANS: Menu item in default local navigation panel.
                    $this->submenu(_m('MENU', 'Groups'), $sn);
                }
            }

            if (!empty($user)) {
                $sn = new ListsNav($this->action, $user->getProfile());
                if ($sn->hasLists()) {
                    // TRANS: Menu item in default local navigation panel.
                    $this->submenu(_m('MENU', 'Lists'), $sn);
                }
            }

            Event::handle('EndDefaultLocalNav', array($this, $user));
        }

        $this->action->elementEnd('ul');
    }
}
?>