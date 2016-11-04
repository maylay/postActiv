<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * A class for representing links in JSON Activities
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

class ActivityStreamsLink
{
    private $linkDict;

    function __construct($url = null, $rel = null, $mediaType = null)
    {
        // links MUST have a URL
        if (empty($url)) {
            throw new Exception('Links must have a URL.');
        }

        $this->linkDict = array(
            'url'   => $url,
            'rel'   => $rel,      // extension
            'type'  => $mediaType // extension
        );
    }

    function asArray()
    {
        return array_filter($this->linkDict);
    }
}
?>