<?php
/* ============================================================================
 * Title: postActiv
 * Core class for abstracting the postActiv software
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
 * Core class for abstracting the postActiv software
 * 
 * Renamed from 'GNUsocial' to avoid trademark/IP problems.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Brion Vibber <brion@pobox.com>
 * o James Walker <walkah@walkah.net>
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chimo <chimo@chromic.org>
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

global $config, $_server, $_path;


// ============================================================================
// Class: postActiv
// Superclass to handle global configuration setup and management, as well as
// bootstrapping various features.
class postActiv {
   protected static $config_files = array();
   protected static $have_config;
   protected static $is_api;
   protected static $is_ajax;
   protected static $plugins = array();


   // -------------------------------------------------------------------------
   // Function: addPlugin
   // Configure and instantiate a plugin into the current configuration.
   // Class definitions will be loaded from standard paths if necessary.
   // Note that initialization events won't be fired until later.
   //
   // Parameters:
   // o string $name class name & plugin file/subdir name
   // o array $attrs key/value pairs of public attributes to set on plugin instance
   //
   // Error States:
   // o throws ServerException if plugin can't be found
   public static function addPlugin($name, array $attrs=array()) {
      $name = ucfirst($name);
      if (isset(self::$plugins[$name])) {
         // We have already loaded this plugin. Don't try to
         // do it again with (possibly) different values.
         // Försten till kvarn får mala.
         return true;
      }
      $pluginclass = "{$name}Plugin";
      if (!class_exists($pluginclass)) {
         $files = array("local/plugins/{$pluginclass}.php",
                        "local/plugins/{$name}/{$pluginclass}.php",
                        "local/{$pluginclass}.php",
                        "local/{$name}/{$pluginclass}.php",
                        "modules/{$pluginclass}.php",
                        "modules/{$name}/{$pluginclass}.php",
                        "plugins/{$pluginclass}.php",
                        "plugins/{$name}/{$pluginclass}.php");
         foreach ($files as $file) {
            $fullpath = INSTALLDIR.'/'.$file;
            if (@file_exists($fullpath)) {
               include_once($fullpath);
               break;
            }
         }
         if (!class_exists($pluginclass)) {
                throw new ServerException("Plugin $name not found.", 500);
         }
      }

      // Doesn't this $inst risk being garbage collected or something?
      // TODO: put into a static array that makes sure $inst isn't lost.
      $inst = new $pluginclass();
      foreach ($attrs as $aname => $avalue) {
         $inst->$aname = $avalue;
      }

      // Record activated plugins for later display/config dump
      self::$plugins[$name] = $attrs;
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: delPlugin
   // Unload a plugin
   public static function delPlugin($name) {
      // Remove our plugin if it was previously loaded
      $name = ucfirst($name);
      if (isset(self::$plugins[$name])) {
         unset(self::$plugins[$name]);
      }
      // make sure initPlugins will avoid this
      common_config_set('plugins', 'disable-'.$name, true);
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: getActivePlugins
   // Get a list of activated plugins in this process.
   //
   // Returns:
   // o array of (string $name, array $args) pairs
   public static function getActivePlugins() {
      return self::$plugins;
   }

   
   // -------------------------------------------------------------------------
   // Function: init
   // Initialize, or re-initialize, postActiv global configuration and plugins.
   //
   // If switching site configurations during script execution, be careful 
   // when working with leftover objects -- global settings affect many things 
   // and they may not behave as you expected.
   //
   // Parameters:
   // o server   - optional web server hostname for picking config
   // o path     - optional URL path for picking config
   // o conffile - optional configuration file path
   //
   // Error States:
   // o throws NoConfigException if config file can't be found
   public static function init($server=null, $path=null, $conffile=null) {
      Router::clear();
      self::initDefaults($server, $path);
      self::loadConfigFile($conffile);
      $sprofile = common_config('site', 'profile');
      if (!empty($sprofile)) {
         self::loadSiteProfile($sprofile);
      }
      // Load settings from database; note we need autoload for this
      Config::loadSettings();
      self::fillConfigVoids();
      self::verifyLoadedConfig();
      self::initPlugins();
   }

   
   // -------------------------------------------------------------------------
   // Function: currentSite
   // Get identifier of the currently active site configuration
   //
   // Returns:
   // o string
   public static function currentSite() {
      return common_config('site', 'nickname');
   }


   // -------------------------------------------------------------------------
   // Function: switchSite
   // Change site configuration to site specified by nickname,  if set up via
   // Status_network. If not, sites other than the current will fail horribly.
   //
   // May throw exception or trigger a fatal error if the given site is
   // missing or configured incorrectly.
   //
   // Parameters:
   // o string $nickname
   public static function switchSite($nickname) {
      if ($nickname == self::currentSite()) {
         return true;
      }

      $sn = Status_network::getKV('nickname', $nickname);
      if (empty($sn)) {
         return false;
         throw new Exception("No such site nickname '$nickname'");
      }
      $server = $sn->getServerName();
      self::init($server);
   }


   // -------------------------------------------------------------------------
   // Function: findAllSites
   // Pull all local sites from status_network table.
   //
   // Behavior undefined if site is not configured via Status_network.
   //
   // Returns:
   // o array of nicknames
   public static function findAllSites() {
      $sites = array();
      $sn = new Status_network();
      $sn->find();
      while ($sn->fetch()) {
         $sites[] = $sn->nickname;
      }
      return $sites;
   }


   // -------------------------------------------------------------------------
   // Function: initPlugins
   // Fire initialization events for all instantiated plugins.
   //
   // User config may have already added some of these plugins, with
   // maybe configured parameters. The self::addPlugin function will
   // ignore the new call if it has already been instantiated.
   protected static function initPlugins() {
      // Load core plugins
      foreach (common_config('plugins', 'core') as $name => $params) {
         call_user_func('self::addPlugin', $name, $params);
      }

      // Load default plugins
      foreach (common_config('plugins', 'default') as $name => $params) {
         $key = 'disable-' . $name;
         if (common_config('plugins', $key)) {
            continue;
         }

         // TODO: We should be able to avoid this is_null and assume $params
         // is an array, since that's how it is typed in addPlugin
         if (is_null($params)) {
            self::addPlugin($name);
         } else if (is_array($params)) {
            if (count($params) == 0) {
               self::addPlugin($name);
            } else {
               $keys = array_keys($params);
               if (is_string($keys[0])) {
                  self::addPlugin($name, $params);
               } else {
                  foreach ($params as $paramset) {
                     self::addPlugin($name, $paramset);
                  }
               }
            }
         }
      }
      // XXX: if plugins should check the schema at runtime, do that here.
      if (common_config('db', 'schemacheck') == 'runtime') {
         Event::handle('CheckSchema');
      }
      // Give plugins a chance to initialize in a fully-prepared environment
      Event::handle('InitializePlugin');
   }

   
   // -------------------------------------------------------------------------
   // Function: haveConfig
   // Quick-check if configuration has been established. Useful for functions 
   // which may get used partway through initialization to back off from 
   // fancier things.
   //
   // Returns:
   // o boolean
   public static function haveConfig() {
      return self::$have_config;
   }

   
   // -------------------------------------------------------------------------
   // Function: configFiles
   // Returns a list of configuration files that have been loaded for this 
   // instance of postActiv.
   public static function configFiles() {
      return self::$config_files;
   }


   // -------------------------------------------------------------------------
   // Function: isApi
   // Returns true/false if the page or endpoint being served up is an API
   // page
   public static function isApi() {
      return self::$is_api;
   }


   // -------------------------------------------------------------------------
   // Function: setApi
   // Sets the API mode flag for this instantiation of postActiv
   public static function setApi($mode) {
      self::$is_api = $mode;
   }


   // -------------------------------------------------------------------------
   // Function: isAjax
   // Returns true/false whether the page or endpoint being served up is an
   // AJAX document.
   public static function isAjax() {
      return self::$is_ajax;
   }


   // -------------------------------------------------------------------------
   // Function: setAjax
   // Sets the Ajax mode flag for this instantiation of postActiv
   public static function setAjax($mode) {
      self::$is_ajax = $mode;
   }


   // -------------------------------------------------------------------------
   // Function: defaultConfig
   // Build default configuration array
   //
   // Returns:
   // o array
   protected static function defaultConfig() {
      global $_server, $_path;
      require(INSTALLDIR.'/lib/default.php');
      return $default;
   }

   
   // -------------------------------------------------------------------------
   // Function: initDefaults
   // Establish default configuration based on given or default server and path.
   // Sets global $_server, $_path, and $config.,
   public static function initDefaults($server, $path) {
      global $_server, $_path, $config, $_PEAR;
      Event::clearHandlers();
      self::$plugins = array();
      
      // try to figure out where we are. $server and $path
      // can be set by including module, else we guess based
      // on HTTP info.
      if (isset($server)) {
         $_server = $server;
      } else {
         $_server = array_key_exists('SERVER_NAME', $_SERVER)
            ? strtolower($_SERVER['SERVER_NAME'])
            : null;
      }
      if (isset($path)) {
         $_path = $path;
      } else {
         $_path = (array_key_exists('SERVER_NAME', $_SERVER) && array_key_exists('SCRIPT_NAME', $_SERVER))
            ? self::_sn_to_path($_SERVER['SCRIPT_NAME'])
            : null;
      }
      
      // Set config values initially to default values
      $default = self::defaultConfig();
      $config = $default;

      // default configuration, overwritten in config.php
      // Keep DB_DataObject's db config synced to ours...
      $config['db'] = &$_PEAR->getStaticProperty('DB_DataObject','options');
      $config['db'] = $default['db'];

      if (function_exists('date_default_timezone_set')) {
         /* Work internally in UTC */
         date_default_timezone_set('UTC');
      }
   }


   // -------------------------------------------------------------------------
   // Function: loadSiteProfile
   // Bootstrap loading the site settings for a given profile (community, private)
   public static function loadSiteProfile($name) {
      global $config;
      $settings = SiteProfile::getSettings($name);
      $config = array_replace_recursive($config, $settings);
   }


   // -------------------------------------------------------------------------
   // Function: _sn_to_path
   // Helper function to get the path the application is running in.
   protected static function _sn_to_path($sn) {
      $past_root = substr($sn, 1);
      $last_slash = strrpos($past_root, '/');
      if ($last_slash > 0) {
         $p = substr($past_root, 0, $last_slash);
      } else {
         $p = '';
      }
      return $p;
   }


   // -------------------------------------------------------------------------
   // Function: loadConfigFile
   // Load the default or specified configuration file.  Modifies global 
   // $config and may establish plugins.
   //
   // Parameters:
   // o conffile - string with a path to a specific configuration file
   //
   // Error States:
   // o throws NoConfigException if unable to find a config file.
   // o throws ServerException if there is no database configuration specified.
   protected static function loadConfigFile($conffile=null) {
      global $_server, $_path, $config;

      // From most general to most specific:
      // server-wide, then vhost-wide, then for a path,
      // finally for a dir (usually only need one of the last two).
      if (isset($conffile)) {
         $config_files = array($conffile);
      } else {
         $config_files = array('/etc/gnusocial/config.php',
                               '/etc/gnusocial/config.d/'.$_server.'.php',
                               '/etc/postactiv/config.php',
                               '/etc/postactiv/config.d/'.$_server.'.php');
         if (strlen($_path) > 0) {
            $config_files[] = '/etc/gnusocial/config.d/'.$_server.'_'.$_path.'.php';
         }
         $config_files[] = INSTALLDIR.'/config.php';
      }
      self::$have_config = false;
      foreach ($config_files as $_config_file) {
         if (@file_exists($_config_file)) {
            // Ignore 0-byte config files
            if (filesize($_config_file) > 0) {
               include($_config_file);
               self::$config_files[] = $_config_file;
               self::$have_config = true;
            }
         }
      }

      if (!self::$have_config) {
         throw new NoConfigException("No configuration file found.", $config_files);
      }

      // Check for database server; must exist!
      if (empty($config['db']['database'])) {
         throw new ServerException("No database server for this site.");
      }
   }


   // -------------------------------------------------------------------------
   // Function: fillConfigVoids
   // Fill in some defaults if they are not specified.
   static function fillConfigVoids() {
      // special cases on empty configuration options
      if (!common_config('thumbnail', 'dir')) {
         common_config_set('thumbnail', 'dir', File::path('thumb'));
      }
   }


   // -------------------------------------------------------------------------
   // Function: verifyLoadedConfig
   // Verify that the loaded config is good. Not complete, but will throw 
   // exceptions on common configuration problems I hope.
   //
   // Might make changes to the filesystem, to created dirs, but will not make 
   // database changes.
   //
   // Error States:
   // o Throws ConfigException if the configuration doesn't check out.
   static function verifyLoadedConfig() {
      $mkdirs = [];
      if (common_config('htmlpurifier', 'Cache.DefinitionImpl') === 'Serializer' && !is_dir(common_config('htmlpurifier', 'Cache.SerializerPath'))) {
         $mkdirs[common_config('htmlpurifier', 'Cache.SerializerPath')] = 'HTMLPurifier Serializer cache';
      }

      // go through our configurable storage directories
      foreach (['attachments', 'thumbnail'] as $dirtype) {
         $dir = common_config($dirtype, 'dir');
         if (!empty($dir) && !is_dir($dir)) {
            $mkdirs[$dir] = $dirtype;
         }
      }

      // try to create those that are not directories
      foreach ($mkdirs as $dir=>$description) {
         if (is_file($dir)) {
            throw new ConfigException('Expected directory for '._ve($description).' is a file!');
         }
         if (!mkdir($dir)) {
            throw new ConfigException('Could not create directory for '._ve($description).': '._ve($dir));
         }
         if (!chmod($dir, 0775)) {
            common_log(LOG_WARNING, 'Could not chmod 0775 on directory for '._ve($description).': '._ve($dir));
         }
      }

      if (!is_array(common_config('public', 'autosource'))) {
         throw new ConfigException('Configuration option public/autosource is not an array.');
      }
   }


   // -------------------------------------------------------------------------
   // Function: isHTTPS
   // Are we running from the web with HTTPS?
   //
   // Returns:
   // o boolean true if we're running with HTTPS; else false
   static function isHTTPS() {
      if (common_config('site', 'sslproxy')) {
         return true;
      }
      // There are some exceptions to this; add them here!
      if (empty($_SERVER['HTTPS'])) {
         return false;
      }
      // If it is _not_ "off", it is on, so "true".
      return strtolower($_SERVER['HTTPS']) !== 'off';
   }

    /**
     * Can we use HTTPS? Then do! Only return false if it's not configured ("never").
     */
    static function useHTTPS()
    {
        return self::isHTTPS() || common_config('site', 'ssl') != 'never';
    }
}


// ============================================================================
// Class: NoConfigException
// Subclass of PHP class that reports configuration errors
//
// Properties:
// o configFiles - the config file or files that were misconfigured.
class NoConfigException extends Exception
{
   public $configFiles;

   // ------------------------------------------------------------------------
   // Function: __construct
   // Construct the exception with configfiles that were misconfigured.
   function __construct($msg, $configFiles) {
      parent::__construct($msg);
      $this->configFiles = $configFiles;
   }
}

// END OF FILE
// ============================================================================
?>