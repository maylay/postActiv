<?php
/* ============================================================================
 * Title: StoreRemoteMedia
 * Caches remote images linked in notices
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
 * Caches remote images linked in notices
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * o Saul St John <saul.stjohn@gmail.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('GNUSOCIAL')) { exit(1); }

// ----------------------------------------------------------------------------
// Class: StoreRemoteMediaPlugin
// Main StoreRemoteMedia plugin class
// 
// FIXME:
// o To support remote video/whatever files, this plugin needs reworking.
//
// Variables:
// o $domain_whitelist - array of domains to allow media from
// o $append_whitelist - additional domains to allow media from
// o $check_whitelist - whether to enforce a whitelist
// o $domain_blacklist - array of domains to block media from
// o $check_blacklist - whether to enforce a blacklist
// o $max_image_bytes - maximum image size in bytes
// o $imgData - raw image data
class StoreRemoteMediaPlugin extends Plugin
{
    // settings which can be set in config.php with addPlugin('Oembed', array('param'=>'value', ...));
    // WARNING, these are _regexps_ (slashes added later). Always escape your dots and end your strings
    public $domain_whitelist = array(       // hostname => service provider
                                    '^i\d*\.ytimg\.com$' => 'YouTube',
                                    '^i\d*\.vimeocdn\.com$' => 'Vimeo',
                                    );
    public $append_whitelist = array(); // fill this array as domain_whitelist to add more trusted sources
    public $check_whitelist  = false;    // security/abuse precaution

    public $domain_blacklist = array();
    public $check_blacklist = false;

    public $max_image_bytes = 10485760;  // 10MiB max image size by default

    protected $imgData = array();

    // ------------------------------------------------------------------------
    // Function: initalize
    // Initialize the plugin
    public function initialize()
    {
        parent::initialize();

        $this->domain_whitelist = array_merge($this->domain_whitelist, $this->append_whitelist);
    }

    // ------------------------------------------------------------------------
    // Function: onStartFileSaveNew
    // Save embedding information for a File, if applicable.
    //
    // Normally this event is called through File::saveNew()
    //
    // Parameters:
    // o File $file - The about-to-be-inserted File object.
    //
    // Returns:
    // o boolean success
    public function onStartFileSaveNew(File &$file)
    {
        // save given URL as title if it's a media file this plugin understands
        // which will make it shown in the AttachmentList widgets

        if (isset($file->title) && strlen($file->title)>0) {
            // Title is already set
            return true;
        }
        if (!isset($file->mimetype)) {
            // Unknown mimetype, it's not our job to figure out what it is.
            return true;
        }
        switch (common_get_mime_media($file->mimetype)) {
        case 'image':
            // Just to set something for now at least...
            //$file->title = $file->mimetype;
            break;
        }
        
        return true;
    }

    // ------------------------------------------------------------------------
    // Function: onCreateFileImageThumbnailSource
    // Create a thumbnail from remote media, adhering to applicable whitelists
    // and blacklists.
    //
    // Parameters:
    // o File $file - A File object
    // o string $imgPath - where to save the image file
    // o string $media - media type
    //
    // Returns:
    // o boolean true on success; false otherwise
    public function onCreateFileImageThumbnailSource(File $file, &$imgPath, $media=null)
    {
        // If we are on a private node, we won't do any remote calls (just as a precaution until
        // we can configure this from config.php for the private nodes)
        if (common_config('site', 'private')) {
            return true;
        }

        if ($media !== 'image') {
            return true;
        }

        // If there is a local filename, it is either a local file already or has already been downloaded.
        if (!empty($file->filename)) {
            return true;
        }

        $remoteUrl = $file->getUrl();

        if (!$this->checkWhiteList($remoteUrl) ||
            !$this->checkBlackList($remoteUrl)) {
		    return true;
        }

        try {
            /*
            $http = new HTTPClient();
            common_debug(sprintf('Performing HEAD request for remote file id==%u to avoid unnecessarily downloading too large files. URL: %s', $file->getID(), $remoteUrl));
            $head = $http->head($remoteUrl);
            $remoteUrl = $head->getEffectiveUrl();   // to avoid going through redirects again
            if (!$this->checkBlackList($remoteUrl)) {
                common_log(LOG_WARN, sprintf('%s: Non-blacklisted URL %s redirected to blacklisted URL %s', __CLASS__, $file->getUrl(), $remoteUrl));
                return true;
            }

            $headers = $head->getHeader();
            $filesize = isset($headers['content-length']) ? $headers['content-length'] : null;
            */
            $filesize = $file->getSize();
            if (empty($filesize)) {
                // file size not specified on remote server
                common_debug(sprintf('%s: Ignoring remote media because we did not get a content length for file id==%u', __CLASS__, $file->getID()));
                return true;
            } elseif ($filesize > $this->max_image_bytes) {
                //FIXME: When we perhaps start fetching videos etc. we'll need to differentiate max_image_bytes from that...
                // file too big according to plugin configuration
                common_debug(sprintf('%s: Skipping remote media because content length (%u) is larger than plugin configured max_image_bytes (%u) for file id==%u', __CLASS__, intval($filesize), $this->max_image_bytes, $file->getID()));
                return true;
            } elseif ($filesize > common_config('attachments', 'file_quota')) {
                // file too big according to site configuration
                common_debug(sprintf('%s: Skipping remote media because content length (%u) is larger than file_quota (%u) for file id==%u', __CLASS__, intval($filesize), common_config('attachments', 'file_quota'), $file->getID()));
                return true;
            }

            // Then we download the file to memory and test whether it's actually an image file
            common_debug(sprintf('Downloading remote file id==%u (should be size %u) with effective URL: %s', $file->getID(), $filesize, _ve($remoteUrl)));
            $imgData = HTTPClient::quickGet($remoteUrl);
        } catch (HTTP_Request2_ConnectionException $e) {
            common_log(LOG_ERR, __CLASS__.': '._ve(get_class($e)).' on URL: '._ve($file->getUrl()).' threw exception: '.$e->getMessage());
            return true;
        }
        $info = @getimagesizefromstring($imgData);
        if ($info === false) {
            throw new UnsupportedMediaException(_('Remote file format was not identified as an image.'), $remoteUrl);
        } elseif (!$info[0] || !$info[1]) {
            throw new UnsupportedMediaException(_('Image file had impossible geometry (0 width or height)'));
        }

        $filehash = hash(File::FILEHASH_ALG, $imgData);
        try {
            // Exception will be thrown before $file is set to anything, so old $file value will be kept
            $file = File::getByHash($filehash);

            //FIXME: Add some code so we don't have to store duplicate File rows for same hash files.
        } catch (NoResultException $e) {
            $filename = $filehash . '.' . common_supported_mime_to_ext($info['mime']);
            $fullpath = File::path($filename);

            // Write the file to disk if it doesn't exist yet. Throw Exception on failure.
            if (!file_exists($fullpath) && file_put_contents($fullpath, $imgData) === false) {
                throw new ServerException(_('Could not write downloaded file to disk.'));
            }

            // Updated our database for the file record
            $orig = clone($file);
            $file->filehash = $filehash;
            $file->filename = $filename;
            $file->width = $info[0];    // array indexes documented on php.net:
            $file->height = $info[1];   // https://php.net/manual/en/function.getimagesize.php
            // Throws exception on failure.
            $file->updateWithKeys($orig);
        }
        // Get rid of the file from memory
        unset($imgData);

        $imgPath = $file->getPath();

        return false;
    }

    // ------------------------------------------------------------------------
    // Function: checkBlackList
    // Check if a given url matches one of the domains in the blacklist
    // if $check_blacklist is set to true, otherwise just allow any url.
    //
    // Parameters:
    // o string $url - url to check
    //
    // Returns:
    // o boolean true if given url passes blacklist check
    protected function checkBlackList($url)
    {
        if (!$this->check_blacklist) {
            return true;
        }
        $host = parse_url($url, PHP_URL_HOST);
        foreach ($this->domain_blacklist as $regex => $provider) {
            if (preg_match("/$regex/", $host)) {
                return false;
            }
        }

        return true;
    }

    // ------------------------------------------------------------------------
    // Function: checkWhiteList
    // Check if a given url mathes one of the domains in the whitelist
    // if $check_whitelist is set to true, otherwise just allow any url.
    //
    // Parameters:
    // o string $url - url to check
    //
    // Returns:
    // o boolean true if given url passes whitelist check
    protected function checkWhiteList($url)
    {
        if (!$this->check_whitelist) {
            return true;
        }
        $host = parse_url($url, PHP_URL_HOST);
        foreach ($this->domain_whitelist as $regex => $provider) {
            if (preg_match("/$regex/", $host)) {
                return true;
            }
        }

        return false;
    }

    // ------------------------------------------------------------------------
    // Function: onPluginVersion
    // Modify a versions array to provide the plugin version info.
    //
    // Parameters:
    // o array $versions - versions array to modify
    //
    // Returns:
    // o boolean true
    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'StoreRemoteMedia',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'https://gnu.io/',
                            'description' =>
                            // TRANS: Plugin description.
                            _m('Plugin for downloading remotely attached files to local server.'));
        return true;
    }
}
// END OF FILE
// ============================================================================
