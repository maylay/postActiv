<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: Groups
 * Latest groups information
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
 * Latest groups information
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Sarven Capadisli
 * o Meitar Moscovitz <meitarm@gmail.com>
 * o Jeffery To <jeffery.to@gmail.com>
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

require_once INSTALLDIR.'/lib/grouplist.php';

/**
 * Latest groups
 *
 * Show the latest groups on the site
 */
class GroupsAction extends Action
{
    var $page = null;
    var $profile = null;

    function isReadOnly($args)
    {
        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Title for first page of the groups list.
            return _m('TITLE',"Groups");
        } else {
            // TRANS: Title for all but the first page of the groups list.
            // TRANS: %d is the page number.
            return sprintf(_m('TITLE',"Groups, page %d"), $this->page);
        }
    }

    function prepare(array $args = array())
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;
        return true;
    }

    function handle()
    {
        parent::handle();
        $this->showPage();
    }

    function showPageNotice()
    {
        $notice =
          // TRANS: Page notice of group list. %%%%site.name%%%% is the StatusNet site name,
          // TRANS: %%%%action.groupsearch%%%% and %%%%action.newgroup%%%% are URLs. Do not change them.
          // TRANS: This message contains Markdown links in the form [link text](link).
          sprintf(_('%%%%site.name%%%% groups let you find and talk with ' .
                    'people of similar interests. After you join a group ' .
                    'you can send messages to all other members using the ' .
                    'syntax "!groupname". Don\'t see a group you like? Try ' .
                    '[searching for one](%%%%action.groupsearch%%%%) or ' .
                    '[start your own](%%%%action.newgroup%%%%)!'));
        $this->elementStart('div', 'instructions');
        $this->raw(common_markup_to_html($notice));
        $this->elementEnd('div');
    }

    function showContent()
    {
        if (common_logged_in()) {
            $this->elementStart('p', array('id' => 'new_group'));
            $this->element('a', array('href' => common_local_url('newgroup'),
                                      'class' => 'more'),
                           // TRANS: Link to create a new group on the group list page.
                           _('Create a new group'));
            $this->elementEnd('p');
        }

        $offset = ($this->page-1) * GROUPS_PER_PAGE;
        $limit  = GROUPS_PER_PAGE + 1;

        $qry = 'SELECT user_group.* '.
          'from user_group join local_group on user_group.id = local_group.group_id '.
          'order by user_group.created desc '.
          'limit ' . $limit . ' offset ' . $offset;

        $groups = new User_group();

        $cnt = 0;

        $groups->query($qry);

        $gl = new GroupList($groups, null, $this);
        $cnt = $gl->show();

        $this->pagination($this->page > 1, $cnt > GROUPS_PER_PAGE,
                          $this->page, 'groups');
    }

    function showSections()
    {
        $gbp = new GroupsByPostsSection($this);
        $gbp->show();
        $gbm = new GroupsByMembersSection($this);
        $gbm->show();
    }
}

// END OF FILE
// ============================================================================
?>