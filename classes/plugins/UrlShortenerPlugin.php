<?php
/* ============================================================================
 * Title: URLShortenerPlugin
 * Superclass for plugins that do URL shortening
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
 *
 * Derived from code copyright various sources:
 * o GNU Social (C) 2013-2016, Free Software Foundation, Inc
 * o StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
 * License:
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
 * <https://www.gnu.org/licenses/agpl.html>
 * ----------------------------------------------------------------------------
 * About:
 * Superclass for plugins that do URL shortening
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */
 
// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Superclass for plugins that do URL shortening
 */
abstract class UrlShortenerPlugin extends Plugin
{
    public $shortenerName;
    public $freeService = false;

    // Url Shortener plugins should implement some (or all)
    // of these methods

    /**
     * Make an URL shorter.
     *
     * @param string $url URL to shorten
     *
     * @return string shortened version of the url, or null on failure
     */

    protected abstract function shorten($url);

    /**
     * Utility to get the data at an URL
     *
     * @param string $url URL to fetch
     *
     * @return string response body
     *
     * @todo rename to code-standard camelCase httpGet()
     */

    protected function http_get($url)
    {
        $request  = HTTPClient::start();
        $response = $request->get($url);
        return $response->getBody();
    }

    /**
     * Utility to post a request and get a response URL
     *
     * @param string $url  URL to fetch
     * @param array  $data post parameters
     *
     * @return string response body
     *
     * @todo rename to code-standard httpPost()
     */

    protected function http_post($url, $data)
    {
        $request  = HTTPClient::start();
        $response = $request->post($url, null, $data);
        return $response->getBody();
    }

    // Hook handlers

    /**
     * Called when all plugins have been initialized
     *
     * @return boolean hook value
     */

    function onInitializePlugin()
    {
        if (!isset($this->shortenerName)) {
            throw new Exception("must specify a shortenerName");
        }
        return true;
    }

    /**
     * Called when a showing the URL shortener drop-down box
     *
     * Properties of the shortening service currently only
     * include whether it's a free service.
     *
     * @param array &$shorteners array mapping shortener name to properties
     *
     * @return boolean hook value
     */

    function onGetUrlShorteners(&$shorteners)
    {
        $shorteners[$this->shortenerName] =
          array('freeService' => $this->freeService);
        return true;
    }

    /**
     * Called to shorten an URL
     *
     * @param string $url           URL to shorten
     * @param string $shortenerName Shortening service. Don't handle if it's
     *                              not you!
     * @param string &$shortenedUrl URL after shortening; out param.
     *
     * @return boolean hook value
     */

    function onStartShortenUrl($url, $shortenerName, &$shortenedUrl)
    {
        if ($shortenerName == $this->shortenerName) {
            $result = $this->shorten($url);
            if (isset($result) && $result != null && $result !== false) {
                $shortenedUrl = $result;
                common_log(LOG_INFO,
                           __CLASS__ . ": $this->shortenerName ".
                           "shortened $url to $shortenedUrl");
                return false;
            }
        }
        return true;
    }
}

// END OF FILE
// ============================================================================
?>