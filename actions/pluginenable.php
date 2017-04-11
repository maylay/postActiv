<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: PluginEnable
 * Plugin enable action.
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
 * Plugin enable action.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
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
 * Plugin enable action.
 *
 * (Re)-enables a plugin from the default plugins list.
 *
 * Takes parameters:
 *
 *    - plugin: plugin name
 *    - token: session token to prevent CSRF attacks
 *    - ajax: boolean; whether to return Ajax or full-browser results
 *
 * Only works if the current user is logged in.
 */
class PluginEnableAction extends Action
{
    var $user;
    var $plugin;

    /**
     * Check pre-requisites and instantiate attributes
     *
     * @param Array $args array of arguments (URL, GET, POST)
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        // @fixme these are pretty common, should a parent class factor these out?

        // Only allow POST requests

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // TRANS: Client error displayed when trying to use another method than POST.
            // TRANS: Do not translate POST.
            $this->clientError(_('This action only accepts POST requests.'));
        }

        // CSRF protection

        $token = $this->trimmed('token');

        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token.'.
                                 ' Try again, please.'));
        }

        // Only for logged-in users

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
            $this->clientError(_('Not logged in.'));
        }

        if (!AdminPanelAction::canAdmin('plugins')) {
            // TRANS: Client error displayed when trying to enable or disable a plugin without access rights.
            $this->clientError(_('You cannot administer plugins.'));
        }

        $this->plugin = $this->arg('plugin');
        $defaultPlugins = common_config('plugins', 'default');
        if (!array_key_exists($this->plugin, $defaultPlugins)) {
            // TRANS: Client error displayed when trying to enable or disable a non-existing plugin.
            $this->clientError(_('No such plugin.'));
        }

        return true;
    }

    /**
     * Handle request
     *
     * Does the subscription and returns results.
     *
     * @return void
     */
    function handle()
    {
        $key = 'disable-' . $this->plugin;
        Config::save('plugins', $key, $this->overrideValue());

        // @fixme this is a pretty common pattern and should be refactored down
        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            $this->element('title', null, $this->successShortTitle());
            $this->elementEnd('head');
            $this->elementStart('body');
            $form = $this->successNextForm();
            $form->show();
            $this->elementEnd('body');
            $this->endHTML();
        } else {
            $url = common_local_url('pluginsadminpanel');
            common_redirect($url, 303);
        }
    }

    /**
     * Value to save into $config['plugins']['disable-<name>']
     */
    protected function overrideValue()
    {
        return 0;
    }

    protected function successShortTitle()
    {
        // TRANS: Page title for AJAX form return when enabling a plugin.
        return _m('plugin', 'Enabled');
    }

    protected function successNextForm()
    {
        return new DisablePluginForm($this, $this->plugin);
    }
}

// END OF FILE
// ============================================================================
?>