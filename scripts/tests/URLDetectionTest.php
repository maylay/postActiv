<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unit test for detecting URLs
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
 * @author    Craig Andrews <candrews@integralblue.com>
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

class URLDetectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     *
     */
    public function testProduction($content, $expected)
    {
        $rendered = common_render_text($content);
        // hack!
        $rendered = preg_replace('/id="attachment-\d+"/', 'id="attachment-XXX"', $rendered);
        $this->assertEquals($expected, $rendered);
    }

    static public function provider()
    {
        return array(
                     array('not a link :: no way',
                           'not a link :: no way'),
                     array('link http://www.somesite.com/xyz/35637563@N00/52803365/ link',
                           'link <a href="http://www.somesite.com/xyz/35637563@N00/52803365/" title="http://www.somesite.com/xyz/35637563@N00/52803365/" rel="nofollow external">http://www.somesite.com/xyz/35637563@N00/52803365/</a> link'),
                     array('http://127.0.0.1',
                           '<a href="http://127.0.0.1/" title="http://127.0.0.1/" rel="nofollow external">http://127.0.0.1</a>'),
                     array('127.0.0.1',
                           '<a href="http://127.0.0.1/" title="http://127.0.0.1/" rel="nofollow external">127.0.0.1</a>'),
                     array('127.0.0.1:99',
                           '<a href="http://127.0.0.1:99/" title="http://127.0.0.1:99/" rel="nofollow external">127.0.0.1:99</a>'),
                     array('127.0.0.1/Name:test.php',
                           '<a href="http://127.0.0.1/Name:test.php" title="http://127.0.0.1/Name:test.php" rel="nofollow external">127.0.0.1/Name:test.php</a>'),
                     array('127.0.0.1/~test',
                           '<a href="http://127.0.0.1/~test" title="http://127.0.0.1/~test" rel="nofollow external">127.0.0.1/~test</a>'),
                     array('127.0.0.1/+test',
                           '<a href="http://127.0.0.1/+test" title="http://127.0.0.1/+test" rel="nofollow external">127.0.0.1/+test</a>'),
                     array('127.0.0.1/$test',
                           '<a href="http://127.0.0.1/$test" title="http://127.0.0.1/$test" rel="nofollow external">127.0.0.1/$test</a>'),
                     array('127.0.0.1/\'test',
                           '<a href="http://127.0.0.1/\'test" title="http://127.0.0.1/\'test" rel="nofollow external">127.0.0.1/\'test</a>'),
                     array('127.0.0.1/"test',
                           '<a href="http://127.0.0.1/" title="http://127.0.0.1/" rel="nofollow external">127.0.0.1/</a>&quot;test'),
                     array('127.0.0.1/test"test',
                           '<a href="http://127.0.0.1/test" title="http://127.0.0.1/test" rel="nofollow external">127.0.0.1/test</a>&quot;test'),
                     array('127.0.0.1/-test',
                           '<a href="http://127.0.0.1/-test" title="http://127.0.0.1/-test" rel="nofollow external">127.0.0.1/-test</a>'),
                     array('127.0.0.1/_test',
                           '<a href="http://127.0.0.1/_test" title="http://127.0.0.1/_test" rel="nofollow external">127.0.0.1/_test</a>'),
                     array('127.0.0.1/!test',
                           '<a href="http://127.0.0.1/!test" title="http://127.0.0.1/!test" rel="nofollow external">127.0.0.1/!test</a>'),
                     array('127.0.0.1/*test',
                           '<a href="http://127.0.0.1/*test" title="http://127.0.0.1/*test" rel="nofollow external">127.0.0.1/*test</a>'),
                     array('127.0.0.1/test%20stuff',
                           '<a href="http://127.0.0.1/test%20stuff" title="http://127.0.0.1/test%20stuff" rel="nofollow external">127.0.0.1/test%20stuff</a>'),
                     array('http://[::1]:99/test.php',
                           '<a href="http://[::1]:99/test.php" title="http://[::1]:99/test.php" rel="nofollow external">http://[::1]:99/test.php</a>'),
                     array('http://::1/test.php',
                           '<a href="http://::1/test.php" title="http://::1/test.php" rel="nofollow external">http://::1/test.php</a>'),
                     array('http://::1',
                           '<a href="http://::1/" title="http://::1/" rel="nofollow external">http://::1</a>'),
                     array('2001:4978:1b5:0:21d:e0ff:fe66:59ab/test.php',
                           '<a href="http://2001:4978:1b5:0:21d:e0ff:fe66:59ab/test.php" title="http://2001:4978:1b5:0:21d:e0ff:fe66:59ab/test.php" rel="nofollow external">2001:4978:1b5:0:21d:e0ff:fe66:59ab/test.php</a>'),
                     array('[2001:4978:1b5:0:21d:e0ff:fe66:59ab]:99/test.php',
                           '<a href="http://[2001:4978:1b5:0:21d:e0ff:fe66:59ab]:99/test.php" title="http://[2001:4978:1b5:0:21d:e0ff:fe66:59ab]:99/test.php" rel="nofollow external">[2001:4978:1b5:0:21d:e0ff:fe66:59ab]:99/test.php</a>'),
                     array('2001:4978:1b5:0:21d:e0ff:fe66:59ab',
                           '<a href="http://2001:4978:1b5:0:21d:e0ff:fe66:59ab/" title="http://2001:4978:1b5:0:21d:e0ff:fe66:59ab/" rel="nofollow external">2001:4978:1b5:0:21d:e0ff:fe66:59ab</a>'),
                     array('http://127.0.0.1',
                           '<a href="http://127.0.0.1/" title="http://127.0.0.1/" rel="nofollow external">http://127.0.0.1</a>'),
                     array('example.com',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>'),
                     array('example.com',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>'),
                     array('http://example.com',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>'),
                     array('http://example.com.',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>.'),
                     array('/var/lib/example.so',
                           '/var/lib/example.so'),
                     array('example',
                           'example'),
                     array('user@example.com',
                           '<a href="mailto:user@example.com" title="mailto:user@example.com" rel="nofollow external">user@example.com</a>'),
                     array('user_name+other@example.com',
                           '<a href="mailto:user_name+other@example.com" title="mailto:user_name+other@example.com" rel="nofollow external">user_name+other@example.com</a>'),
                     array('mailto:user@example.com',
                           '<a href="mailto:user@example.com" title="mailto:user@example.com" rel="nofollow external">mailto:user@example.com</a>'),
                     array('mailto:user@example.com?subject=test',
                           '<a href="mailto:user@example.com?subject=test" title="mailto:user@example.com?subject=test" rel="nofollow external">mailto:user@example.com?subject=test</a>'),
                     array('xmpp:user@example.com',
                           '<a href="xmpp:user@example.com" title="xmpp:user@example.com" rel="nofollow external">xmpp:user@example.com</a>'),
                     array('#example',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('example'))) . '" rel="tag">example</a></span>'),
                     array('#example.com',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('example.com'))) . '" rel="tag">example.com</a></span>'),
                     array('#.net',
                           '#<span class="tag"><a href="' . common_local_url('tag', array('tag' => common_canonical_tag('.net'))) . '" rel="tag">.net</a></span>'),
                     array('http://example',
                           '<a href="http://example/" title="http://example/" rel="nofollow external">http://example</a>'),
                     array('http://3xampl3',
                           '<a href="http://3xampl3/" title="http://3xampl3/" rel="nofollow external">http://3xampl3</a>'),
                     array('http://example/',
                           '<a href="http://example/" title="http://example/" rel="nofollow external">http://example/</a>'),
                     array('http://example/path',
                           '<a href="http://example/path" title="http://example/path" rel="nofollow external">http://example/path</a>'),
                     array('http://example.com',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>'),
                     array('https://example.com',
                           '<a href="https://example.com/" title="https://example.com/" rel="nofollow external">https://example.com</a>'),
                     array('ftp://example.com',
                           '<a href="ftp://example.com/" title="ftp://example.com/" rel="nofollow external">ftp://example.com</a>'),
                     array('ftps://example.com',
                           '<a href="ftps://example.com/" title="ftps://example.com/" rel="nofollow external">ftps://example.com</a>'),
                     array('http://user@example.com',
                           '<a href="http://user@example.com/" title="http://user@example.com/" rel="nofollow external">http://user@example.com</a>'),
                     array('http://user:pass@example.com',
                           '<a href="http://user:pass@example.com/" title="http://user:pass@example.com/" rel="nofollow external">http://user:pass@example.com</a>'),
                     array('http://example.com:8080',
                           '<a href="http://example.com:8080/" title="http://example.com:8080/" rel="nofollow external">http://example.com:8080</a>'),
                     array('http://example.com:8080/test.php',
                           '<a href="http://example.com:8080/test.php" title="http://example.com:8080/test.php" rel="nofollow external">http://example.com:8080/test.php</a>'),
                     array('example.com:8080/test.php',
                           '<a href="http://example.com:8080/test.php" title="http://example.com:8080/test.php" rel="nofollow external">example.com:8080/test.php</a>'),
                     array('http://www.example.com',
                           '<a href="http://www.example.com/" title="http://www.example.com/" rel="nofollow external">http://www.example.com</a>'),
                     array('http://example.com/',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com/</a>'),
                     array('http://example.com/path',
                           '<a href="http://example.com/path" title="http://example.com/path" rel="nofollow external">http://example.com/path</a>'),
                     array('http://example.com/path.html',
                           '<a href="http://example.com/path.html" title="http://example.com/path.html" rel="nofollow external">http://example.com/path.html</a>'),
                     array('http://example.com/path.html#fragment',
                           '<a href="http://example.com/path.html#fragment" title="http://example.com/path.html#fragment" rel="nofollow external">http://example.com/path.html#fragment</a>'),
                     array('http://example.com/path.php?foo=bar&bar=foo',
                           '<a href="http://example.com/path.php?foo=bar&amp;bar=foo" title="http://example.com/path.php?foo=bar&amp;bar=foo" rel="nofollow external">http://example.com/path.php?foo=bar&amp;bar=foo</a>'),
                     array('http://example.com.',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>.'),
                     array('http://müllärör.de',
                           '<a href="http://m&#xFC;ll&#xE4;r&#xF6;r.de/" title="http://m&#xFC;ll&#xE4;r&#xF6;r.de/" rel="nofollow external">http://müllärör.de</a>'),
                     array('http://ﺱﺲﺷ.com',
                           '<a href="http://&#xFEB1;&#xFEB2;&#xFEB7;.com/" title="http://&#xFEB1;&#xFEB2;&#xFEB7;.com/" rel="nofollow external">http://ﺱﺲﺷ.com</a>'),
                     array('http://сделаткартинки.com',
                           '<a href="http://&#x441;&#x434;&#x435;&#x43B;&#x430;&#x442;&#x43A;&#x430;&#x440;&#x442;&#x438;&#x43D;&#x43A;&#x438;.com/" title="http://&#x441;&#x434;&#x435;&#x43B;&#x430;&#x442;&#x43A;&#x430;&#x440;&#x442;&#x438;&#x43D;&#x43A;&#x438;.com/" rel="nofollow external">http://сделаткартинки.com</a>'),
                     array('http://tūdaliņ.lv',
                           '<a href="http://t&#x16B;dali&#x146;.lv/" title="http://t&#x16B;dali&#x146;.lv/" rel="nofollow external">http://tūdaliņ.lv</a>'),
                     array('http://brændendekærlighed.com',
                           '<a href="http://br&#xE6;ndendek&#xE6;rlighed.com/" title="http://br&#xE6;ndendek&#xE6;rlighed.com/" rel="nofollow external">http://brændendekærlighed.com</a>'),
                     array('http://あーるいん.com',
                           '<a href="http://&#x3042;&#x30FC;&#x308B;&#x3044;&#x3093;.com/" title="http://&#x3042;&#x30FC;&#x308B;&#x3044;&#x3093;.com/" rel="nofollow external">http://あーるいん.com</a>'),
                     array('http://예비교사.com',
                           '<a href="http://&#xC608;&#xBE44;&#xAD50;&#xC0AC;.com/" title="http://&#xC608;&#xBE44;&#xAD50;&#xC0AC;.com/" rel="nofollow external">http://예비교사.com</a>'),
                     array('http://example.com.',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>.'),
                     array('http://example.com?',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>?'),
                     array('http://example.com!',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>!'),
                     array('http://example.com,',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>,'),
                     array('http://example.com;',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>;'),
                     array('http://example.com:',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>:'),
                     array('\'http://example.com\'',
                           '\'<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>\''),
                     array('"http://example.com"',
                           '&quot;<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>&quot;'),
                     array('"http://example.com/"',
                           '&quot;<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com/</a>&quot;'),
                     array('http://example.com',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>'),
                     array('(http://example.com)',
                           '(<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>)'),
                     array('[http://example.com]',
                           '[<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>]'),
                     array('<http://example.com>',
                           '&lt;<a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a>&gt;'),
                     array('http://example.com/path/(foo)/bar',
                           '<a href="http://example.com/path/(foo)/bar" title="http://example.com/path/(foo)/bar" rel="nofollow external">http://example.com/path/(foo)/bar</a>'),
                     array('http://example.com/path/[foo]/bar',
                           '<a href="http://example.com/path/" title="http://example.com/path/" rel="nofollow external">http://example.com/path/</a>[foo]/bar'),
                     array('http://example.com/path/foo/(bar)',
                           '<a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">http://example.com/path/foo/(bar)</a>'),
                     //Not a valid url - urls cannot contain unencoded square brackets
                     array('http://example.com/path/foo/[bar]',
                           '<a href="http://example.com/path/foo/" title="http://example.com/path/foo/" rel="nofollow external">http://example.com/path/foo/</a>[bar]'),
                     array('Hey, check out my cool site http://example.com okay?',
                           'Hey, check out my cool site <a href="http://example.com/" title="http://example.com/" rel="nofollow external">http://example.com</a> okay?'),
                     array('What about parens (e.g. http://example.com/path/foo/(bar))?',
                           'What about parens (e.g. <a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">http://example.com/path/foo/(bar)</a>)?'),
                     array('What about parens (e.g. http://example.com/path/foo/(bar)?',
                           'What about parens (e.g. <a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">http://example.com/path/foo/(bar)</a>?'),
                     array('What about parens (e.g. http://example.com/path/foo/(bar).)?',
                           'What about parens (e.g. <a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">http://example.com/path/foo/(bar)</a>.)?'),
                     //Not a valid url - urls cannot contain unencoded commas
                     array('What about parens (e.g. http://example.com/path/(foo,bar)?',
                           'What about parens (e.g. <a href="http://example.com/path/(foo,bar)" title="http://example.com/path/(foo,bar)" rel="nofollow external">http://example.com/path/(foo,bar)</a>?'),
                     array('Unbalanced too (e.g. http://example.com/path/((((foo)/bar)?',
                           'Unbalanced too (e.g. <a href="http://example.com/path/((((foo)/bar)" title="http://example.com/path/((((foo)/bar)" rel="nofollow external">http://example.com/path/((((foo)/bar)</a>?'),
                     array('Unbalanced too (e.g. http://example.com/path/(foo))))/bar)?',
                           'Unbalanced too (e.g. <a href="http://example.com/path/(foo))))/bar" title="http://example.com/path/(foo))))/bar" rel="nofollow external">http://example.com/path/(foo))))/bar</a>)?'),
                     array('Unbalanced too (e.g. http://example.com/path/foo/((((bar)?',
                           'Unbalanced too (e.g. <a href="http://example.com/path/foo/((((bar)" title="http://example.com/path/foo/((((bar)" rel="nofollow external">http://example.com/path/foo/((((bar)</a>?'),
                     array('Unbalanced too (e.g. http://example.com/path/foo/(bar))))?',
                           'Unbalanced too (e.g. <a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">http://example.com/path/foo/(bar)</a>)))?'),
                     array('example.com',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>'),
                     array('example.org',
                           '<a href="http://example.org/" title="http://example.org/" rel="nofollow external">example.org</a>'),
                     array('example.co.uk',
                           '<a href="http://example.co.uk/" title="http://example.co.uk/" rel="nofollow external">example.co.uk</a>'),
                     array('www.example.co.uk',
                           '<a href="http://www.example.co.uk/" title="http://www.example.co.uk/" rel="nofollow external">www.example.co.uk</a>'),
                     array('farm1.images.example.co.uk',
                           '<a href="http://farm1.images.example.co.uk/" title="http://farm1.images.example.co.uk/" rel="nofollow external">farm1.images.example.co.uk</a>'),
                     array('example.museum',
                           '<a href="http://example.museum/" title="http://example.museum/" rel="nofollow external">example.museum</a>'),
                     array('example.travel',
                           '<a href="http://example.travel/" title="http://example.travel/" rel="nofollow external">example.travel</a>'),
                     array('example.com.',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>.'),
                     array('example.com?',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>?'),
                     array('example.com!',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>!'),
                     array('example.com,',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>,'),
                     array('example.com;',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>;'),
                     array('example.com:',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>:'),
                     array('\'example.com\'',
                           '\'<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>\''),
                     array('"example.com"',
                           '&quot;<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>&quot;'),
                     array('example.com',
                           '<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>'),
                     array('(example.com)',
                           '(<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>)'),
                     array('[example.com]',
                           '[<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>]'),
                     array('<example.com>',
                           '&lt;<a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>&gt;'),
                     array('Hey, check out my cool site example.com okay?',
                           'Hey, check out my cool site <a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a> okay?'),
                     array('Hey, check out my cool site example.com.I made it.',
                           'Hey, check out my cool site <a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>.I made it.'),
                     array('Hey, check out my cool site example.com.Funny thing...',
                           'Hey, check out my cool site <a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>.Funny thing...'),
                     array('Hey, check out my cool site example.com.You will love it.',
                           'Hey, check out my cool site <a href="http://example.com/" title="http://example.com/" rel="nofollow external">example.com</a>.You will love it.'),
                     array('What about parens (e.g. example.com/path/foo/(bar))?',
                           'What about parens (e.g. <a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">example.com/path/foo/(bar)</a>)?'),
                     array('What about parens (e.g. example.com/path/foo/(bar)?',
                           'What about parens (e.g. <a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">example.com/path/foo/(bar)</a>?'),
                     array('What about parens (e.g. example.com/path/foo/(bar).)?',
                           'What about parens (e.g. <a href="http://example.com/path/foo/(bar)" title="http://example.com/path/foo/(bar)" rel="nofollow external">example.com/path/foo/(bar)</a>.)?'),
                     array('What about parens (e.g. example.com/path/(foo,bar)?',
                           'What about parens (e.g. <a href="http://example.com/path/(foo,bar)" title="http://example.com/path/(foo,bar)" rel="nofollow external">example.com/path/(foo,bar)</a>?'),
                     array('file.ext',
                           'file.ext'),
                     array('file.html',
                           'file.html'),
                     array('file.php',
                           'file.php'),

                     // scheme-less HTTP URLs with @ in the path: http://status.net/open-source/issues/2248
                     array('http://flickr.com/photos/34807140@N05/3838905434',
                           '<a href="http://flickr.com/photos/34807140@N05/3838905434" title="http://flickr.com/photos/34807140@N05/3838905434" class="attachment thumbnail" id="attachment-XXX" rel="nofollow external">http://flickr.com/photos/34807140@N05/3838905434</a>'),
                     array('flickr.com/photos/34807140@N05/3838905434',
                           '<a href="http://flickr.com/photos/34807140@N05/3838905434" title="http://flickr.com/photos/34807140@N05/3838905434" class="attachment thumbnail" id="attachment-XXX" rel="nofollow external">flickr.com/photos/34807140@N05/3838905434</a>'),
                     );
    }
}

