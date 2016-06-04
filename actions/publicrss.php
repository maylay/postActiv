<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Public RSS action class.
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
 * @author    Robin Millette <millette@status.net>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link      https://git.gnu.io/maiyannah/postActiv 
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * RSS feed for public timeline.
 *
 * Formatting of RSS handled by Rss10Action
 */
class PublicrssAction extends Rss10Action
{
    /**
     * Get notices
     *
     * @param integer $limit max number of notices to return
     *
     * @return array notices
     */
    protected function getNotices()
    {
        $stream  = Notice::publicStream(0, $this->limit);
        return $stream->fetchAll();
    }

     /**
     * Get channel.
     *
     * @return array associative array on channel information
     */
    function getChannel()
    {
        $sitename = common_config('site', 'name');
        $c = array(
              'url' => common_local_url('publicrss'),
            // TRANS: Public RSS feed title. %s is the StatusNet site name.
              'title' => sprintf(_('%s public timeline'), $sitename),
              'link' => common_local_url('public'),
            // TRANS: Public RSS feed description. %s is the StatusNet site name.
              'description' => sprintf(_('%s updates from everyone.'), $sitename));
        return $c;
    }

    /**
     * Get image.
     *
     * @return nothing
     */
    function getImage()
    {
        // nop
    }

    function isReadOnly($args)
    {
        return true;
    }
}
?>