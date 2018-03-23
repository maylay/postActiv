<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: SiteNoticeAdminPanel
 * Site notice administration panel
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
 * Site notice administration panel
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
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
 * Update the site-wide notice text
 */
class SitenoticeadminpanelAction extends AdminPanelAction
{
    /**
     * Returns the page title
     *
     * @return string page title
     */
    function title()
    {
        // TRANS: Page title for site-wide notice tab in admin panel.
        return _('Site Notice');
    }

    /**
     * Instructions for using this form.
     *
     * @return string instructions
     */
    function getInstructions()
    {
        // TRANS: Instructions for site-wide notice tab in admin panel.
        return _('Edit site-wide message');
    }

    /**
     * Show the site notice admin panel form
     *
     * @return void
     */
    function showForm()
    {
        $form = new SiteNoticeAdminPanelForm($this);
        $form->show();
        return;
    }

    /**
     * Save settings from the form
     *
     * @return void
     */
    function saveSettings()
    {
        $siteNotice = $this->trimmed('site-notice');

        // assert(all values are valid);
        // This throws an exception on validation errors

        $this->validate($siteNotice);

        $config = new Config();

        $result = Config::save('site', 'notice', $siteNotice);

        if (!$result) {
            // TRANS: Server error displayed when saving a site-wide notice was impossible.
            $this->ServerError(_('Unable to save site notice.'));
        }
    }

    function validate(&$siteNotice)
    {
        // Validate notice text

        if (mb_strlen($siteNotice) > 255)  {
            $this->clientError(
                // TRANS: Client error displayed when a site-wide notice was longer than allowed.
                _('Maximum length for the site-wide notice is 255 characters.')
            );
        }

        // scrub HTML input
        $siteNotice = common_purify($siteNotice);
    }
}

class SiteNoticeAdminPanelForm extends AdminForm
{
    /**
     * ID of the form
     *
     * @return int ID of the form
     */

    function id()
    {
        return 'form_site_notice_admin_panel';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */

    function formClass()
    {
        return 'form_settings';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */

    function action()
    {
        return common_local_url('sitenoticeadminpanel');
    }

    /**
     * Data elements of the form
     *
     * @return void
     */

    function formData()
    {
        $this->out->elementStart('ul', 'form_data');

        $this->out->elementStart('li');
        $this->out->textarea(
            'site-notice',
            // TRANS: Label for site-wide notice text field in admin panel.
            _('Site notice text'),
            common_config('site', 'notice'),
            // TRANS: Tooltip for site-wide notice text field in admin panel.
            _('Site-wide notice text (255 characters maximum; HTML allowed)')
        );
        $this->out->elementEnd('li');

        $this->out->elementEnd('ul');
    }

    /**
     * Action elements
     *
     * @return void
     */

    function formActions()
    {
        $this->out->submit(
            'submit',
            // TRANS: Button text for saving site notice in admin panel.
            _m('BUTTON','Save'),
            'submit',
            null,
            // TRANS: Button title to save site notice in admin panel.
            _('Save site notice.')
        );
    }
}

// END OF FILE
// ============================================================================
?>