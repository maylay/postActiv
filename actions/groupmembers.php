<?php
/* ============================================================================
 * Title: GroupMembers
 * List of group members
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
 * List of group members
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Meitar Moscovitz <meitarm@gmail.com>
 * o Zach Copley
 * o Sarven Capadisli
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
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
 * List of group members
 */
class GroupmembersAction extends GroupAction
{
    var $page = null;

    function isReadOnly($args)
    {
        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Title of the page showing group members.
            // TRANS: %s is the name of the group.
            return sprintf(_('%s group members'),
                           $this->group->nickname);
        } else {
            // TRANS: Title of the page showing group members.
            // TRANS: %1$s is the name of the group, %2$d is the page number of the members list.
            return sprintf(_('%1$s group members, page %2$d'),
                           $this->group->nickname,
                           $this->page);
        }
    }

    protected function handle()
    {
        parent::handle();
        $this->showPage();
    }

    function showPageNotice()
    {
        $this->element('p', 'instructions',
                       // TRANS: Page notice for group members page.
                       _('A list of the users in this group.'));
    }

    function showContent()
    {
        $offset = ($this->page-1) * PROFILES_PER_PAGE;
        $limit =  PROFILES_PER_PAGE + 1;

        $cnt = 0;

        $members = $this->group->getMembers($offset, $limit);

        if ($members) {
            $member_list = new GroupMemberList($members, $this->group, $this);
            $cnt = $member_list->show();
        }

        $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                          $this->page, 'groupmembers',
                          array('nickname' => $this->group->nickname));
    }
}
?>