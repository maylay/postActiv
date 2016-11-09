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
 * An activity
 *
 * @category  Feed
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Zach Copley
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

// XXX: Arg! This wouldn't be necessary if we used Avatars conistently
class AvatarLink
{
    public $url;
    public $type;
    public $size;
    public $width;
    public $height;

    function __construct($element=null)
    {
        if ($element) {
            // @fixme use correct namespaces
            $this->url = $element->getAttribute('href');
            $this->type = $element->getAttribute('type');
            $width = $element->getAttribute('media:width');
            if ($width != null) {
                $this->width = intval($width);
            }
            $height = $element->getAttribute('media:height');
            if ($height != null) {
                $this->height = intval($height);
            }
        }
    }

    static function fromAvatar(Avatar $avatar)
    {
        $alink = new AvatarLink();
        $alink->type   = $avatar->mediatype;
        $alink->height = $avatar->height;
        $alink->width  = $avatar->width;
        $alink->url    = $avatar->displayUrl();
        return $alink;
    }

    static function fromFilename($filename, $size)
    {
        $alink = new AvatarLink();
        $alink->url    = $filename;
        $alink->height = $size;
        $alink->width  = $size;
        if (!empty($filename)) {
            $alink->type   = self::mediatype($filename);
        } else {
            $alink->url    = User_group::defaultLogo($size);
            $alink->type   = 'image/png';
        }
        return $alink;
    }

    // yuck!
    static function mediatype($filename) {
        $parts = explode('.', $filename);
        $ext = strtolower(end($parts));
        if ($ext == 'jpeg') {
            $ext = 'jpg';
        }
        // hope we don't support any others
        $types = array('png', 'gif', 'jpg', 'jpeg');
        if (in_array($ext, $types)) {
            return 'image/' . $ext;
        }
        return null;
    }
}
?>