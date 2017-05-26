<?php
/* ============================================================================
 * Title: Config
 * Table definition for config
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
 * Superclass holding a representation of configuration settings and interfaces.
 *
 * This superclass represents a legacy implementation of holding the
 * configuration settings in the database and is kept mostly for caching
 * purposes.  There's probably a better way to do this and it is a candidate
 * for revision or removal.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Brion Vibber <brion@pobox.com>
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


// ============================================================================
// Class: Config
// Superclass holding a representation of configuration settings and interfaces.
//
// Properties:
// o __table = 'config' - table name
// o section - varchar(32)  primary_key not_null
// o setting - varchar(32)  primary_key not_null
// o value   - text
//
// Constants:
// o settingsKey = 'config:settings';
class Config extends Managed_DataObject {
   public $__table = 'config';                          // table name
   public $section;                         // varchar(32)  primary_key not_null
   public $setting;                         // varchar(32)  primary_key not_null
   public $value;                           // text

   const settingsKey = 'config:settings';


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns a representation of the database schema for the table this
   // superclass itself represents, in an array.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'section' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'default' => '', 'description' => 'configuration section'),
            'setting' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'default' => '', 'description' => 'configuration setting'),
            'value' => array('type' => 'text', 'description' => 'configuration value'),),
         'primary key' => array('section', 'setting'),
      );
   }


   // -------------------------------------------------------------------------
   // Function: loadSettings
   // Load the settings currently stored into the database into the class.
   // FIXME: This silently drops errors.
   static function loadSettings() {
      try {
         $settings = self::_getSettings();
         if (!empty($settings)) {
            self::_applySettings($settings);
         }
      } catch (Exception $e) {
         return;
      }
   }


   // -------------------------------------------------------------------------
   // Function: _getSettings
   // Helper function for loadSettings that does iterating through different
   // settings, checking the cache first and database second.
   //
   // Returns:
   // o array containing all the retrieved settings
   static function _getSettings() {
      $c = self::memcache();
      if (!empty($c)) {
         $settings = $c->get(Cache::key(self::settingsKey));
         if ($settings !== false) {
            return $settings;
         }
      }

      $settings = array();
      $config = new Config();
      $config->find();
      while ($config->fetch()) {
         $settings[] = array($config->section, $config->setting, $config->value);
      }
      $config->free();
      if (!empty($c)) {
         $c->set(Cache::key(self::settingsKey), $settings);
      }
      return $settings;
   }


   // -------------------------------------------------------------------------
   // Function: _applySettings
   // Helper function to apply configuration settings represented in an 
   // associative array into the config class.
   static function _applySettings($settings) {
      global $config;
      foreach ($settings as $s) {
         list($section, $setting, $value) = $s;
         $config[$section][$setting] = $value;
      }
    }


   // -------------------------------------------------------------------------
   // Function: insert
   // Save the configuration settings to database.
   function insert() {
      $result = parent::insert();
      if ($result) {
         Config::_blowSettingsCache();
      }
      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: delete
   // Remove the configuration settings from the database.
   function delete($useWhere=false) {
      $result = parent::delete($useWhere);
      if ($result !== false) {
         Config::_blowSettingsCache();
      }
      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: update
   // Updates existing configuration settings in the database.
   function update($dataObject=false) {
      $result = parent::update($dataObject);
      if ($result !== false) {
         Config::_blowSettingsCache();
      }
      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: save
   // Given a $section, $setting, and $value, save a configuration value in the
   // database.
   //
   // Returns:
   // o result of the insertion function
   static function save($section, $setting, $value) {
      $result = null;
      $config = Config::pkeyGet(array('section' => $section,
                                      'setting' => $setting));

      if (!empty($config)) {
         $orig = clone($config);
         $config->value = $value;
         $result = $config->update($orig);
      } else {
         $config = new Config();
         $config->section = $section;
         $config->setting = $setting;
         $config->value   = $value;
         $result = $config->insert();
      }

      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: _blowSettingsCache
   // A helper function to clear all of the configuration settings we presently
   // have in the settings cache.
   function _blowSettingsCache() {
      $c = self::memcache();
      if (!empty($c)) {
         $c->delete(Cache::key(self::settingsKey));
      }
   }
}

// END OF FILE
// ============================================================================
?>