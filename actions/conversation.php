<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * PHP version 5
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
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2009-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Conversation tree in the browser
 *
 * Will always try to show the entire conversation, since that's how our
 * ConversationNoticeStream works.
 */
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