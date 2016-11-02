<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unit test for @-alias (nickname) code
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

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));
define('POSTACTIV', true);
define('GNUSOCIAL', true);
define('STATUSNET', true);  // compatibility

require_once INSTALLDIR . '/lib/common.php';

/**
 * Test cases for nickname validity and normalization.
 */
class NicknameTest extends PHPUnit_Framework_TestCase
{
    /**
     * Basic test using Nickname::normalize()
     *
     * @dataProvider provider
     */
    public function testBasic($input, $expected, $expectedException=null)
    {
        $exception = null;
        $normalized = false;
        try {
            $normalized = Nickname::normalize($input);
        } catch (NicknameException $e) {
            $exception = $e;
        }

        if ($expected === false) {
            if ($expectedException) {
                if ($exception) {
                    $stuff = get_class($exception) . ': ' . $exception->getMessage();
                } else {
                    $stuff = var_export($exception, true);
                }
                $this->assertTrue($exception && $exception instanceof $expectedException,
                        "invalid input '$input' expected to fail with $expectedException, " .
                        "got $stuff");
            } else {
                $this->assertTrue($normalized == false,
                        "invalid input '$input' expected to fail");
            }
        } else {
            $msg = "normalized input nickname '$input' expected to normalize to '$expected', got ";
            if ($exception) {
                $msg .= get_class($exception) . ': ' . $exception->getMessage();
            } else {
                $msg .= "'$normalized'";
            }
            $this->assertEquals($expected, $normalized, $msg);
        }
    }

    /**
     * Test on the regex matching used in common_find_mentions
     * (testing on the full notice rendering is difficult as it needs
     * to be able to pull from global state)
     *
     * @dataProvider provider
     */
    public function testAtReply($input, $expected, $expectedException=null)
    {
        if ($expected == false) {
            // nothing to do
        } else {
            $text = "@{$input} awesome! :)";
            $matches = common_find_mentions_raw($text);
            $this->assertEquals(1, count($matches));
            $this->assertEquals($expected, Nickname::normalize($matches[0][0]));
        }
    }

    static public function provider()
    {
        return array(
                     array('evan', 'evan'),

                     // Case and underscore variants
                     array('Evan', 'evan'),
                     array('EVAN', 'evan'),
                     array('ev_an', 'evan'),
                     array('E__V_an', 'evan'),
                     array('evan1', 'evan1'),
                     array('evan_1', 'evan1'),
                     array('0x20', '0x20'),
                     array('1234', '1234'), // should this be allowed though? :)
                     array('12__34', '1234'),

                     // Some (currently) invalid chars...
                     array('^#@&^#@', false, 'NicknameInvalidException'), // all invalid :D
                     array('ev.an', false, 'NicknameInvalidException'),
                     array('ev/an', false, 'NicknameInvalidException'),
                     array('ev an', false, 'NicknameInvalidException'),
                     array('ev-an', false, 'NicknameInvalidException'),

                     // Non-ASCII letters; currently not allowed, in future
                     // we'll add them at least with conversion to ASCII.
                     // Not much use until we have storage of display names,
                     // though.
                     array('évan', false, 'NicknameInvalidException'), // so far...
                     array('Évan', false, 'NicknameInvalidException'), // so far...

                     // Length checks
                     array('', false, 'NicknameEmptyException'),
                     array('___', false, 'NicknameEmptyException'),
                     array('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee'), // 64 chars
                     array('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee_', false, 'NicknameTooLongException'), // the _ is too long...
                     array('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', false, 'NicknameTooLongException'), // 65 chars -- too long
                     );
    }
}
