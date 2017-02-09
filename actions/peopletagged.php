<?php
/* ============================================================================
 * Title: PeopleTagged
 * List of people tagged by the user with a tag
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
 * List of people tagged by the user with a tag
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Shashi Gowda <connect2shashi@gmail.com>
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

/**
 * List of people tagged by the user with a tag
 */
class PeopletaggedAction extends Action
{
    var $page = null;
    var $peopletag = null;
    var $tagger = null;

    function isReadOnly($args)
    {
        return true;
    }

    function prepare(array $args = array())
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        if (common_config('singleuser', 'enabled')) {
            $tagger_arg = User::singleUserNickname();
        } else {
            $tagger_arg = $this->arg('tagger');
        }

        $tag_arg = $this->arg('tag');
        $tagger = common_canonical_nickname($tagger_arg);
        $tag = common_canonical_tag($tag_arg);

        // Permanent redirect on non-canonical nickname

        if ($tagger_arg != $tagger || $tag_arg != $tag) {
            $args = array('tagger' => $nickname, 'tag' => $tag);
            if ($this->page != 1) {
                $args['page'] = $this->page;
            }
            common_redirect(common_local_url('peopletagged', $args), 301);
        }

        if (!$tagger) {
            // TRANS: Client error displayed when a tagger is expected but not provided.
            $this->clientError(_('No tagger.'), 404);
        }

        $user = User::getKV('nickname', $tagger);

        if (!$user) {
            // TRANS: Client error displayed when referring to non-existing user.
            $this->clientError(_('No such user.'), 404);
        }

        $this->tagger = $user->getProfile();
        $this->peopletag = Profile_list::pkeyGet(array('tagger' => $user->id, 'tag' => $tag));

        if (!$this->peopletag) {
            // TRANS: Client error displayed when referring to a non-existing list.
            $this->clientError(_('No such list.'), 404);
        }

        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Title for list of people listed by the user.
            // TRANS: %1$s is a list, %2$s is a username.
            return sprintf(_('People listed in %1$s by %2$s'),
                           $this->peopletag->tag, $this->tagger->nickname);
        } else {
            // TRANS: Title for list of people listed by the user.
            // TRANS: %1$s is a list, %2$s is a username, %2$s is a page number.
            return sprintf(_('People listed in %1$s by %2$s, page %3$d'),
                           $this->peopletag->tag, $this->user->nickname,
                           $this->page);
        }
    }

    function handle()
    {
        parent::handle();
        $this->showPage();
    }

    function showPageNotice()
    {
    }

    function showLocalNav()
    {
        $nav = new PeopletagGroupNav($this, $this->peopletag);
        $nav->show();
    }

    function showContent()
    {
        $offset = ($this->page-1) * PROFILES_PER_PAGE;
        $limit =  PROFILES_PER_PAGE + 1;

        $cnt = 0;

        $subs = $this->peopletag->getTagged($offset, $limit);

        if ($subs) {
            $subscriber_list = new PeopletagMemberList($subs, $this->peopletag, $this);
            $cnt = $subscriber_list->show();
        }

        $subs->free();

        $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                          $this->page, 'peopletagged',
                          array('tagger' => $this->tagger->nickname,
                                'tag'    => $this->peopletag->tag));
    }
}

class PeopletagMemberList extends ProfileList
{
    var $peopletag = null;

    function __construct($profile, $peopletag, $action)
    {
        parent::__construct($profile, $action);

        $this->peopletag = $peopletag;
    }

    function newListItem($profile)
    {
        return new PeopletagMemberListItem($profile, $this->peopletag, $this->action);
    }
}

class PeopletagMemberListItem extends ProfileListItem
{
    var $peopletag = null;

    function __construct($profile, $peopletag, $action)
    {
        parent::__construct($profile, $action);

        $this->peopletag = $peopletag;
    }

    function showFullName()
    {
        parent::showFullName();
        if ($this->profile->id == $this->peopletag->tagger) {
            $this->out->text(' ');
            // TRANS: Addition in tag membership list for creator of a tag.
            $this->out->element('span', 'role', _('Creator'));
        }
    }

    function showActions()
    {
        $this->startActions();
        if (Event::handle('StartProfileListItemActionElements', array($this))) {
            $this->showSubscribeButton();
            // TODO: Untag button
            Event::handle('EndProfileListItemActionElements', array($this));
        }
        $this->endActions();
    }

    function linkAttributes()
    {
        // tagging people is healthy page-rank flow.
        return parent::linkAttributes();
    }

    /**
     * Fetch necessary return-to arguments for the profile forms
     * to return to this list when they're done.
     *
     * @return array
     */
    protected function returnToArgs()
    {
        $args = array('action' => 'peopletagged',
                      'tag' => $this->peopletag->tag,
                      'tagger' => $this->profile->nickname);
        $page = $this->out->arg('page');
        if ($page) {
            $args['param-page'] = $page;
        }
        return $args;
    }
}
?>