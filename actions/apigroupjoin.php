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
 * Join a group via the API
 *
 * @category  API
 * @package   postActiv
 * @author    Zach Copley
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Eric Helgeson <erichelgeson@gmail.com>
 * @author    Craig Andrews <candrews@integralblue.com>
 * @author    Michele Azzolari <macno@macno.org>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Joins the authenticated user to the group specified by ID
 */
class ApiGroupJoinAction extends ApiAuthAction
{
    protected $needPost = true;

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

        $this->group = $this->getTargetGroup($this->arg('id'));

        return true;
    }

    /**
     * Handle the request
     *
     * Join the authenticated user to the group
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (empty($this->scoped)) {
            // TRANS: Client error displayed when trying to have a non-existing user join a group.
            $this->clientError(_('No such user.'), 404);
        }

        if (empty($this->group)) {
            // TRANS: Client error displayed when trying to join a group that does not exist.
            $this->clientError(_('Group not found.'), 404);
        }

        if ($this->scoped->isMember($this->group)) {
            // TRANS: Server error displayed when trying to join a group the user is already a member of.
            $this->clientError(_('You are already a member of that group.'), 403);
        }

        if (Group_block::isBlocked($this->group, $this->scoped)) {
            // TRANS: Server error displayed when trying to join a group the user is blocked from joining.
            $this->clientError(_('You have been blocked from that group by the admin.'), 403);
        }

        try {
            $this->scoped->joinGroup($this->group);
        } catch (Exception $e) {
            // TRANS: Server error displayed when joining a group failed in the database.
            // TRANS: %1$s is the joining user's nickname, $2$s is the group nickname for which the join failed.
            $this->serverError(sprintf(_('Could not join user %1$s to group %2$s.'),
                                       $this->scoped->nickname, $this->group->nickname));
        }

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlGroup($this->group);
            break;
        case 'json':
            $this->showSingleJsonGroup($this->group);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }
}
?>