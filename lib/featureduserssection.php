<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Section for featured users
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
 * @category  Widget
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

/**
 * Section for featured users
 *
 * @category Widget
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class FeaturedUsersSection extends ProfileSection
{
    function show()
    {
        $featured_nicks = common_config('nickname', 'featured');
        if (empty($featured_nicks)) {
            return;
        }
        parent::show();
    }

    function getProfiles()
    {
        $featured_nicks = common_config('nickname', 'featured');

        if (!$featured_nicks) {
            return null;
        }

        $quoted = array();

        foreach ($featured_nicks as $nick) {
            $quoted[] = "'$nick'";
        }

        $table = "user";
        if(common_config('db','quote_identifiers')) {
          $table = '"' . $table . '"';
        }

        $qry = 'SELECT profile.* ' .
            'FROM profile JOIN '. $table .' on profile.id = '. $table .'.id ' .
          'WHERE '. $table .'.nickname in (' . implode(',', $quoted) . ') ' .
          'ORDER BY profile.created DESC ';

        $limit = PROFILES_PER_SECTION + 1;
        $offset = 0;

        if (common_config('db','type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $profile = Memcached_DataObject::cachedQuery('Profile',
                                                     $qry,
                                                     6 * 3600);
        return $profile;
    }

    function title()
    {
        // TRANS: Title for featured users section.
        return _('Featured users');
    }

    function divId()
    {
        return 'featured_users';
    }

    function moreUrl()
    {
        return common_local_url('featured');
    }
}
