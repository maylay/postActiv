<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Show a user's followers (subscribers)
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  API
 * @package   postActiv
 * @author    Dan Moore <dan@moore.cx>
 * @author    Evan Prodromou <evan@status.net>
 * @author    Zach Copley <zach@status.net>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2009-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Ouputs the authenticating user's followers (subscribers), each with
 * current Twitter-style status inline.  They are ordered by the order
 * in which they subscribed to the user, 100 at a time.
 *
 * @category API
 * @package  StatusNet
 * @author   Dan Moore <dan@moore.cx>
 * @author   Evan Prodromou <evan@status.net>
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class ApiUserFollowersAction extends ApiSubscriptionsAction
{
    /**
     * Get the user's subscribers (followers) as an array of profiles
     *
     * @return array Profiles
     */
    protected function getProfiles()
    {
        $offset = ($this->page - 1) * $this->count;
        $limit =  $this->count + 1;

        $subs = null;

        if (isset($this->tag)) {
            $subs = $this->target->getTaggedSubscribers(
                $this->tag, $offset, $limit
            );
        } else {
            $subs = $this->target->getSubscribers(
                $offset,
                $limit
            );
        }

        $profiles = array();

        while ($subs->fetch()) {
            $profiles[] = clone($subs);
        }

        return $profiles;
    }
}
?>