<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: GroupQueue
 * Queue of people who want to subscribe to a group.
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
 * Queue of people who want to subscribe to a group.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Zach Copley
 * o Evan Prodromou
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

require_once(INSTALLDIR.'/lib/profilelist.php');
require_once INSTALLDIR.'/lib/publicgroupnav.php';

/**
 * List of group members
 */
class GroupqueueAction extends GroupAction
{
    var $page = null;

    function isReadOnly($args)
    {
        return true;
    }

    // @todo FIXME: most of this belongs in a base class, sounds common to most group actions?
    protected function prepare(array $args=array())
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        $nickname_arg = $this->arg('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            if ($this->page != 1) {
                $args['page'] = $this->page;
            }
            common_redirect(common_local_url('groupqueue', $args), 301);
        }

        if (!$nickname) {
            // TRANS: Client error displayed when trying to view group members without providing a group nickname.
            $this->clientError(_('No nickname.'), 404);
        }

        $local = Local_group::getKV('nickname', $nickname);

        if (!$local) {
            // TRANS: Client error displayed when trying to view group members for a non-existing group.
            $this->clientError(_('No such group.'), 404);
        }

        $this->group = User_group::getKV('id', $local->group_id);

        if (!$this->group) {
            // TRANS: Client error displayed when trying to view group members for an object that is not a group.
            $this->clientError(_('No such group.'), 404);
        }

        $cur = common_current_user();
        if (!$cur || !$cur->isAdmin($this->group)) {
            // TRANS: Client error displayed when trying to approve group applicants without being a group administrator.
            $this->clientError(_('Only the group admin may approve users.'));
        }
        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Title of the first page showing pending group members still awaiting approval to join the group.
            // TRANS: %s is the name of the group.
            return sprintf(_('%s group members awaiting approval'),
                           $this->group->nickname);
        } else {
            // TRANS: Title of all but the first page showing pending group members still awaiting approval to join the group.
            // TRANS: %1$s is the name of the group, %2$d is the page number of the members list.
            return sprintf(_('%1$s group members awaiting approval, page %2$d'),
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
                       _('A list of users awaiting approval to join this group.'));
    }

    function showContent()
    {
        $offset = ($this->page-1) * PROFILES_PER_PAGE;
        $limit =  PROFILES_PER_PAGE + 1;

        $cnt = 0;

        $members = $this->group->getRequests($offset, $limit);

        if ($members) {
            // @fixme change!
            $member_list = new GroupQueueList($members, $this->group, $this);
            $cnt = $member_list->show();
        }

        $members->free();

        $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                          $this->page, 'groupqueue',
                          array('nickname' => $this->group->nickname));
    }
}

// @todo FIXME: documentation missing.
class GroupQueueList extends GroupMemberList
{
    function newListItem(Profile $profile)
    {
        return new GroupQueueListItem($profile, $this->group, $this->action);
    }
}

// @todo FIXME: documentation missing.
class GroupQueueListItem extends GroupMemberListItem
{
    function showActions()
    {
        $this->startActions();
        if (Event::handle('StartProfileListItemActionElements', array($this))) {
            $this->showApproveButtons();
            Event::handle('EndProfileListItemActionElements', array($this));
        }
        $this->endActions();
    }

    function showApproveButtons()
    {
        $this->out->elementStart('li', 'entity_approval');
        $form = new ApproveGroupForm($this->out, $this->group, $this->profile);
        $form->show();
        $this->out->elementEnd('li');
    }
}

// END OF FILE
// ============================================================================
?>