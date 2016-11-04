<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * A queue manager interface for just doing things immediately
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  QueueManager
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Brion Vibber <brion@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */
 
if (!defined('POSTACTIV')) { exit(1); } 

class UnQueueManager extends QueueManager
{

    /**
     * Dummy queue storage manager: instead of saving events for later,
     * we just process them immediately. This is only suitable for events
     * that can be processed quickly and don't need polling or long-running
     * connections to another server such as XMPP.
     *
     * @param Notice $object    this specific manager just handles Notice objects anyway
     * @param string $queue
     */
    function enqueue($object, $transport)
    {
        try {
            $handler = $this->getHandler($transport);
            $handler->handle($object);
        } catch (NoQueueHandlerException $e) {
            if (Event::handle('UnqueueHandleNotice', array(&$object, $transport))) {
                throw new ServerException("UnQueueManager: Unknown queue transport: $transport");
            }
        }
    }
}
?>