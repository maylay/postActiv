<?php
/* ============================================================================
 * Title: APIUserFriends
 * Show a user's friends (subscriptions)
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
 * Show a user's friends (subscriptions)
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Evan Prodromou
 * o Robin Millette <robin@millette.info>
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
 * Ouputs the authenticating user's friends (subscriptions), each with
 * current Twitter-style status inline.  They are ordered by the date
 * in which the user subscribed to them, 100 at a time.
 */
class ApiUserFriendsAction extends ApiSubscriptionsAction
{
    /**
     * Get the user's subscriptions (friends) as an array of profiles
     *
     * @return array Profiles
     */
    protected function getProfiles()
    {
        $offset = ($this->page - 1) * $this->count;
        $limit =  $this->count + 1;

        $subs = null;

        if (isset($this->tag)) {
            $subs = $this->target->getTaggedSubscriptions(
                $this->tag, $offset, $limit
            );
        } else {
            $subs = $this->target->getSubscribed(
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

// END OF FILE
// ============================================================================
?>