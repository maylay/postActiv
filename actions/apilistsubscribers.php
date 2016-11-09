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
 * Show/add/remove list subscribers.
 *
 * @category  API
 * @package   postActiv
 * @author    Sashi Gowda <connect2shashi@gmail.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2011-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================ 
 */

if (!defined('POSTACTIV')) { exit(1); }

require_once INSTALLDIR . '/lib/apilistusers.php';

class ApiListSubscribersAction extends ApiListUsersAction
{
    /**
     * Subscribe to list
     *
     * @return boolean success
     */
    function handlePost()
    {
        $result = Profile_tag_subscription::add($this->list,
                            $this->auth_user);

        if(empty($result)) {
            // TRANS: Client error displayed when an unknown error occurs in the list subscribers action.
            $this->clientError(_('An error occured.'), 500);
        }

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlList($this->list);
            break;
        case 'json':
            $this->showSingleJsonList($this->list);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }

    function handleDelete()
    {
        $args = array('profile_tag_id' => $this->list->id,
                      'profile_id' => $this->auth_user->id);
        $ptag = Profile_tag_subscription::pkeyGet($args);

        if(empty($ptag)) {
            // TRANS: Client error displayed when trying to unsubscribe from a non-subscribed list.
            $this->clientError(_('You are not subscribed to this list.'));
        }

        $result = Profile_tag_subscription::remove($this->list, $this->auth_user);

        if (empty($result)) {
            // TRANS: Client error displayed when an unknown error occurs unsubscribing from a list.
            $this->clientError(_('An error occured.'), 500);
        }

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlList($this->list);
            break;
        case 'json':
            $this->showSingleJsonList($this->list);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
        return true;
    }

    function getUsers()
    {
        $fn = array($this->list, 'getSubscribers');
        list($this->users, $this->next_cursor, $this->prev_cursor) =
            Profile_list::getAtCursor($fn, array(), $this->cursor, 20);
    }
}
?>