<?php
/* ============================================================================
 * Title: APIListMember
 * API method to check if a user belongs to a list.
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
 * API method to check if a user belongs to a list.
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
class ApiListMemberAction extends ApiBareAuthAction
{
    /**
     * Set the flags for handling the request. Show the profile if this
     * is a GET request AND the profile is a member of the list, add a member
     * if it is a POST, remove the profile from the list if method is DELETE
     * or if method is POST and an argument _method is set to DELETE. Act
     * like we don't know if the current user has no access to the list.
     *
     * Takes parameters:
     *     - user: the user id or nickname
     *     - list_id: the id of the tag or the tag itself
     *     - id: the id of the member being looked for/added/removed
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $this->target = $this->getTargetProfile($this->arg('id'));
        $this->list = $this->getTargetList($this->arg('user'), $this->arg('list_id'));

        if (empty($this->list)) {
            // TRANS: Client error displayed when referring to a non-existing list.
            $this->clientError(_('List not found.'), 404);
        }

        if (!($this->target instanceof Profile)) {
            // TRANS: Client error displayed when referring to a non-existing user.
            $this->clientError(_('No such user.'), 404);
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

        $arr = array('tagger' => $this->list->tagger,
                      'tag' => $this->list->tag,
                      'tagged' => $this->target->id);
        $ptag = Profile_tag::pkeyGet($arr);

        if(empty($ptag)) {
            // TRANS: Client error displayed when referring to a non-list member.
            $this->clientError(_('The specified user is not a member of this list.'));
        }

        $user = $this->twitterUserArray($this->target, true);

        switch($this->format) {
        case 'xml':
            $this->showTwitterXmlUser($user, 'user', true);
            break;
        case 'json':
            $this->showSingleJsonUser($user);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
        return true;
    }
}

// END OF FILE
// ============================================================================
?>