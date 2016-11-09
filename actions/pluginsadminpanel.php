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
 * Plugins administration panel
 *
 * @category  Admin
 * @package   postActiv
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    chimo <chimo@chromic.org>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================
 */

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