<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unit test for curry()
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

define('INSTALLDIR', realpath(dirname(__FILE__) . '/../..'));
define('POSTACTIV', true);
define('GNUSOCIAL', true);
define('STATUSNET', true);  // compatibility

require_once INSTALLDIR . '/lib/common.php';

class CurryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     *
     */
    public function testProduction($callback, $curry_params, $call_params, $expected)
    {
        $params = array_merge(array($callback), $curry_params);
        $curried = call_user_func_array('curry', $params);
        $result = call_user_func_array($curried, $call_params);
        $this->assertEquals($expected, $result);
    }

    static public function provider()
    {
        $obj = new CurryTestHelperObj('oldval');
        return array(array(array('CurryTest', 'callback'),
                           array('curried'),
                           array('called'),
                           'called|curried'),
                     array(array('CurryTest', 'callback'),
                           array('curried1', 'curried2'),
                           array('called1', 'called2'),
                           'called1|called2|curried1|curried2'),
                     array(array('CurryTest', 'callbackObj'),
                           array($obj),
                           array('newval1'),
                           'oldval|newval1'),
                     // Confirm object identity is retained...
                     array(array('CurryTest', 'callbackObj'),
                           array($obj),
                           array('newval2'),
                           'newval1|newval2'));
    }

    static function callback()
    {
        $args = func_get_args();
        return implode("|", $args);
    }

    static function callbackObj($val, $obj)
    {
        $old = $obj->val;
        $obj->val = $val;
        return "$old|$val";
    }
}

class CurryTestHelperObj
{
    public $val='';

    function __construct($val)
    {
        $this->val = $val;
    }
}
