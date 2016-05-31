<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Common superclass for all IM sending queue handlers.
 */

class ImQueueHandler extends QueueHandler
{
    function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Handle a notice
     * @param Notice $notice
     * @return boolean success
     */
    function handle($notice)
    {
        $this->plugin->broadcastNotice($notice);
        if ($notice->isLocal()) {
            $this->plugin->publicNotice($notice);
        }
        return true;
    }
}
?>