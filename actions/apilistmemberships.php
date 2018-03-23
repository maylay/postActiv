<?php
/* ============================================================================
 * Title: APIListMemberships
 * Get a list of lists a user belongs to.
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
 * Get a list of lists a user belongs to. (people tags for a user)
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
 * Action handler for API method to list lists a user belongs to.
 * (people tags for a user)
 */
class ApiListMembershipsAction extends ApiBareAuthAction
{
    var $lists = array();
    var $cursor = -1;
    var $next_cursor = 0;
    var $prev_cursor = 0;

    /**
     * Prepare for running the action
     * Take arguments for running:s
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     *
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $this->cursor = (int) $this->arg('cursor', -1);
        $user = $this->getTargetUser($this->arg('user'));

        if (!($user instanceof User)) {
            // TRANS: Client error displayed trying to perform an action related to a non-existing user.
            $this->clientError(_('No such user.'), 404);
        }
        $this->target = $user->getProfile();

        $this->getLists();

        return true;
    }

    /**
     * Handle the request
     *
     * Show the lists
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        switch($this->format) {
        case 'xml':
            $this->showXmlLists($this->lists, $this->next_cursor, $this->prev_cursor);
            break;
        case 'json':
            $this->showJsonLists($this->lists, $this->next_cursor, $this->prev_cursor);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'));
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

    function getLists()
    {
        $fn = array($this->target, 'getOtherTags');

        # 20 lists
        list($this->lists, $this->next_cursor, $this->prev_cursor) =
                Profile_list::getAtCursor($fn, array($this->scoped), $this->cursor, 20);
    }
}

// END OF FILE
// ============================================================================
?>