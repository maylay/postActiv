<?php
/* ============================================================================
 * Title: NewApplication
 * Register a new OAuth Application
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 * Add a new application
 *
 * This is the form for adding a new application
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Evan Prodromou
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
 * Add a new application
 *
 * This is the form for adding a new application
 */
class NewApplicationAction extends SettingsAction
{
    function title()
    {
        // TRANS: This is the title of the form for adding a new application.
        return _('New application');
    }

    protected function doPost()
    {
        if ($this->arg('cancel')) {
            common_redirect(common_local_url('oauthappssettings'), 303);
        } elseif ($this->arg('save')) {
            //trySave will never return, just throw exception or redirect
            $this->trySave();
        }

        // TRANS: Client error displayed when encountering an unexpected action on form submission.
        $this->clientError(_('Unexpected form submission.'));
    }

    protected function getForm()
    {
        return new ApplicationEditForm($this);
    }

    protected function getInstructions()
    {
        // TRANS: Form instructions for registering a new application.
        return _('Use this form to register a new application.');
    }

    protected function trySave()
    {
        $name         = $this->trimmed('name');
        $description  = $this->trimmed('description');
        $source_url   = $this->trimmed('source_url');
        $organization = $this->trimmed('organization');
        $homepage     = $this->trimmed('homepage');
        $callback_url = $this->trimmed('callback_url');
        $type         = $this->arg('app_type');
        $access_type  = $this->arg('default_access_type');

        if (empty($name)) {
            // TRANS: Validation error shown when not providing a name in the "New application" form.
            $this->clientError(_('Name is required.'));
        } else if ($this->nameExists($name)) {
            // TRANS: Validation error shown when providing a name for an application that already exists in the "New application" form.
            $this->clientError(_('Name already in use. Try another one.'));
        } elseif (mb_strlen($name) > 255) {
            // TRANS: Validation error shown when providing too long a name in the "New application" form.
            $this->clientError(_('Name is too long (maximum 255 characters).'));
        } elseif (empty($description)) {
            // TRANS: Validation error shown when not providing a description in the "New application" form.
            $this->clientError(_('Description is required.'));
        } elseif (Oauth_application::descriptionTooLong($description)) {
            $this->clientError(sprintf(
                // TRANS: Form validation error in New application form.
                // TRANS: %d is the maximum number of characters for the description.
                _m('Description is too long (maximum %d character).',
                   'Description is too long (maximum %d characters).',
                   Oauth_application::maxDesc()),
                Oauth_application::maxDesc()));
        } elseif (empty($source_url)) {
            // TRANS: Validation error shown when not providing a source URL in the "New application" form.
            $this->clientError(_('Source URL is required.'));
        } elseif ((strlen($source_url) > 0) && !common_valid_http_url($source_url)) {
            // TRANS: Validation error shown when providing an invalid source URL in the "New application" form.
            $this->clientError(_('Source URL is not valid.'));
        } elseif (empty($organization)) {
            // TRANS: Validation error shown when not providing an organisation in the "New application" form.
            $this->clientError(_('Organization is required.'));
        } elseif (mb_strlen($organization) > 255) {
            // TRANS: Validation error shown when providing too long an arganisation name in the "Edit application" form.
            $this->clientError(_('Organization is too long (maximum 255 characters).'));
        } elseif (empty($homepage)) {
            // TRANS: Form validation error show when an organisation name has not been provided in the new application form.
            $this->clientError(_('Organization homepage is required.'));
        } elseif ((strlen($homepage) > 0) && !common_valid_http_url($homepage)) {
            // TRANS: Validation error shown when providing an invalid homepage URL in the "New application" form.
            $this->clientError(_('Homepage is not a valid URL.'));
        } elseif (mb_strlen($callback_url) > 255) {
            // TRANS: Validation error shown when providing too long a callback URL in the "New application" form.
            $this->clientError(_('Callback is too long.'));
        } elseif (strlen($callback_url) > 0 && !common_valid_http_url($callback_url)) {
            // TRANS: Validation error shown when providing an invalid callback URL in the "New application" form.
            $this->clientError(_('Callback URL is not valid.'));
        }

        // Login is checked in parent::prepare()
        assert(!is_null($this->scoped));

        $app = new Oauth_application();

        $app->query('BEGIN');

        $app->name         = $name;
        $app->owner        = $this->scoped->getID();
        $app->description  = $description;
        $app->source_url   = $source_url;
        $app->organization = $organization;
        $app->homepage     = $homepage;
        $app->callback_url = $callback_url;
        $app->type         = $type;

        // Yeah, I dunno why I chose bit flags. I guess so I could
        // copy this value directly to Oauth_application_user
        // access_type which I think does need bit flags -- Z

        if ($access_type == 'r') {
            $app->setAccessFlags(true, false);
        } else {
            $app->setAccessFlags(true, true);
        }

        $app->created = common_sql_now();

        // generate consumer key and secret

        $consumer = Consumer::generateNew();

        $result = $consumer->insert();

        if (!$result) {
            common_log_db_error($consumer, 'INSERT', __FILE__);
            $app->query('ROLLBACK');
            // TRANS: Server error displayed when an application could not be registered in the database through the "New application" form.
            $this->serverError(_('Could not create application.'));
        }

        $app->consumer_key = $consumer->consumer_key;

        $this->app_id = $app->insert();

        if (!$this->app_id) {
            common_log_db_error($app, 'INSERT', __FILE__);
            $app->query('ROLLBACK');
            // TRANS: Server error displayed when an application could not be registered in the database through the "New application" form.
            $this->serverError(_('Could not create application.'));
        }

        try {
            $app->uploadLogo();
        } catch (Exception $e) {
            $app->query('ROLLBACK');
            // TRANS: Form validation error messages displayed when uploading an invalid application logo.
            $this->clientError(_('Invalid image.'));
        }

        $app->query('COMMIT');

        common_redirect(common_local_url('oauthappssettings'), 303);
    }

    /**
     * Does the app name already exist?
     *
     * Checks the DB to see someone has already registered an app
     * with the same name.
     *
     * @param string $name app name to check
     *
     * @return boolean true if the name already exists
     */
    function nameExists($name)
    {
        $app = Oauth_application::getKV('name', $name);
        return !empty($app);
    }
}

// END OF FILE
// ============================================================================
?>