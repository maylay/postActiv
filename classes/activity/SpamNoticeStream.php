<?php
/* ============================================================================
 * Title: SpamNoticeStream
 * A notice stream which displays messages labelled as Spam by a SpamFilter
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016, Maiyannah Bishop
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
 * A notice stream which displays messages labelled as Spam by a SpamFilter
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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
 * Spam notice stream
 *
 * @category  Spam
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2012 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

class SpamNoticeStream extends ScopingNoticeStream
{
    function __construct(Profile $scoped=null)
    {
        parent::__construct(new CachingNoticeStream(new RawSpamNoticeStream(), 'spam_score:notice_ids'),
                            $scoped);
    }
}

/**
 * Raw stream of spammy notices
 *
 * @category  Stream
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

class RawSpamNoticeStream extends NoticeStream
{
    function getNoticeIds($offset, $limit, $since_id, $max_id)
    {
        $ss = new Spam_score();

        $ss->is_spam = 1;

        $ss->selectAdd();
        $ss->selectAdd('notice_id');

        Notice::addWhereSinceId($ss, $since_id, 'notice_id');
        Notice::addWhereMaxId($ss, $max_id, 'notice_id');

        $ss->orderBy('notice_created DESC, notice_id DESC');

        if (!is_null($offset)) {
            $ss->limit($offset, $limit);
        }

        $ids = array();

        if ($ss->find()) {
            while ($ss->fetch()) {
                $ids[] = $ss->notice_id;
            }
        }

        return $ids;
    }
}
