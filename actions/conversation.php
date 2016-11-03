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
 * Conversation tree in the browser
 *
 * Will always try to show the entire conversation, since that's how our
 * ConversationNoticeStream works.
 *
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Zach Copley <zach@copley.name>
 * @author    Jeffrey To <jeffery.to@gmail.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

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