<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Base class for group actions
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
 * @category  Action
 * @package   StatusNet
 * @author    Zach Copley <zach@status.net>
 * @copyright 2009-2011 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

define('MEMBERS_PER_SECTION', 27);

/**
 * Base class for group actions, similar to ProfileAction
 *
 * @category Action
 * @package  StatusNet
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class GroupAction extends ShowstreamAction
{
    protected $group;

    protected function doPreparation()
    {
        $nickname_arg = $this->arg('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg !== $nickname) {
            $args = array('nickname' => $nickname);
            if ($this->page != 1) {
                $args['page'] = $this->page;
            }
            common_redirect(common_local_url($this->getActionName(), $args), 301);
        }

        if (!$nickname) {
            // TRANS: Client error displayed if no nickname argument was given requesting a group page.
            $this->clientError(_('No nickname.'), 404);
        }

        $local = Local_group::getKV('nickname', $nickname);

        if (!$local) {
            $alias = Group_alias::getKV('alias', $nickname);
            if ($alias) {
                $args = array('id' => $alias->group_id);
                if ($this->page != 1) {
                    $args['page'] = $this->page;
                }
                common_redirect(common_local_url('groupbyid', $args), 301);
            } else {
                common_log(LOG_NOTICE, "Couldn't find local group for nickname '$nickname'");
                // TRANS: Client error displayed if no remote group with a given name was found requesting group page.
                throw new ClientException(_('No such group.'), 404);
            }
        }

        $this->group = User_group::getKV('id', $local->group_id);
        $this->target = $this->group->getProfile();

        if (!$this->group instanceof User_group) {
            // TRANS: Client error displayed if no local group with a given name was found requesting group page.
            throw new ClientException(_('No such group.'), 404);
        }
    }

    function showProfileBlock()
    {
        $block = new GroupProfileBlock($this, $this->group);
        $block->show();
    }

    /**
     * Fill in the sidebar.
     *
     * @return void
     */
    function showSections()
    {
        $this->showMembers();
        if ($this->scoped instanceof Profile && $this->scoped->isAdmin($this->group)) {
            $this->showPending();
            $this->showBlocked();
        }

        $this->showAdmins();
    }

    /**
     * Show mini-list of members
     *
     * @return void
     */
    function showMembers()
    {
        $member = $this->group->getMembers(0, MEMBERS_PER_SECTION);

        if (!$member) {
            return;
        }

        $this->elementStart('div', array('id' => 'entity_members',
                                         'class' => 'section'));

        if (Event::handle('StartShowGroupMembersMiniList', array($this))) {
            $this->elementStart('h2');

            $this->element('a', array('href' => common_local_url('groupmembers', array('nickname' =>
                                                                                       $this->group->nickname))),
                           // TRANS: Header for mini list of group members on a group page (h2).
                           _('Members'));

            $this->text(' ');

            $this->text($this->group->getMemberCount());

            $this->elementEnd('h2');

            $gmml = new GroupMembersMiniList($member, $this);
            $cnt = $gmml->show();
            if ($cnt == 0) {
                // TRANS: Description for mini list of group members on a group page when the group has no members.
                $this->element('p', null, _('(None)'));
            }

            // @todo FIXME: Should be shown if a group has more than 27 members, but I do not see it displayed at
            //              for example http://identi.ca/group/statusnet. Broken?
            if ($cnt > MEMBERS_PER_SECTION) {
                $this->element('a', array('href' => common_local_url('groupmembers',
                                                                     array('nickname' => $this->group->nickname))),
                               // TRANS: Link to all group members from mini list of group members if group has more than n members.
                               _('All members'));
            }

            Event::handle('EndShowGroupMembersMiniList', array($this));
        }

        $this->elementEnd('div');
    }

    function showPending()
    {
        if ($this->group->join_policy != User_group::JOIN_POLICY_MODERATE) {
            return;
        }

        $pending = $this->group->getQueueCount();

        if (!$pending) {
            return;
        }

        $request = $this->group->getRequests(0, MEMBERS_PER_SECTION);

        if (!$request) {
            return;
        }

        $this->elementStart('div', array('id' => 'entity_pending',
                                         'class' => 'section'));

        if (Event::handle('StartShowGroupPendingMiniList', array($this))) {

            $this->elementStart('h2');

            $this->element('a', array('href' => common_local_url('groupqueue', array('nickname' =>
                                                                                     $this->group->nickname))),
                           // TRANS: Header for mini list of users with a pending membership request on a group page (h2).
                           _('Pending'));

            $this->text(' ');

            $this->text($pending);

            $this->elementEnd('h2');

            $gmml = new ProfileMiniList($request, $this);
            $gmml->show();

            Event::handle('EndShowGroupPendingMiniList', array($this));
        }

        $this->elementEnd('div');
    }

    function showBlocked()
    {
        $blocked = $this->group->getBlocked(0, MEMBERS_PER_SECTION);

        $this->elementStart('div', array('id' => 'entity_blocked',
                                         'class' => 'section'));

        if (Event::handle('StartShowGroupBlockedMiniList', array($this))) {

            $this->elementStart('h2');

            $this->element('a', array('href' => common_local_url('blockedfromgroup', array('nickname' =>
                                                                                           $this->group->nickname))),
                           // TRANS: Header for mini list of users that are blocked in a group page (h2).
                           _('Blocked'));

            $this->text(' ');

            $this->text($this->group->getBlockedCount());

            $this->elementEnd('h2');

            $gmml = new GroupBlockedMiniList($blocked, $this);
            $cnt = $gmml->show();
            if ($cnt == 0) {
                // TRANS: Description for mini list of group members on a group page when the group has no members.
                $this->element('p', null, _('(None)'));
            }

            // @todo FIXME: Should be shown if a group has more than 27 members, but I do not see it displayed at
            //              for example http://identi.ca/group/statusnet. Broken?
            if ($cnt > MEMBERS_PER_SECTION) {
                $this->element('a', array('href' => common_local_url('blockedfromgroup',
                                                                     array('nickname' => $this->group->nickname))),
                               // TRANS: Link to all group members from mini list of group members if group has more than n members.
                               _('All members'));
            }

            Event::handle('EndShowGroupBlockedMiniList', array($this));
        }

        $this->elementEnd('div');
    }

    /**
     * Show list of admins
     *
     * @return void
     */
    function showAdmins()
    {
        $adminSection = new GroupAdminSection($this, $this->group);
        $adminSection->show();
    }

    function noticeFormOptions()
    {
        $options = parent::noticeFormOptions();
        $cur = common_current_user();

        if (!empty($cur) && $cur->isMember($this->group)) {
            $options['to_group'] =  $this->group;
        }

        return $options;
    }

    function getGroup()
    {
        return $this->group;
    }
}
?>