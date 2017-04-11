<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: OAuthAppSettings
 * List the OAuth applications that a user has registered with this instance
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
 * List the OAuth applications that a user has registered with this instance
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Evan Prodromou
 * o Jean Baptiste Favre <statusnet@jbfavre.org>
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
 * Show a user's registered OAuth applications
 */
class OauthappssettingsAction extends SettingsAction
{
    protected $page = null;

    protected function doPreparation()
    {
        $this->page = $this->int('page') ?: 1;
    }

    /**
     * Title of the page
     *
     * @return string Title of the page
     */

    function title()
    {
        // TRANS: Page title for OAuth applications
        return _('OAuth applications');
    }

    /**
     * Instructions for use
     *
     * @return instructions for use
     */

    function getInstructions()
    {
        // TRANS: Page instructions for OAuth applications
        return _('Applications you have registered');
    }

    function showContent()
    {
        $offset = ($this->page - 1) * APPS_PER_PAGE;
        $limit  =  APPS_PER_PAGE + 1;

        $application = new Oauth_application();
        $application->owner = $this->scoped->getID();
        $application->whereAdd("name != 'anonymous'");
        $application->limit($offset, $limit);
        $application->orderBy('created DESC');
        $application->find();

        $cnt = 0;

        if ($application) {
            $al = new ApplicationList($application, $this->scoped, $this);
            $cnt = $al->show();
            if (0 == $cnt) {
                $this->showEmptyListMessage();
            }
        }

        $this->elementStart('p', array('id' => 'application_register'));
        $this->element('a',
            array('href' => common_local_url('newapplication'),
                  'class' => 'more'
            ),
            // TRANS: Link description to add a new OAuth application.
            'Register a new application');
        $this->elementEnd('p');

        $this->pagination(
            $this->page > 1,
            $cnt > APPS_PER_PAGE,
            $this->page,
            'oauthappssettings'
        );
    }

    function showEmptyListMessage()
    {
        // TRANS: Empty list message on page with OAuth applications. Markup allowed
        $message = sprintf(_('You have not registered any applications yet.'));

        $this->elementStart('div', 'guide');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    }
}

// END OF FILE
// ============================================================================
?>