<?php
/* ============================================================================
 * Title: APIFriendshipsShow
 * Show information about the relationship between two users
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
 * Show information about the relationship between two users
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Dan Moore <dan@moore.cx>
 * o Evan Prodromou
 * o Zach Copley
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
 * Outputs detailed information about the relationship between two users
 */
class ApiFriendshipsShowAction extends ApiBareAuthAction
{
    var $source = null;
    var $target = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $source_id          = (int)$this->trimmed('source_id');
        $source_screen_name = $this->trimmed('source_screen_name');
        $target_id          = (int)$this->trimmed('target_id');
        $target_screen_name = $this->trimmed('target_screen_name');

        if (!empty($source_id)) {
            $this->source = User::getKV($source_id);
        } elseif (!empty($source_screen_name)) {
            $this->source = User::getKV('nickname', $source_screen_name);
        } else {
            $this->source = $this->auth_user;
        }

        if (!empty($target_id)) {
            $this->target = User::getKV($target_id);
        } elseif (!empty($target_screen_name)) {
            $this->target = User::getKV('nickname', $target_screen_name);
        }

        return true;
    }

    /**
     * Determines whether this API resource requires auth.  Overloaded to look
     * return true in case source_id and source_screen_name are both empty
     *
     * @return boolean true or false
     */
    function requiresAuth()
    {
        if (common_config('site', 'private')) {
            return true;
        }

        $source_id          = $this->trimmed('source_id');
        $source_screen_name = $this->trimmed('source_screen_name');

        if (empty($source_id) && empty($source_screen_name)) {
            return true;
        }

        return false;
    }

    /**
     * Handle the request
     *
     * Check the format and show the user info
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (!in_array($this->format, array('xml', 'json'))) {
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }

        if (empty($this->source)) {
            $this->clientError(
                // TRANS: Client error displayed when a source user could not be determined showing friendship.
                _('Could not determine source user.'),
                404
             );
        }

        if (empty($this->target)) {
            $this->clientError(
                // TRANS: Client error displayed when a target user could not be determined showing friendship.
                _('Could not find target user.'),
                404
            );
        }

        $result = $this->twitterRelationshipArray($this->source, $this->target);

        switch ($this->format) {
        case 'xml':
            $this->initDocument('xml');
            $this->showTwitterXmlRelationship($result[relationship]);
            $this->endDocument('xml');
            break;
        case 'json':
            $this->initDocument('json');
            print json_encode($result);
            $this->endDocument('json');
            break;
        default:
            break;
        }
    }

    /**
     * Return true if read only.
     *
     * MAY override
     *
     * @param array $args other arguments
     *
     * @return boolean is read only action?
     */

    function isReadOnly($args)
    {
        return true;
    }
}

// END OF FILE
// ============================================================================
?>