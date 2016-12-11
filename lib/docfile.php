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
 * Utility for finding and parsing documentation files
 *
 * @category  Documentation
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Utility for finding and parsing documentation files
 */

class DocFile
{
    protected $filename;
    protected $contents;

    function __construct($filename)
    {
        $this->filename = $filename;
    }

    static function forTitle($title, $paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $filename = null;

        if (Event::handle('StartDocFileForTitle', array($title, &$paths, &$filename))) {

            foreach ($paths as $path) {

                $def = $path.'/'.$title;

                if (!file_exists($def)) {
                    $def = null;
                }

                $lang = glob($path.'/'.$title.'.*');

                if ($lang === false) {
                    $lang = array();
                }

                if (!empty($lang) || !empty($def)) {
                    $filename = self::negotiateLanguage($lang, $def);
                    break;
                }
            }

            Event::handle('EndDocFileForTitle', array($title, $paths, &$filename));
        }

        if (empty($filename)) {
            return null;
        } else {
            return new DocFile($filename);
        }
    }

    function toHTML($args=null)
    {
        if (is_null($args)) {
            $args = array();
        }

        if (empty($this->contents)) {
            $this->contents = file_get_contents($this->filename);
        }

        return common_markup_to_html($this->contents, $args);
    }

    static function defaultPaths()
    {
        $paths = array(INSTALLDIR.'/local/doc-src/',
                       INSTALLDIR.'/doc-src/');

        $site = postActiv::currentSite();

        if (!empty($site)) {
            array_unshift($paths, INSTALLDIR.'/local/doc-src/'.$site.'/');
        }

        return $paths;
    }

    static function mailPaths()
    {
        $paths = array(INSTALLDIR.'/local/mail-src/',
                       INSTALLDIR.'/mail-src/');

        $site = postActiv::currentSite();

        if (!empty($site)) {
            array_unshift($paths, INSTALLDIR.'/local/mail-src/'.$site.'/');
        }

        return $paths;
    }

    static function negotiateLanguage($filenames, $defaultFilename=null)
    {
        // XXX: do this better

        $langcode = common_language();

        foreach ($filenames as $filename) {
            if (preg_match('/\.'.$langcode.'$/', $filename)) {
                return $filename;
            }
        }

        return $defaultFilename;
    }
}
?>
