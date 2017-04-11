<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unit test for UUID generation
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
 * @copyright 2010-2011 StatusNet, Inc.
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

class UUIDTest extends PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $result = UUID::gen();
        $this->assertRegExp('/^[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}$/',
                            $result);
        // Check version number
        $this->assertEquals(0x4000, hexdec(substr($result, 14, 4)) & 0xF000);
        $this->assertEquals(0x8000, hexdec(substr($result, 19, 4)) & 0xC000);
    }

    public function testUnique()
    {
        $reps = 100;
        $ids = array();

        for ($i = 0; $i < $reps; $i++) {
            $ids[] = UUID::gen();
        }

        $this->assertEquals(count($ids), count(array_unique($ids)), "UUIDs must be unique");
    }
}

