<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 *
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
 * PHP version 5
 *
 * List of group members
 *
 * @category  Group
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Meitar Moscovitz <meitarm@gmail.com>
 * @author    Zach Copley <zach@copley.name>
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Brion Vibber <brion@pobox.com>
 * @auhtor    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * List of group members
 *
 * @category Group
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
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