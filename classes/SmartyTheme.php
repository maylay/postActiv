<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 * PHP version 5 
 *
 * Class abstraction of a Smarty theme
 *
 * @category  UI
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */


// For now, since I'm doing this as a proof of concept back port
// I'm mostly concerned with seeing that this actually works.
// As such a lot is missing and a lot is hardcoded
public class SmartyTheme {
   protected Smarty;   // Smarty template system class object
   var Name;           // Name of template we're using
   var Templates;      // Array to hold template locations
   var Stylesheets;    // Array to hold stylesheets
   var Scripts;        // Array to hold scripts

   public function __construct($name) {
      try
      {
         require_once(INSTALL_DIR . "/extlib/Smarty/Autoloader.php");
         $this->Smarty = new Smarty();

         $this->Smarty->setCacheDir(INSTALL_DIR . '/extlib/Smarty/cache');
         $this->Smarty->setConfigDir(INSTALL_DIR . '/extlib/Smarty/configs');

         $this->Name = $name;
         require(INSTALL_DIR . "/templates/" . $this->Name . "/manifest.php");
         return TRUE;
      }
      catch (exception $error)
      {
         common_log("Error constructing SmartyTheme class for " . $name . ": " . $error . PHP_EOL);
         return FALSE;
      }
   }
   
   // -------------------------------------------------------------------------
   // mapTemplatesDir($url)
   //    Public interface to set the directory the theme's Smarty instance is
   //    pulling templates from.
   public function mapTemplatesDir($url)
   {
      try
      {
         $this->Smarty->setTemplatesDir($url);
      }
      catch (exception $error)
      {
         common_log("Error setting the Smarty template dir: " . $error . PHP_EOL);
      }
   }

   // -------------------------------------------------------------------------
   // mapCompileDir($url)
   //    Public interface to set the directory the theme's Smarty instance is
   //    compiling templates to.
   public function mapCompileDir($url)
   {
      try
      {
         $this->Smarty->setCompileDir($url);
      }
      catch (exception $error)
      {
         common_log("Error setting the Smarty compile dir: " . $error . PHP_EOL);
      }
   }

   // -------------------------------------------------------------------------
   // mapTemplate($short_alias, $url)
   //    Save the URL for a given short_alias for a template, in our internal
   //    Templates array.
   //    Returns TRUE on success, FALSE on failure.
   public function mapTemplate($short_alias, $url)
   {
      try
      {
         $this->Templates[$short_alias] = $url;
         return TRUE;
      }
      catch (exception $error)
      {
         common_log("Error mapping Template in SmartyTheme::mapTemplate(" . $short_alias . "," . $url . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // retrieveTemplate($short_alias)
   //    Retrieve the template URL for the given short alias
   //    Returns the URL on success, FALSE on failure.
   //
   //    If you just want to do this to display a template, then use 
   //    displayTemplate instead!
   public function retrieveTemplate($short_alias)
   {
      try
      {
         if (array_key_exists($short_alias, $this->Templates))
         {
            return $this->Templates[$short_alias];
         }
         else
         {
            return FALSE;
         }
      }
      catch (exception $error)
      {
         common_log("Error retrieving Template in SmartyTheme::retrieveTemplate(" . $short_alias . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // displayTemplate($short_alias)
   //    Have Smarty display the template with the given short_alias.  Make 
   //    sure you assign the appropriate template variables first!
   //    You can use assignVariable($var, $value) for this.
   //    Returns TRUE if successful, FALSE if not.
   public function displayTemplate($short_alias)
   {
      try
      {
         if (array_key_exists($short_alias, $this->Templates))
         {
            $this->Smarty->display($this->Templates[$short_alias]);
            return TRUE;
         }
         else
         {
            return FALSE;
         }
      }
      catch (exception $error)
      {
         common_log("Error displaying Template in SmartyTheme::displayTemplate(" . $short_alias . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }

   // -------------------------------------------------------------------------
   // assignVariable($var, $value)
   //    Interface to the internal Smarty instance to assign variables used in
   //    templates.
   //    Returns TRUE if successful, FALSE if not.
   public function assignVariable($var, $value)
   {
      try
      {
         $this->Smarty->assign($var,$value);
         return TRUE;
      }
      catch
      {
         common_log("Error assigning Smarty variables in SmartyTheme::assignVariable(" . $var . "," . $value . "): " . $error . PHP_EOL);
         return FALSE;
      }
   }
}

 ?>