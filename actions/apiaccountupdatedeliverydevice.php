<?php
/* ============================================================================
 * Title: APIAccountUpdateDeliveryDevice
 * Update the authenticating user notification channels
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
 * Update the authenticating user notification channels
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Craig Andrews <candrews@integralblue.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brenda Wallace <shiny@cpan.org>
 * o Brion Vibber <brion@pobox.com>
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
 * Sets which channel (device) StatusNet delivers updates to for
 * the authenticating user. Sending none as the device parameter
 * will disable IM and/or SMS updates.
 */
class ApiAccountUpdateDeliveryDeviceAction extends ApiAuthAction
{
    protected $needPost = true;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        $this->user   = $this->auth_user;
        $this->device = $this->trimmed('device');

        return true;
    }

    /**
     * Handle the request
     *
     * See which request params have been set, and update the user settings
     *
     * @return void
     */
    function handle()
    {
        parent::handle();

        if (!in_array($this->format, array('xml', 'json'))) {
            $this->clientError(
                // TRANS: Client error displayed when coming across a non-supported API method.
                _('API method not found.'),
                404,
                $this->format
            );
        }

        // Note: Twitter no longer supports IM

        if (!in_array(strtolower($this->device), array('sms', 'im', 'none'))) {
            // TRANS: Client error displayed when no valid device parameter is provided for a user's delivery device setting.
            $this->clientError(_( 'You must specify a parameter named ' .
                                  '\'device\' with a value of one of: sms, im, none.' ));
        }

        if (empty($this->user)) {
            // TRANS: Client error displayed when no existing user is provided for a user's delivery device setting.
            $this->clientError(_('No such user.'), 404);
        }

        $original = clone($this->user);

        if (strtolower($this->device) == 'sms') {
            $this->user->smsnotify = true;
        } elseif (strtolower($this->device) == 'im') {
            //TODO IM is pluginized now, so what should we do?
            //Enable notifications for all IM plugins?
            //For now, don't do anything
            //$this->user->jabbernotify = true;
        } elseif (strtolower($this->device == 'none')) {
            $this->user->smsnotify    = false;
            //TODO IM is pluginized now, so what should we do?
            //Disable notifications for all IM plugins?
            //For now, don't do anything
            //$this->user->jabbernotify = false;
        }

        $result = $this->user->update($original);

        if ($result === false) {
            common_log_db_error($this->user, 'UPDATE', __FILE__);
            // TRANS: Server error displayed when a user's delivery device cannot be updated.
            $this->serverError(_('Could not update user.'));
        }

        $profile = $this->user->getProfile();

        $twitter_user = $this->twitterUserArray($profile, true);

        // Note: this Twitter API method is retarded because it doesn't give
        // any success/failure information. Twitter's docs claim that the
        // notification field will change to reflect notification choice,
        // but that's not true; notification> is used to indicate
        // whether the auth user is following the user in question.

        if ($this->format == 'xml') {
            $this->initDocument('xml');
            $this->showTwitterXmlUser($twitter_user, 'user', true);
            $this->endDocument('xml');
        } elseif ($this->format == 'json') {
            $this->initDocument('json');
            $this->showJsonObjects($twitter_user);
            $this->endDocument('json');
        }
    }
}

// END OF FILE
// ============================================================================
?>