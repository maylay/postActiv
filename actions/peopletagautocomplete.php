<?php
/* ============================================================================
 * Title: PeopleTagAutoComplete
 * Peopletag autocomple action.
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
 * Peopletag autocomple action.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Shashi Gowda <connect2shashi@gmail.com>
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

class PeopletagautocompleteAction extends Action
{
    var $user;
    var $tags;
    var $last_mod;

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

        // Only for logged-in users

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
            $this->clientError(_('Not logged in.'));
        }

        // CSRF protection

        $token = $this->trimmed('token');

        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token.'.
                                 ' Try again, please.'));
        }

        $profile = $this->user->getProfile();
        $tags = $profile->getLists($this->scoped);

        $this->tags = array();
        while ($tags->fetch()) {

            if (empty($this->last_mod)) {
                $this->last_mod = $tags->modified;
            }

            $arr = array();
            $arr['tag'] = $tags->tag;
            $arr['mode'] = $tags->private ? 'private' : 'public';
            // $arr['url'] = $tags->homeUrl();
            $arr['freq'] = $tags->taggedCount();

            $this->tags[] = $arr;
        }

        $tags = NULL;

        return true;
    }

    /**
     * Last modified time
     *
     * Helps in browser-caching
     *
     * @return String time
     */
    function lastModified()
    {
        return strtotime($this->last_mod);
    }

    /**
     * Handle request
     *
     * Print the JSON autocomplete data
     *
     * @return void
     */
    function handle()
    {
        //common_log(LOG_DEBUG, 'Autocomplete data: ' . json_encode($this->tags));
        if ($this->tags) {
            print(json_encode($this->tags));
            exit(0);
        }
        return false;
    }
}

// END OF FILE
// ============================================================================
?>