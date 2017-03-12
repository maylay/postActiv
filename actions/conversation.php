<?php
/* ============================================================================
 * Title: Conversation
 * Conversation tree in the browser
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
 * Conversation tree in the browser
 *
 * Will always try to show the entire conversation, since that's how our
 * ConversationNoticeStream works.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Sarven Capadisli
 * o Zach Copley
 * o Jeffrey To <jeffery.to@gmail.com>
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

class ConversationAction extends ManagedAction
{
    var $conv        = null;
    var $page        = null;
    var $notices     = null;

    protected function doPreparation()
    {
        $this->conv = Conversation::getByID($this->int('id'));
    }

    /**
     * Returns the page title
     *
     * @return string page title
     */
    function title()
    {
        // TRANS: Title for page with a conversion (multiple notices in context).
        return _('Conversation');
    }

    /**
     * Show content.
     *
     * NoticeList extended classes do most heavy lifting. Plugins can override.
     *
     * @return void
     */
    function showContent()
    {
        if (Event::handle('StartShowConversation', array($this, $this->conv, $this->scoped))) {
            $notices = $this->conv->getNotices($this->scoped);
            $nl = new FullThreadedNoticeList($notices, $this, $this->scoped);
            $cnt = $nl->show();
        }
        Event::handle('EndShowConversation', array($this, $this->conv, $this->scoped));
    }

    function isReadOnly($args)
    {
        return true;
    }
    
    function getFeeds()
    {
    	
        return array(new Feed(Feed::JSON,
                              common_local_url('apiconversation',
                                               array(
                                                    'id' => $this->conv->getID(),
                                                    'format' => 'as')),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              _('Conversation feed (Activity Streams JSON)')),
                     new Feed(Feed::RSS2,
                              common_local_url('apiconversation',
                                               array(
                                                    'id' => $this->conv->getID(),
                                                    'format' => 'rss')),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              _('Conversation feed (RSS 2.0)')),
                     new Feed(Feed::ATOM,
                              common_local_url('apiconversation',
                                               array(
                                                    'id' => $this->conv->getID(),
                                                    'format' => 'atom')),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              _('Conversation feed (Atom)')));
    }
}
?>