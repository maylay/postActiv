<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * A class for representing MediaLinks in JSON Activities
 *
 * @category Feed
 * @package  StatusNet
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */

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
