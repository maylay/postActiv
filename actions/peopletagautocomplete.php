<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Peopletag autocomple action.
 *
 * @category  Action
 * @package   postActiv
 * @author    Shashi Gowda <connect2shashi@gmail.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2011-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================ 
 */

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
?>