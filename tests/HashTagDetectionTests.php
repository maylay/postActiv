<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unit test for hashtag detection
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
 * @author    Brenda Wallace <shiny@cpan.org>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Craig Andrews <candrews@integralblue.com>
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

class HashTagDetectionTests extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     *
     */
    public function testProduction($content, $expected)
    {
        $rendered = common_render_text($content);
        $this->assertEquals($expected, $rendered);
    }

    static public function provider()
    {
        return array(
                     array('hello',
                           'hello'),
                     array('#hello people',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('hello'))) . '" rel="tag">hello</a></span> people'),
                     array('"#hello" people',
                           '&quot;#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('hello'))) . '" rel="tag">hello</a></span>&quot; people'),
                     array('say "#hello" people',
                           'say &quot;#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('hello'))) . '" rel="tag">hello</a></span>&quot; people'),
                     array('say (#hello) people',
                           'say (#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('hello'))) . '" rel="tag">hello</a></span>) people'),
                     array('say [#hello] people',
                           'say [#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('hello'))) . '" rel="tag">hello</a></span>] people'),
                     array('say {#hello} people',
                           'say {#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('hello'))) . '" rel="tag">hello</a></span>} people'),
                     array('say \'#hello\' people',
                           'say \'#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('hello'))) . '" rel="tag">hello</a></span>\' people'),

                     // Unicode legit letters
                     array('#éclair yummy',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('éclair'))) . '" rel="tag">éclair</a></span> yummy'),
                     array('#维基百科 zh.wikipedia!',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('维基百科'))) . '" rel="tag">维基百科</a></span> zh.wikipedia!'),
                     array('#Россия russia',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('Россия'))) . '" rel="tag">Россия</a></span> russia'),

                     // Unicode punctuators -- the ideographic "，" separates the tag, just as "," does
                     array('#维基百科,zh.wikipedia!',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('维基百科'))) . '" rel="tag">维基百科</a></span>,zh.wikipedia!'),
                     array('#维基百科，zh.wikipedia!',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('维基百科'))) . '" rel="tag">维基百科</a></span>，zh.wikipedia!'),

                     );
    }
}

