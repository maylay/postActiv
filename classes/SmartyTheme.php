<?php
/* ============================================================================
 * Title: SmartyTheme
 * Class abstraction of a Smarty theme
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
 * Class abstraction of a Smarty theme
 *
 * Copyright on this file belongs explicitly to Maiyannah Bishop.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */
 
// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.


// ============================================================================
// Class: SmartyTheme
// Superclass representing a Smarty-based theme
//
// For now, since I'm doing this as a proof of concept back port
// I'm mostly concerned with seeing that this actually works.
// As such a lot is missing and a lot is hardcoded
//
// Properties:
// o $name        - Name of template we're using
// o $templates   - Array to hold template locations
// o $stylesheets - Array to hold stylesheets
// o $scripts     - Array to hold scripts
class SmartyTheme {
   protected $Smarty;   // Smarty template system class object
   var $name;           // Name of template we're using
   var $templates;      // Array to hold template locations
   var $stylesheets;    // Array to hold stylesheets
   var $scripts;        // Array to hold scripts


   // -------------------------------------------------------------------------
   // Function: __construct
   // Bootstrap the Smarty instance for this theme
   public function __construct($name) {
      try {
         // Load a Smarty processor instance for this Template
         require_once(INSTALLDIR . "/extlib/Smarty/Smarty.class.php");
         $this->Smarty = new Smarty();
         $this->Smarty->setCacheDir(INSTALLDIR . '/extlib/Smarty/cache');
         $this->Name = $name;

         // Populate the "Templates" array with system short_aliases
         $this->instantiateTemplates();

         // If we've got this far, we're good!
         return TRUE;
      } catch (exception $error) {
         die("Error constructing SmartyTheme class for " . $name . ": " . $error . PHP_EOL);
      }
   }

   // -------------------------------------------------------------------------
   // Function: instantiateTemplates()
   // Populate the Templates array of this theme with the system short_aliases
   private function instantiateTemplates() {
      $this->Templates["single_notice"] = "";
   }

   // -------------------------------------------------------------------------
   // Function: mapTemplatesDir
   // Set the directory the theme's Smarty instance is pulling templates from.
   //
   // Parameters:
   // o url - URL of the Smarty templates directory
   public function mapTemplatesDir($url) {
      try {
         $this->Smarty->setTemplatesDir($url);
         return TRUE;
      } catch (exception $error) {
         common_debug("Error setting the Smarty template dir: " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // Function: mapCompileDir
   // Set the directory the theme's Smarty instance is ompiling templates to.
   //
   // Parameters:
   // o url - URL of the Smarty template compile directory.
   public function mapCompileDir($url) {
      try {
         $this->Smarty->setCompileDir($url);
         return TRUE;
      } catch (exception $error) {
         common_debug("Error setting the Smarty compile dir: " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // Function: mapTemplate
   // Save the URL for a given short_alias for a template, in our internal
   // Templates array.
   //
   // Parameters:
   // o short_alias - alias of the template we are mapping
   // o url         - url we are mapping the template to
   //
   // Returns:
   // o boolean true/false for success
   public function mapTemplate($short_alias, $url) {
      try {
         $this->Templates[$short_alias] = $url;
         return TRUE;
      } catch (exception $error) {
         common_debug("Error mapping Template in SmartyTheme::mapTemplate(" . $short_alias . "," . $url . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // Function: retrieveTemplate
   // Retrieve the template URL for the given short alias.
   //
   // If you just want to do this to display a template, then use 
   // displayTemplate instead!
   //
   // Parameters:
   // o short_alias - alias of the template we're looking up
   //
   // Returns:
   // o the URL on success, FALSE on failure.
   public function retrieveTemplate($short_alias) {
      try {
         if (array_key_exists($short_alias, $this->Templates)) {
            return $this->Templates[$short_alias];
         } else {
            return FALSE;
         }
      } catch (exception $error) {
         common_debug("Error retrieving Template in SmartyTheme::retrieveTemplate(" . $short_alias . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // Function: displayTemplate($short_alias)
   // Have Smarty display the template with the given short_alias.  Make sure
   // you assign the appropriate template variables first! You can use
   // assignVariable($var, $value) for this.
   //
   // Parameters:
   // o short_alias - alias of the template we are rendering
   //
   // Returns:
   // o TRUE if successful, FALSE if not.
   public function displayTemplate($short_alias) {
      try {
         if (array_key_exists($short_alias, $this->Templates)) {
            $this->Smarty->display($this->Templates[$short_alias]);
            return TRUE;
         } else {
            return FALSE;
         }
      } catch (exception $error) {
         common_debug("Error displaying Template in SmartyTheme::displayTemplate(" . $short_alias . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // Function: assignVariable($var, $value)
   // Interface to the internal Smarty instance to assign variables used in
   // templates.
   //
   // Returns:
   // o TRUE if successful, FALSE if not.
   public function assignVariable($var, $value) {
      try {
         $this->Smarty->assign($var,$value);
         return TRUE;
      } catch (exception $error) {
         common_debug("Error assigning Smarty variables in SmartyTheme::assignVariable(" . $var . "," . $value . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }
}


// END OF FILE
// ============================================================================
 ?>