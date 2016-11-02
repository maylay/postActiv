<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unit test for media files code
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
 * @author    Brenda Wallace <shiny@cpan.org>
 * @author    Zach Copley <zcopley@danube.local>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Bhuvan Krishna <bhuvan@swecha.net>
 * @author    Bob Mottram <bob@robotics.co.uk>
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

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));
define('POSTACTIV', true);
define('GNUSOCIAL', true);
define('STATUSNET', true);  // compatibility

require_once INSTALLDIR . '/lib/common.php';

class MediaFileTest extends PHPUnit_Framework_TestCase
{

    public function setup()
    {
        $this->old_attachments_supported = common_config('attachments', 'supported');
        $GLOBALS['config']['attachments']['supported'] = true;
    }

    public function tearDown()
    {
        $GLOBALS['config']['attachments']['supported'] = $this->old_attachments_supported;
    }

    /**
     * @dataProvider fileTypeCases
     *
     */
    public function testMimeType($filename, $expectedType)
    {
        if (!file_exists($filename)) {
            throw new Exception("WTF? $filename test file missing");
        }

        $type = MediaFile::getUploadedMimeType($filename, basename($filename));
        $this->assertEquals($expectedType, $type);
    }

    /**
     * @dataProvider fileTypeCases
     *
     */
    public function testUploadedMimeType($filename, $expectedType)
    {
        if (!file_exists($filename)) {
            throw new Exception("WTF? $filename test file missing");
        }
        $tmp = tmpfile();
        fwrite($tmp, file_get_contents($filename));

        $tmp_metadata = stream_get_meta_data($tmp);
        $type = MediaFile::getUploadedMimeType($tmp_metadata['uri'], basename($filename));
        $this->assertEquals($expectedType, $type);
    }

    static public function fileTypeCases()
    {
        $base = dirname(__FILE__);
        $dir = "$base/sample-uploads";
        $files = array(
            "image.png" => "image/png",
            "image.gif" => "image/gif",
            "image.jpg" => "image/jpeg",
            "image.jpeg" => "image/jpeg",
        
            "office.pdf" => "application/pdf",
            
            "wordproc.odt" => "application/vnd.oasis.opendocument.text",
            "wordproc.ott" => "application/vnd.oasis.opendocument.text-template",
            "wordproc.doc" => "application/msword",
            "wordproc.docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "wordproc.rtf" => "text/rtf",
            
            "spreadsheet.ods" => "application/vnd.oasis.opendocument.spreadsheet",
            "spreadsheet.ots" => "application/vnd.oasis.opendocument.spreadsheet-template",
            "spreadsheet.xls" => "application/vnd.ms-excel",
            "spreadsheet.xlt" => "application/vnd.ms-excel",
            "spreadsheet.xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            
            "presentation.odp" => "application/vnd.oasis.opendocument.presentation",
            "presentation.otp" => "application/vnd.oasis.opendocument.presentation-template",
            "presentation.ppt" => "application/vnd.ms-powerpoint",
            "presentation.pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        );

        $dataset = array();
        foreach ($files as $file => $type) {
            $dataset[] = array("$dir/$file", $type);
        }
        return $dataset;
    }

}

