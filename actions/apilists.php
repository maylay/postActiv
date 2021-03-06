<?php
/* ============================================================================
 * Title: APILists
 * List existing lists or create a new list.
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
 * List existing lists or create a new list.
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


/**
 * Action handler for Twitter list_memeber methods
 */
class ApiListsAction extends ApiBareAuthAction
{
    var $lists   = null;
    var $cursor = 0;
    var $next_cursor = 0;
    var $prev_cursor = 0;
    var $create = false;

    /**
     * Set the flags for handling the request. List lists created by user if this
     * is a GET request, create a new list if it is a POST request.
     *
     * Takes parameters:
     *     - user: the user id or nickname
     * Parameters for POST request
     *     - name: name of the new list (the people tag itself)
     *     - mode: (optional) mode for the new list private/public
     *     - description: (optional) description for the list
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $this->create = ($_SERVER['REQUEST_METHOD'] == 'POST');

        if (!$this->create) {

            $this->user = $this->getTargetUser($this->arg('user'));

            if (!($user instanceof User)) {
                // TRANS: Client error displayed trying to perform an action related to a non-existing user.
                $this->clientError(_('No such user.'), 404);
            }
            $this->target = $user->getProfile();
            $this->getLists();
        }

        return true;
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
     * Handle request:
     *     Show the lists the user has created if the request method is GET
     *     Create a new list by diferring to handlePost() if it is POST.
     */
    protected function handle()
    {
        parent::handle();

        if($this->create) {
            return $this->handlePost();
        }

        switch($this->format) {
        case 'xml':
            $this->showXmlLists($this->lists, $this->next_cursor, $this->prev_cursor);
            break;
        case 'json':
            $this->showJsonLists($this->lists, $this->next_cursor, $this->prev_cursor);
            break;
        default:
            $this->clientError(
                // TRANS: Client error displayed when coming across a non-supported API method.
                _('API method not found.'),
                404,
                $this->format
            );
            break;
        }
    }

    /**
     * Create a new list
     *
     * @return boolean success
     */
    function handlePost()
    {
        $name=$this->arg('name');
        if(empty($name)) {
            // mimick twitter
            // TRANS: Client error displayed when trying to create a list without a name.
            print _("A list must have a name.");
            exit(1);
        }

        // twitter creates a new list by appending a number to the end
        // if the list by the given name already exists
        // it makes more sense to return the existing list instead

        $private = null;
        if ($this->arg('mode') === 'public') {
            $private = false;
        } else if ($this->arg('mode') === 'private') {
            $private = true;
        }

        $list = Profile_list::ensureTag($this->auth_user->id,
                                        $this->arg('name'),
                                        $this->arg('description'),
                                        $private);
        if (empty($list)) {
            return false;
        }

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlList($list);
            break;
        case 'json':
            $this->showSingleJsonList($list);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
        return true;
    }

    /**
     * Get lists
     */
    function getLists()
    {
        $cursor = (int) $this->arg('cursor', -1);

        // twitter fixes count at 20
        // there is no argument named count
        $count = 20;
        $fn = array($this->target, 'getLists');

        list($this->lists,
             $this->next_cursor,
             $this->prev_cursor) = Profile_list::getAtCursor($fn, array($this->scoped), $cursor, $count);
    }

    function isReadOnly($args)
    {
        return false;
    }

    function lastModified()
    {
        if (!$this->create && !empty($this->lists) && (count($this->lists) > 0)) {
            return strtotime($this->lists[0]->created);
        }

        return null;
    }

    /**
     * An entity tag for this list of lists
     *
     * Returns an Etag based on the action name, language, user ID and
     * timestamps of the first and last list the user has joined
     *
     * @return string etag
     */
    function etag()
    {
        if (!$this->create && !empty($this->lists) && (count($this->lists) > 0)) {

            $last = count($this->lists) - 1;

            return '"' . implode(
                ':',
                array($this->arg('action'),
                      common_language(),
                      $this->target->id,
                      strtotime($this->lists[0]->created),
                      strtotime($this->lists[$last]->created))
            )
            . '"';
        }

        return null;
    }
}

// END OF FILE
// ============================================================================
?>