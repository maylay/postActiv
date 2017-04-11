<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: Subscribers
 * List a user's subscribers
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
 * List a user's subscribers
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Robin Millette <robin@millette.info>
 * o Jeffery To <jeffery.to@gmail.com>
 * o Brion Vibber <brion@pobox.com>
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
 * List a user's subscribers
 */
class SubscribersAction extends GalleryAction
{
    function title()
    {
        if ($this->page == 1) {
            // TRANS: Header for list of subscribers for a user (first page).
            // TRANS: %s is the user's nickname.
            return sprintf(_('%s subscribers'), $this->target->getNickname());
        } else {
            // TRANS: Header for list of subscribers for a user (not first page).
            // TRANS: %1$s is the user's nickname, $2$d is the page number.
            return sprintf(_('%1$s subscribers, page %2$d'),
                           $this->target->getNickname(),
                           $this->page);
        }
    }

    function showPageNotice()
    {
        if ($this->scoped instanceof Profile && $this->scoped->id === $this->target->id) {
            $this->element('p', null,
                           // TRANS: Page notice for page with an overview of all subscribers
                           // TRANS: of the logged in user's own profile.
                           _('These are the people who listen to '.
                             'your notices.'));
        } else {
            $this->element('p', null,
                           // TRANS: Page notice for page with an overview of all subscribers of a user other
                           // TRANS: than the logged in user. %s is the user nickname.
                           sprintf(_('These are the people who '.
                                     'listen to %s\'s notices.'),
                                   $this->target->getNickname()));
        }
    }

    function showContent()
    {
        parent::showContent();

        $offset = ($this->page-1) * PROFILES_PER_PAGE;
        $limit =  PROFILES_PER_PAGE + 1;

        $cnt = 0;

        if ($this->tag) {
            $subscribers = $this->target->getTaggedSubscribers($this->tag, $offset, $limit);
        } else {
            $subscribers = $this->target->getSubscribers($offset, $limit);
        }

        if ($subscribers) {
            $subscribers_list = new SubscribersList($subscribers, $this->target, $this);
            $cnt = $subscribers_list->show();
            if (0 == $cnt) {
                $this->showEmptyListMessage();
            }
        }

        $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                          $this->page, 'subscribers',
                          array('nickname' => $this->target->getNickname()));
    }

    function showEmptyListMessage()
    {
        if ($this->scoped instanceof Profile && $this->target->id === $this->scoped->id) {
            // TRANS: Subscriber list text when the logged in user has no subscribers.
            $message = _('You have no subscribers. Try subscribing to people you know and they might return the favor.');
        } elseif ($this->scoped instanceof Profile) {
            // TRANS: Subscriber list text when looking at the subscribers for a of a user other
            // TRANS: than the logged in user that has no subscribers. %s is the user nickname.
            $message = sprintf(_('%s has no subscribers. Want to be the first?'), $this->target->getNickname());
        } else {
            // TRANS: Subscriber list text when looking at the subscribers for a of a user that has none
            // TRANS: as an anonymous user. %s is the user nickname.
            // TRANS: This message contains a Markdown URL. The link description is between
            // TRANS: square brackets, and the link between parentheses. Do not separate "]("
            // TRANS: and do not change the URL part.
            $message = sprintf(_('%s has no subscribers. Why not [register an account](%%%%action.register%%%%) and be the first?'), $this->target->getNickname());
        }

        $this->elementStart('div', 'guide');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    }

    function showSections()
    {
        parent::showSections();
    }
}

// END OF FILE
// ============================================================================
?>