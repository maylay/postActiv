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
 * Menu for admin panels
 *
 * @category  Menu
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2011-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Menu for admin panels
 */
class AdminPanelNav extends Menu
{
    /**
     * Show the menu
     *
     * @return void
     */
    function show()
    {
        $action_name = $this->action->trimmed('action');
        $user = common_current_user();
        $nickname = $user->nickname;
        $name = $user->getProfile()->getBestName();

        $stub = new HomeStubNav($this->action);
        $this->submenu(_m('MENU','Home'), $stub);

        $this->action->elementStart('ul');
        $this->action->elementStart('li');
        // TRANS: Header in administrator navigation panel.
        $this->action->element('h3', null, _m('HEADER','Admin'));
        $this->action->elementStart('ul', array('class' => 'nav'));

        if (Event::handle('StartAdminPanelNav', array($this))) {

            if (AdminPanelAction::canAdmin('site')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('Basic site configuration');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('siteadminpanel'), _m('MENU', 'Site'),
                                     $menu_title, $action_name == 'siteadminpanel', 'nav_site_admin_panel');
            }

            if (AdminPanelAction::canAdmin('user')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('User configuration');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('useradminpanel'), _m('MENU','User'),
                                     $menu_title, $action_name == 'useradminpanel', 'nav_user_admin_panel');
            }

            if (AdminPanelAction::canAdmin('access')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('Access configuration');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('accessadminpanel'), _m('MENU','Access'),
                                     $menu_title, $action_name == 'accessadminpanel', 'nav_access_admin_panel');
            }

            if (AdminPanelAction::canAdmin('paths')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('Paths configuration');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('pathsadminpanel'), _m('MENU','Paths'),
                                    $menu_title, $action_name == 'pathsadminpanel', 'nav_paths_admin_panel');
            }

            if (AdminPanelAction::canAdmin('sessions')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('Sessions configuration');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('sessionsadminpanel'), _m('MENU','Sessions'),
                                     $menu_title, $action_name == 'sessionsadminpanel', 'nav_sessions_admin_panel');
            }

            if (AdminPanelAction::canAdmin('sitenotice')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('Edit site notice');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('sitenoticeadminpanel'), _m('MENU','Site notice'),
                                     $menu_title, $action_name == 'sitenoticeadminpanel', 'nav_sitenotice_admin_panel');
            }

            if (AdminPanelAction::canAdmin('license')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('Set site license');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('licenseadminpanel'), _m('MENU','License'),
                                     $menu_title, $action_name == 'licenseadminpanel', 'nav_license_admin_panel');
            }

            if (AdminPanelAction::canAdmin('plugins')) {
                // TRANS: Menu item title in administrator navigation panel.
                $menu_title = _('Plugins configuration');
                // TRANS: Menu item in administrator navigation panel.
                $this->out->menuItem(common_local_url('pluginsadminpanel'), _m('MENU','Plugins'),
                                     $menu_title, $action_name == 'pluginsadminpanel', 'nav_plugin_admin_panel');
            }

            Event::handle('EndAdminPanelNav', array($this));
        }
        $this->action->elementEnd('ul');
        $this->action->elementEnd('li');
        $this->action->elementEnd('ul');
    }
}
?>