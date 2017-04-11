<?php
/* ============================================================================
 * Title: GetFile
 * Return a requested file
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2017, Maiyannah Bishop
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
 * Return a requested file
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Craig Andrews <candrews@integralblue.com>
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Zach Copley
 * o Mikael Nordfeldth <mmn@hethane.se>
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

class GetfileAction extends Action
{
    /**
     * Path of file to return
     */
    var $path = null;

    /**
     * Get file name
     *
     * @param array $args $_REQUEST array
     *
     * @return success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $filename = $this->trimmed('filename');
        $path = null;

        if ($filename && File::validFilename($filename)) {
            $path = File::path($filename);
        }

        if (empty($path) or !file_exists($path)) {
            // TRANS: Client error displayed when requesting a non-existent file.
            $this->clientError(_('No such file.'), 404);
        }
        if (!is_readable($path)) {
            // TRANS: Client error displayed when requesting a file without having read access to it.
            $this->clientError(_('Cannot read file.'), 403);
        }

        $this->path = $path;
        return true;
    }

    /**
     * Is this page read-only?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    /**
     * Last-modified date for file
     *
     * @return int last-modified date as unix timestamp
     */
    function lastModified()
    {
        if (common_config('site', 'use_x_sendfile')) {
            return null;
        }

        return filemtime($this->path);
    }

    /**
     * etag for file
     *
     * This returns the same data (inode, size, mtime) as Apache would,
     * but in decimal instead of hex.
     *
     * @return string etag http header
     */
    function etag()
    {
        if (common_config('site', 'use_x_sendfile')) {
            return null;
        }

        $cache = Cache::instance();
        if($cache) {
            $key = Cache::key('attachments:etag:' . $this->path);
            $etag = $cache->get($key);
            if($etag === false) {
                $etag = crc32(file_get_contents($this->path));
                $cache->set($key,$etag);
            }
            return $etag;
        }

        $stat = stat($this->path);
        return '"' . $stat['ino'] . '-' . $stat['size'] . '-' . $stat['mtime'] . '"';
    }

    /**
     * Handle input, produce output
     *
     * @return void
     */
    protected function handle()
    {
        // undo headers set by PHP sessions
        $sec = session_cache_expire() * 60;
        header('Expires: ' . date(DATE_RFC1123, time() + $sec));
        header('Cache-Control: max-age=' . $sec);

        parent::handle();

        $path = $this->path;

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        header('Content-Type: ' . $finfo->file($path));

        if (common_config('site', 'use_x_sendfile')) {
            header('X-Sendfile: ' . $path);
        } else {
            header('Content-Length: ' . filesize($path));
            readfile($path);
        }
    }
}

// END OF FILE
// ============================================================================
?>