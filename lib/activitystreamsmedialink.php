<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
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
 * A class for representing MediaLinks in JSON Activities
 *
 * PHP version 5 
 *
 * @category Feed
 * @package  StatusNet
 * @author   Zach Copley <zach@status.net>
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link     http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

class ActivityStreamsMediaLink extends ActivityStreamsLink
{
    private $linkDict;

    function __construct(
        $url       = null,
        $width     = null,
        $height    = null,
        $mediaType = null, // extension
        $rel       = null, // extension
        $duration  = null
    )
    {
        parent::__construct($url, $rel, $mediaType);
        $this->linkDict = array(
            'width'      => intval($width),
            'height'     => intval($height),
            'duration'   => intval($duration)
        );
    }

    function asArray()
    {
        return array_merge(
            parent::asArray(),
            array_filter($this->linkDict)
        );
    }
}
?>