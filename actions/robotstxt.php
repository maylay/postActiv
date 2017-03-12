<?php
/* ============================================================================
 * Title: RobotsTxt
 * robots.txt generator
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
 * robots.txt generator
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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