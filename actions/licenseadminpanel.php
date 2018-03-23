<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: LicenseAdminPanel
 * License administration panel
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
 * License administration panel
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
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
 * License settings
 */
class LicenseadminpanelAction extends AdminPanelAction
{
    /**
     * Returns the page title
     *
     * @return string page title
     */

    function title()
    {
        // TRANS: User admin panel title
        return _m('TITLE', 'License');
    }

    /**
     * Instructions for using this form.
     *
     * @return string instructions
     */
    function getInstructions()
    {
        // TRANS: Form instructions for the site license admin panel.
        return _('License for this StatusNet site');
    }

    /**
     * Show the site admin panel form
     *
     * @return void
     */
    function showForm()
    {
        $form = new LicenseAdminPanelForm($this);
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
        static $settings = array(
            'license' => array('type', 'owner', 'url', 'title', 'image')
        );

        $values = array();

        foreach ($settings as $section => $parts) {
            foreach ($parts as $setting) {
                $values[$section][$setting] = $this->trimmed($setting);
            }
        }

        // This throws an exception on validation errors

        $this->validate($values);

        // assert(all values are valid);

        $config = new Config();

        $config->query('BEGIN');

        foreach ($settings as $section => $parts) {
            foreach ($parts as $setting) {
                Config::save($section, $setting, $values[$section][$setting]);
            }
        }

        $config->query('COMMIT');

        return;
    }

    /**
     * Validate License admin form values
     *
     * @param array &$values from the form
     *
     * @return nothing
     */
    function validate(&$values)
    {
        // Validate license type (shouldn't have to do it, but just in case)

        $types = array('private', 'allrightsreserved', 'cc');

        if (!in_array($values['license']['type'], $types)) {
            // TRANS: Client error displayed selecting an invalid license in the license admin panel.
            $this->clientError(_('Invalid license selection.'));
        }

        // Make sure the user has set an owner if the site has a private
        // license

        if ($values['license']['type'] == 'allrightsreserved'
            && empty($values['license']['owner'])
        ) {
            $this->clientError(
                // TRANS: Client error displayed when not specifying an owner for the all rights reserved license in the license admin panel.
                _('You must specify the owner of the content when using the All Rights Reserved license.')
            );
        }

        // Make sure the license title is not too long
        if (mb_strlen($values['license']['type']) > 255) {
            $this->clientError(
                // TRANS: Client error displayed selecting a too long license title in the license admin panel.
                _('Invalid license title. Maximum length is 255 characters.')
            );
        }

        // URLs should be set for cc license

        if ($values['license']['type'] == 'cc') {
            if (!common_valid_http_url($values['license']['url'])) {
                // TRANS: Client error displayed specifying an invalid license URL in the license admin panel.
                $this->clientError(_('Invalid license URL.'));
            }
            if (!common_valid_http_url($values['license']['image'])) {
                // TRANS: Client error displayed specifying an invalid license image URL in the license admin panel.
                $this->clientError(_('Invalid license image URL.'));
            }
        }

        // can be either blank or a valid URL for private & allrightsreserved

        if (!empty($values['license']['url'])) {
            if (!common_valid_http_url($values['license']['url'])) {
                // TRANS: Client error displayed specifying an invalid license URL in the license admin panel.
                $this->clientError(_('License URL must be blank or a valid URL.'));
            }
        }

        // can be either blank or a valid URL for private & allrightsreserved

        if (!empty($values['license']['image'])) {
            if (!common_valid_http_url($values['license']['image'])) {
                // TRANS: Client error displayed specifying an invalid license image URL in the license admin panel.
                $this->clientError(_('License image must be blank or valid URL.'));
            }
        }
    }
}

class LicenseAdminPanelForm extends AdminForm
{
    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'licenseadminpanel';
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
        return common_local_url('licenseadminpanel');
    }

    /**
     * Data elements of the form
     *
     * @return void
     */

    function formData()
    {
        $this->out->elementStart(
            'fieldset', array('id' => 'settings_license-selection')
        );
        // TRANS: Form legend in the license admin panel.
        $this->out->element('legend', null, _('License selection'));
        $this->out->elementStart('ul', 'form_data');

        $this->li();

        $types = array(
            // TRANS: License option in the license admin panel.
            'private' => _('Private'),
            // TRANS: License option in the license admin panel.
            'allrightsreserved' => _('All Rights Reserved'),
            // TRANS: License option in the license admin panel.
            'cc' => _('Creative Commons')
        );

        $this->out->dropdown(
            'type',
            // TRANS: Dropdown field label in the license admin panel.
            _('Type'),
            $types,
            // TRANS: Dropdown field instructions in the license admin panel.
            _('Select a license.'),
            false,
            $this->value('type', 'license')
        );

        $this->unli();

        $this->out->elementEnd('ul');
        $this->out->elementEnd('fieldset');

        $this->out->elementStart(
            'fieldset',
            array('id' => 'settings_license-details')
        );
        // TRANS: Form legend in the license admin panel.
        $this->out->element('legend', null, _('License details'));
        $this->out->elementStart('ul', 'form_data');

        $this->li();
        $this->input(
            'owner',
            // TRANS: Field label in the license admin panel.
            _('Owner'),
            // TRANS: Field title in the license admin panel.
            _('Name of the owner of the site\'s content (if applicable).'),
            'license'
        );
        $this->unli();

        $this->li();
        $this->input(
            'title',
            // TRANS: Field label in the license admin panel.
            _('License Title'),
            // TRANS: Field title in the license admin panel.
            _('The title of the license.'),
            'license'
        );
        $this->unli();

        $this->li();
        $this->input(
            'url',
            // TRANS: Field label in the license admin panel.
            _('License URL'),
            // TRANS: Field title in the license admin panel.
            _('URL for more information about the license.'),
            'license'
        );
        $this->unli();

        $this->li();
        $this->input(
            // TRANS: Field label in the license admin panel.
            'image', _('License Image URL'),
            // TRANS: Field title in the license admin panel.
            _('URL for an image to display with the license.'),
            'license'
        );
        $this->unli();

        $this->out->elementEnd('ul');
        $this->out->elementEnd('fieldset');
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
            // TRANS: Button text in the license admin panel.
            _m('BUTTON','Save'),
            'submit',
            null,
            // TRANS: Button title in the license admin panel.
            _('Save license settings.')
        );
    }
}

// END OF FILE
// ============================================================================
?>