<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unit test for geolocation code
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
 * @category  Unit Tests
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (isset($_SERVER) && array_key_exists('REQUEST_METHOD', $_SERVER)) {
    print "This script must be run from the command line\n";
    exit();
}

define('INSTALLDIR', realpath(dirname(__FILE__) . '/../..'));
define('POSTACTIV', true);
define('GNUSOCIAL', true);
define('STATUSNET', true);  // compatibility

require_once INSTALLDIR . '/lib/common.php';

// Make sure this is loaded
// XXX: how to test other plugins...?

addPlugin('Geonames');

class LocationTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider locationNames
     */

    public function testLocationFromName($name, $language, $location)
    {
        $result = Location::fromName($name, $language);
        $this->assertEquals($result, $location);
    }

    static public function locationNames()
    {
        return array(array('Montreal', 'en', null),
                     array('San Francisco, CA', 'en', null),
                     array('Paris, France', 'en', null),
                     array('Paris, Texas', 'en', null));
    }

    /**
     * @dataProvider locationIds
     */

    public function testLocationFromId($id, $ns, $language, $location)
    {
        $result = Location::fromId($id, $ns, $language);
        $this->assertEquals($result, $location);
    }

    static public function locationIds()
    {
        return array(array(6077243, GeonamesPlugin::LOCATION_NS, 'en', null),
                     array(5391959, GeonamesPlugin::LOCATION_NS, 'en', null));
    }

    /**
     * @dataProvider locationLatLons
     */

    public function testLocationFromLatLon($lat, $lon, $language, $location)
    {
        $result = Location::fromLatLon($lat, $lon, $language);
        $this->assertEquals($result, $location);
    }

    static public function locationLatLons()
    {
        return array(array(37.77493, -122.41942, 'en', null),
                     array(45.509, -73.588, 'en', null));
    }

    /**
     * @dataProvider nameOfLocation
     */

    public function testLocationGetName($location, $language, $name)
    {
        $result = $location->getName($language);
        $this->assertEquals($result, $name);
    }

    static public function nameOfLocation()
    {
        return array(array(new Location(), 'en', 'Montreal'),
                     array(new Location(), 'fr', 'Montréal'));
    }
}

