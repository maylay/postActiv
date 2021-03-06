<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Notice stream object for a conversation
 *
 * @category  Conversation
 * @package   postActiv
 * @author    Evan Prodromou
 * @copyright 2011-2012 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Notice stream for a conversation
 */
class ConversationNoticeStream extends ScopingNoticeStream
{
    function __construct($id, Profile $scoped=null)
    {
        parent::__construct(new RawConversationNoticeStream($id),
                            $scoped);
    }
}

/**
 * Notice stream for a conversation
 *
 * @category  Stream
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class RawConversationNoticeStream extends NoticeStream
{
    protected $id;

    function __construct($id)
    {
        parent::__construct();
        $this->id = $id;
    }

    function getNoticeIds($offset, $limit, $since_id=null, $max_id=null)
    {
        $notice = new Notice();
        // SELECT
        $notice->selectAdd();
        $notice->selectAdd('id');

        // WHERE
        $notice->conversation = $this->id;
        if (!empty($since_id)) {
            $notice->whereAdd(sprintf('notice.id > %d', $since_id));
        }
        if (!empty($max_id)) {
            $notice->whereAdd(sprintf('notice.id <= %d', $max_id));
        }
        if (!is_null($offset)) {
            $notice->limit($offset, $limit);
        }

        self::filterVerbs($notice, $this->selectVerbs);

        // ORDER BY
        // currently imitates the previously used "_reverseChron" sorting
        $notice->orderBy('notice.created DESC');
        $notice->find();
        return $notice->fetchAll('id');
    }
}
?>