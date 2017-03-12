<?php
/* ============================================================================
 * Title: PublicRSS
 * Public RSS action class
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
 * Public RSS action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Robin Millette <robin@millette.info>
 * o Eric Helgeson
 * o Jeffery To <jeffery.to@gmail.com>
 * o Zach Copley
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