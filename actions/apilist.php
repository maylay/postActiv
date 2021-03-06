<?php
/* ============================================================================
 * Title: APIList
 * Show, update or delete a list.
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
 * Show, update or delete a list.
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


class ApiListAction extends ApiBareAuthAction
{
    /**
     * The list in question in the current request
     */
    var $list   = null;

    /**
     * Is this an update request?
     */
    var $update = false;

    /**
     * Is this a delete request?
     */
    var $delete = false;

    /**
     * Set the flags for handling the request. Show list if this is a GET
     * request, update it if it is POST, delete list if method is DELETE
     * or if method is POST and an argument _method is set to DELETE. Act
     * like we don't know if the current user has no access to the list.
     *
     * Takes parameters:
     *     - user: the user id or nickname
     *     - id:   the id of the tag or the tag itself
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $this->delete = ($_SERVER['REQUEST_METHOD'] == 'DELETE' ||
                            ($this->trimmed('_method') == 'DELETE' &&
                             $_SERVER['REQUEST_METHOD'] == 'POST'));

        // update list if method is POST or PUT and $this->delete is not true
        $this->update = (!$this->delete &&
                         in_array($_SERVER['REQUEST_METHOD'], array('POST', 'PUT')));

        $this->user = $this->getTargetUser($this->arg('user'));
        $this->list = $this->getTargetList($this->arg('user'), $this->arg('id'));

        if (empty($this->list)) {
            // TRANS: Client error displayed when referring to a non-existing list.
            $this->clientError(_('List not found.'), 404);
        }

        return true;
    }

    /**
     * Handle the request
     *
     * @return boolean success flag
     */
    protected function handle()
    {
        parent::handle();

        if($this->delete) {
            $this->handleDelete();
            return true;
        }

        if($this->update) {
            $this->handlePut();
            return true;
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

    /**
     * require authentication if it is a write action or user is ambiguous
     *
     */
    function requiresAuth()
    {
        return parent::requiresAuth() ||
            $this->create || $this->delete;
    }

    /**
     * Update a list
     *
     * @return boolean success
     */
    function handlePut()
    {
        if($this->auth_user->id != $this->list->tagger) {
            // TRANS: Client error displayed when trying to update another user's list.
            $this->clientError(_('You cannot update lists that do not belong to you.'), 401);
        }

        $new_list = clone($this->list);
        $new_list->tag = common_canonical_tag($this->arg('name'));
        $new_list->description = common_canonical_tag($this->arg('description'));
        $new_list->private = ($this->arg('mode') === 'private') ? true : false;

        $result = $new_list->update($this->list);

        if(!$result) {
            // TRANS: Client error displayed when an unknown error occurs updating a list.
            $this->clientError(_('An error occured.'), 503);
        }

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlList($new_list);
            break;
        case 'json':
            $this->showSingleJsonList($new_list);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }

    /**
     * Delete a list
     *
     * @return boolean success
     */
    function handleDelete()
    {
        if($this->auth_user->id != $this->list->tagger) {
            // TRANS: Client error displayed when trying to delete another user's list.
            $this->clientError(_('You cannot delete lists that do not belong to you.'), 401);
        }

        $record = clone($this->list);
        $this->list->delete();

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlList($record);
            break;
        case 'json':
            $this->showSingleJsonList($record);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }

    /**
     * Indicate that this resource is not read-only.
     *
     * @return boolean is_read-only=false
     */
    function isReadOnly($args)
    {
        return false;
    }

    /**
     * When was the list (people tag) last updated?
     *
     * @return String time_last_modified
     */
    function lastModified()
    {
        if(!empty($this->list)) {
            return strtotime($this->list->modified);
        }
        return null;
    }

    /**
     * An entity tag for this list
     *
     * Returns an Etag based on the action name, language, user ID and
     * timestamps of the first and last list the user has joined
     *
     * @return string etag
     */
    function etag()
    {
        if (!empty($this->list)) {

            return '"' . implode(
                ':',
                array($this->arg('action'),
                      common_language(),
                      $this->user->id,
                      strtotime($this->list->created),
                      strtotime($this->list->modified))
            )
            . '"';
        }

        return null;
    }
}

// END OF LIST
// ============================================================================
?>