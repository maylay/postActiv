<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Groups with the most posts section
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
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Groups with the most posts section
 */
class GroupsByPostsSection extends GroupSection
{
    function getGroups()
    {
        $qry = 'SELECT user_group.*, count(*) as value ' .
          'FROM user_group JOIN group_inbox '.
          'ON user_group.id = group_inbox.group_id ' .
          'GROUP BY user_group.id,user_group.nickname,user_group.fullname,user_group.homepage,user_group.description,user_group.location,user_group.original_logo,user_group.homepage_logo,user_group.stream_logo,user_group.mini_logo,user_group.created,user_group.modified ' .
          'ORDER BY value DESC ';

        $limit = GROUPS_PER_SECTION;
        $offset = 0;

        if (common_config('db','type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $group = Memcached_DataObject::cachedQuery('User_group',
                                                   $qry,
                                                   3600);
        return $group;
    }

    function title()
    {
        // TRANS: Title for groups with the most posts section.
        return _('Active groups');
    }

    function divId()
    {
        return 'top_groups_by_post';
    }
}
?>