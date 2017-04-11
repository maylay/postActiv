<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: oAuthConnectionSettings
 * List a user's OAuth connected applications
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
 * List a user's OAuth connected applications
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
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
 * Show connected OAuth applications
 */
class OauthconnectionssettingsAction extends SettingsAction
{
    var $page = null;

    protected $oauth_token = null;

    protected function doPreparation()
    {
        $this->oauth_token = $this->arg('oauth_token');
        $this->page = $this->int('page') ?: 1;
    }

    /**
     * Title of the page
     *
     * @return string Title of the page
     */
    function title()
    {
        // TRANS: Title for OAuth connection settings.
        return _('Connected applications');
    }

    /**
     * Instructions for use
     *
     * @return instructions for use
     */
    function getInstructions()
    {
        // TRANS: Instructions for OAuth connection settings.
        return _('The following connections exist for your account.');
    }

    /**
     * Content area of the page
     *
     * @return void
     */

    function showContent()
    {
        $offset = ($this->page - 1) * APPS_PER_PAGE;
        $limit  =  APPS_PER_PAGE + 1;

        $connection = $this->scoped->getConnectedApps($offset, $limit);

        $cnt = 0;

        if (!empty($connection)) {
            $cal = new ConnectedAppsList($connection, $this->scoped, $this);
            $cnt = $cal->show();
        }

        if ($cnt == 0) {
            $this->showEmptyListMessage();
        }

        $this->pagination(
            $this->page > 1,
            $cnt > APPS_PER_PAGE,
            $this->page,
            'connectionssettings',
            array('nickname' => $this->scoped->getNickname())
        );
    }

    /**
     * Handle posts to this form
     *
     * Based on the button that was pressed, muxes out to other functions
     * to do the actual task requested.
     *
     * All sub-functions reload the form with a message -- success or failure.
     *
     * @return void
     */
    protected function doPost()
    {
        if ($this->arg('revoke')) {
            return $this->revokeAccess($this->oauth_token);
        }

        // TRANS: Client error when submitting a form with unexpected information.
        throw new ClientException(_('Unexpected form submission.'), 401);
    }

    /**
     * Revoke an access token
     *
     * XXX: Confirm revoke before doing it
     *
     * @param int $appId the ID of the application
     *
     */
    function revokeAccess($token)
    {
        $cur = common_current_user();

        $appUser = Oauth_application_user::getByUserAndToken($cur, $token);

        if (empty($appUser)) {
            // TRANS: Client error when trying to revoke access for an application while not being a user of it.
            $this->clientError(_('You are not a user of that application.'), 401);
        }

        $app = Oauth_application::getKV('id', $appUser->application_id);

        $datastore = new ApiGNUsocialOAuthDataStore();
        $datastore->revoke_token($appUser->token, 1);

        $result = $appUser->delete();

        if (!$result) {
            common_log_db_error($orig, 'DELETE', __FILE__);
            // TRANS: Client error when revoking access has failed for some reason.
            // TRANS: %s is the application ID revoking access failed for.
            $this->clientError(sprintf(_('Unable to revoke access for application: %s.'), $app->id));
        }

        $msg = 'API OAuth - user %s (id: %d) revoked access token %s for app id %d';
        common_log(
            LOG_INFO,
            sprintf(
                $msg,
                $cur->nickname,
                $cur->id,
                $appUser->token,
                $appUser->application_id
            )
        );

        $msg = sprintf(
            // TRANS: Success message after revoking access for an application.
            // TRANS: %1$s is the application name, %2$s is the first part of the user token.
            _('You have successfully revoked access for %1$s and the access token starting with %2$s.'),
             $app->name,
             substr($appUser->token, 0, 7)
        );

        $this->showForm($msg, true);
    }

    function showEmptyListMessage()
    {
        // TRANS: Empty list message when no applications have been authorised yet.
        $message = _('You have not authorized any applications to use your account.');

        $this->elementStart('div', 'guide');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    }

    function showSections()
    {
        $cur = common_current_user();

        $this->elementStart('div', array('id' => 'developer-help', 'class' => 'section'));

        $this->element('h2', null, 'Developers');
        $this->elementStart('p');

        $devMsg = sprintf(
            // TRANS: Note for developers in the OAuth connection settings form.
            // TRANS: This message contains a Markdown link. Do not separate "](".
            // TRANS: %s is the URL to the OAuth settings.
            _('Are you a developer? [Register an OAuth client application](%s) to use with this instance of StatusNet.'),
            common_local_url('oauthappssettings')
        );

        $output = common_markup_to_html($devMsg);

        $this->raw($output);
        $this->elementEnd('p');

        $this->elementEnd('section');
    }
}

// END OF FILE
// ==============================================================================
?>