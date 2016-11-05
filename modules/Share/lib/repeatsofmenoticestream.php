<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Stream of notices that are repeats of mine
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
 * @category  Stream
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Stream of notices that are repeats of mine
 *
 * @category  Stream
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

class RepeatsOfMeNoticeStream extends ScopingNoticeStream
{
    function __construct(Profile $target, Profile $scoped=null)
    {
        parent::__construct(new CachingNoticeStream(new RawRepeatsOfMeNoticeStream($target),
                                                    'user:repeats_of_me:'.$target->getID()),
                            $scoped);
    }
}

/**
 * Raw stream of notices that are repeats of mine
 *
 * @category  Stream
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class RawRepeatsOfMeNoticeStream extends NoticeStream
{
    protected $target;

    function __construct(Profile $target)
    {
        $this->target = $target;
    }

    function getNoticeIds($offset, $limit, $since_id, $max_id)
    {
        $qry =
          'SELECT DISTINCT original.id AS id ' .
          'FROM notice original JOIN notice rept ON original.id = rept.repeat_of ' .
          'WHERE original.profile_id = ' . $this->target->getID() . ' ';

        $since = Notice::whereSinceId($since_id, 'original.id', 'original.created');
        if ($since) {
            $qry .= "AND ($since) ";
        }

        $max = Notice::whereMaxId($max_id, 'original.id', 'original.created');
        if ($max) {
            $qry .= "AND ($max) ";
        }

        $qry .= 'ORDER BY original.created, original.id DESC ';

        if (!is_null($offset)) {
            $qry .= "LIMIT $limit OFFSET $offset";
        }

        $ids = array();

        $notice = new Notice();

        $notice->query($qry);

        while ($notice->fetch()) {
            $ids[] = $notice->id;
        }

        $notice->free();
        $notice = NULL;

        return $ids;
    }
}
