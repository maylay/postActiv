<?php
/* ============================================================================
 * Title: DocFile
 * Utility for finding and parsing documentation files
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
 * Utility for finding and parsing documentation files
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Bob Mottram <bob@freedombone.net>
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */


if (!defined('POSTACTIV')) { exit(1); }

// ----------------------------------------------------------------------------
// Class: DocFile
// Utility for finding and parsing documentation files
//
// Variables:
// o protected $filename
// o protected $contents
class DocFile
{
   protected $filename;
   protected $contents;


   // -------------------------------------------------------------------------
   // Function: __construct
   // Class constructor.  Sets up the filename protected variable.
   function __construct($filename)
   {
      $this->filename = $filename;
   }


   // -------------------------------------------------------------------------
   // Function: forTitle
   // Look up the documentation file by a title in the given paths.
   // Spins off the StartDocForTitle and EndDocForTitle events before and
   // after the lookup, but importantly, both are before any returns, for
   // obvious control path reasons.
   //
   // Parameters:
   // o string title - filename of the docfile to look up
   // o array paths  - paths to look in
   //
   // Returns:
   // o DocFile of that file if it finds a file, null if there is no file found.
   static function forTitle($title, $paths)
   {
      // TODO: This is a dirty fix.  Find out why and where we're passed bad data instead.
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


   // -------------------------------------------------------------------------
   // Function: toHTML
   // Parse the markup in a docfile to HTML equivalents.
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


   // ------------------------------------------------------------------------
   // Function: defaultPaths
   // Returns an array containing the possible paths for the in-UI documentation
   // sources
   static function defaultPaths()
   {
      $paths = array(INSTALLDIR.'/media/doc-src/',
                     INSTALLDIR.'/local/doc-src/',
                     INSTALLDIR.'/doc-src/');

      $site = postActiv::currentSite();

      if (!empty($site)) {
         array_unshift($paths, INSTALLDIR.'/local/doc-src/'.$site.'/');
      }

      $doc_src = common_config("site", "doc_path");
      if ($doc_src) {
         array_unshift($paths, $doc_src);
      }

      return $paths;
   }

   // -------------------------------------------------------------------------
   // Function: mailPaths
   // As <defaultPaths> but for mail sources (template for invites, etc)
   static function mailPaths()
   {
      $paths = array(INSTALLDIR.'/local/mail-src/',
                     INSTALLDIR.'/media/mail-src/',
                     INSTALLDIR.'/mail-src/');

      $site = postActiv::currentSite();

      if (!empty($site)) {
         array_unshift($paths, INSTALLDIR.'/local/mail-src/'.$site.'/');
         array_unshift($paths, INSTALLDIR.'/media/mail-src/'.$site.'/');
      }

      // Prefer site vars over subsection
      if (common_config("site", "doc_path")) {
         $mail_src = common_config("site", "mail_path");
      } elseif(common_config("mail", "templates_path")) {
         $mail_src = common_config("mail", "templates_path");      
      }
      if ($mail_src) {
         array_unshift($paths, $mail_src);
      }

      return $paths;
   }


   // -------------------------------------------------------------------------
   // Function: negotiateLanguage
   // Figure out what language code we're using for a docfile
   //
   // Parameters:
   // o filenames       - files we're looking up the language for
   // o defaultFilename - default filename to use if we don't find something,
   //                     defaults to null
   //
   // Returns:
   // o string filename or null if not found
   //
   // Todo:
   // o Do this better
   static function negotiateLanguage($filenames, $defaultFilename=null)
   {
      $langcode = common_language();
      foreach ($filenames as $filename) {
         if (preg_match('/\.'.$langcode.'$/', $filename)) {
                return $filename;
         }
      }
      return $defaultFilename;
   }
}

// END OF FILE
// ============================================================================
?>