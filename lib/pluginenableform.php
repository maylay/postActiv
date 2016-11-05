<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Form for enabling/disabling plugins
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
 * @category  Form
 * @package   StatusNet
 * @copyright 2010 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

require_once INSTALLDIR.'/lib/form.php';

/**
 * Form for joining a group
 *
 * @category Form
 * @package  StatusNet
 * @author   Brion Vibber <brion@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 *
 * @see      PluginDisableForm
 */

class PluginEnableForm extends Form
{
    /**
     * Plugin to enable/disable
     */

    var $plugin = null;

    /**
     * Constructor
     *
     * @param HTMLOutputter $out    output channel
     * @param string        $plugin plugin to enable/disable
     */

    function __construct($out=null, $plugin=null)
    {
        parent::__construct($out);

        $this->plugin = $plugin;
    }

    /**
     * ID of the form
     *
     * @return string ID of the form
     */

    function id()
    {
        return 'plugin-enable-' . $this->plugin;
    }

    /**
     * class of the form
     *
     * @return string of the form class
     */

    function formClass()
    {
        return 'form_plugin_enable';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */

    function action()
    {
        return common_local_url('pluginenable',
                                array('plugin' => $this->plugin));
    }

    /**
     * Action elements
     *
     * @return void
     */

    function formActions()
    {
        // TRANS: Plugin admin panel controls
        $this->out->submit('submit', _m('plugin', 'Enable'));
    }
}
?>