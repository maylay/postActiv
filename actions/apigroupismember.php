<?php
/* ============================================================================
 * Title: APIGroupIsMember
 * Returns whether a user is a member of a specified group.
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
 * Returns whether a user is a member of a specified group.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Craig Andrews <candrews@integralblue.com>
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
 * Returns whether a user is a member of a specified group.
 */
class ApiGroupIsMemberAction extends ApiBareAuthAction
{
    var $group   = null;

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

        $this->target = $this->getTargetProfile(null);
        $this->group  = $this->getTargetGroup(null);

        return true;
    }

    /**
     * Handle the request
     *
     * Save the new message
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (empty($this->target)) {
            // TRANS: Client error displayed when checking group membership for a non-existing user.
            $this->clientError(_('No such user.'), 404);
        }

        if (empty($this->group)) {
            // TRANS: Client error displayed when checking group membership for a non-existing group.
            $this->clientError(_('Group not found.'), 404);
        }

        $is_member = $this->target->isMember($this->group);

        switch($this->format) {
        case 'xml':
            $this->initDocument('xml');
            $this->element('is_member', null, $is_member);
            $this->endDocument('xml');
            break;
        case 'json':
            $this->initDocument('json');
            $this->showJsonObjects(array('is_member' => $is_member));
            $this->endDocument('json');
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
}

// END OF FILE
// ============================================================================
?>