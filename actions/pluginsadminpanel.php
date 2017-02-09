<?php
/* ============================================================================
 * Title: PluginsAdminPanel
 * Plugins administration panel
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
 * Plugins administration panel
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o chimo <chimo@chromic.org>
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
 * Plugins settings
 */
class PluginsadminpanelAction extends AdminPanelAction
{
    /**
     * Returns the page title
     *
     * @return string page title
     */
    function title()
    {
        // TRANS: Tab and title for plugins admin panel.
        return _m('TITLE','Plugins');
    }

    /**
     * Instructions for using this form.
     *
     * @return string instructions
     */
    function getInstructions()
    {
        // TRANS: Instructions at top of plugin admin page.
        return _('Additional plugins can be enabled and configured manually. ' .
                 'See the <a href="https://git.gnu.io/gnu/gnu-social/blob/master/plugins/README.md">online plugin ' .
                 'documentation</a> for more details.');
    }

    /**
     * Show the plugins admin panel form
     *
     * @return void
     */
    function showForm()
    {
        $this->elementStart('fieldset', array('id' => 'settings_plugins_default'));

        // TRANS: Admin form section header
        $this->element('legend', null, _('Default plugins'), 'default');

        $this->showDefaultPlugins();

        $this->elementEnd('fieldset');
    }

    /**
     * Until we have a general plugin metadata infrastructure, for now
     * we'll just list up the ones we know from the global default
     * plugins list.
     */
    protected function showDefaultPlugins()
    {
        $plugins = array_keys(common_config('plugins', 'default'));
        natsort($plugins);

        if ($plugins) {
            $list = new PluginList($plugins, $this);
            $list->show();
        } else {
            $this->element('p', null,
                           // TRANS: Text displayed on plugin admin page when no plugin are enabled.
                           _('All default plugins have been disabled from the ' .
                             'site\'s configuration file.'));
        }
    }
}
?>