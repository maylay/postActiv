<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Raw public stream
 *
 * PHP version 5
 *
 * @category  Stream
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

class NetworkPublicNoticeStream extends ModeratedNoticeStream
{
    function __construct(Profile $scoped=null)
    {
        parent::__construct(new CachingNoticeStream(new RawNetworkPublicNoticeStream(),
                                                    'networkpublic'),
                            $scoped);
    }
}

class RawNetworkPublicNoticeStream extends FullNoticeStream
{
    function getNoticeIds($offset, $limit, $since_id, $max_id)
    {
        $notice = new Notice();

        $notice->selectAdd(); // clears it
        $notice->selectAdd('id');

        $notice->orderBy('created DESC, id DESC');

        if (!is_null($offset)) {
            $notice->limit($offset, $limit);
        }

        $notice->whereAdd('is_local ='. Notice::REMOTE);
        // -1 == blacklisted, -2 == gateway (i.e. Twitter)
        $notice->whereAdd('is_local !='. Notice::LOCAL_NONPUBLIC);
        $notice->whereAdd('is_local !='. Notice::GATEWAY);

        Notice::addWhereSinceId($notice, $since_id);
        Notice::addWhereMaxId($notice, $max_id);

        self::filterVerbs($notice, $this->selectVerbs);

        $ids = array();

        if ($notice->find()) {
            while ($notice->fetch()) {
                $ids[] = $notice->id;
            }
        }

        $notice->free();
        $notice = NULL;

        return $ids;
    }
}
?>