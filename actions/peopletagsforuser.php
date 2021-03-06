<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: PeopleTagsForUser
 * People tags for a user
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
 * People tags for a user
 *
 * PHP version:
 * Tested with PHP 7.0
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

require_once INSTALLDIR.'/lib/peopletaglist.php';

class PeopletagsforuserAction extends Action
{
    var $page = null;
    var $tagged = null;

    function isReadOnly($args)
    {
        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            // TRANS: Page title. %s is a tagged user's nickname.
            return sprintf(_('Lists with %s in them'), $this->tagged->nickname);
        } else {
            // TRANS: Page title. %1$s is a tagged user's nickname, %2$s is a page number.
            return sprintf(_('Lists with %1$s, page %2$d'), $this->tagged->nickname, $this->page);
        }
    }

    function prepare(array $args = array())
    {
        parent::prepare($args);

        if (common_config('singleuser', 'enabled')) {
            $nickname_arg = User::singleUserNickname();
        } else {
            $nickname_arg = $this->arg('nickname');
        }

        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            if ($this->arg('page') && $this->arg('page') != 1) {
                $args['page'] = $this->arg['page'];
            }
            common_redirect(common_local_url('peopletagsforuser', $args), 301);
        }

        $this->user = User::getKV('nickname', $nickname);

        if (!$this->user) {
            // TRANS: Client error displayed trying to perform an action related to a non-existing user.
            $this->clientError(_('No such user.'), 404);
        }

        $this->tagged = $this->user->getProfile();

        if (!$this->tagged) {
            // TRANS: Error message displayed when referring to a user without a profile.
            $this->serverError(_('User has no profile.'));
        }

        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        return true;
    }

    function handle()
    {
        parent::handle();
        $this->showPage();
    }

    function showAnonymousMessage()
    {
        $notice =
          // TRANS: Message displayed for anonymous users on page that displays lists for a user.
          // TRANS: This message contains Markdown links in the form [description](links).
          // TRANS: %s is a tagger nickname.
          sprintf(_('These are lists for **%s**. ' .
                    'lists are how you sort similar ' .
                    'people on %%%%site.name%%%%, a [micro-blogging]' .
                    '(http://en.wikipedia.org/wiki/Micro-blogging) service ' .
                    'based on the Free Software [StatusNet](http://status.net/) tool. ' .
                    'You can easily keep track of what they ' .
                    'are doing by subscribing to the list\'s timeline.' ), $this->tagged->nickname);
        $this->elementStart('div', array('id' => 'anon_notice'));
        $this->raw(common_markup_to_html($notice));
        $this->elementEnd('div');
    }

    function showContent()
    {
        #TODO: controls here.

        $offset = ($this->page-1) * PEOPLETAGS_PER_PAGE;
        $limit  = PEOPLETAGS_PER_PAGE + 1;

        $ptags = $this->tagged->getOtherTags($this->scoped, $offset, $limit);

        $pl = new PeopletagList($ptags, $this);
        $cnt = $pl->show();

        if ($cnt == 0) {
            $this->showEmptyListMessage();
        }
        $this->pagination($this->page > 1, $cnt > PEOPLETAGS_PER_PAGE,
                          $this->page, 'peopletagsforuser', array('nickname' => $this->tagged->id));
    }

    function showEmptyListMessage()
    {
        // TRANS: Message displayed on page that displays lists a user was added to when there are none.
        // TRANS: This message contains Markdown links in the form [description](links).
        // TRANS: %s is a user nickname.
        $message = sprintf(_('%s has not been [listed](%%%%doc.lists%%%%) by anyone yet.'), $this->tagged->nickname);
        $this->elementStart('div', 'guide');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    }

    function showObjectNav()
    {
        $nav = new PeopletagNav($this, $this->tagged);
        $nav->show();
    }

    function showProfileBlock()
    {
        $block = new AccountProfileBlock($this, $this->tagged);
        $block->show();
    }

    function showSections()
    {
        #TODO: tags with most subscribers
        #TODO: tags with most "members"
    }
}

// END OF FILE
// ============================================================================
?>