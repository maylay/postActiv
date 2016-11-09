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
 * Public RSS action class.
 *
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Robin Millette <robin@millette.info>
 * @author    Eric Helgeson
 * @author    Jeffery To <jeffery.to@gmail.com>
 * @author    Zach Copley
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================ 
 */

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