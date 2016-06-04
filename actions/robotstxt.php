<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * robots.txt generator
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
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Prints out a static robots.txt
 */
class RobotstxtAction extends ManagedAction
{
    public function showPage()
    {
        if (Event::handle('StartRobotsTxt', array($this))) {

            header('Content-Type: text/plain');

            print "User-Agent: *\n";

            if (common_config('site', 'private')) {

                print "Disallow: /\n";
            } else {
                $disallow = common_config('robotstxt', 'disallow');

                foreach ($disallow as $dir) {
                    print "Disallow: /$dir/\n";
                }

                $crawldelay = common_config('robotstxt', 'crawldelay');

                if (!empty($crawldelay)) {
                    print "Crawl-delay: " . $crawldelay . "\n";
                }
            }

            Event::handle('EndRobotsTxt', array($this));
        }
    }

    /**
     * Return true; this page doesn't touch the DB.
     *
     * @param array $args other arguments
     *
     * @return boolean is read only action?
     */
    function isReadOnly($args)
    {
        return true;
    }
}
?>