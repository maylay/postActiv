<?php
/* ============================================================================
 * Title: Status_network
 * Table Definition for status_network
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
 * Table Definition for status_network
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Brion Vibber <brion@pobox.com>
 *  o James Walker <walkah@walkah.net>
 *  o Siebrand Mazeland <s.mazeland@xs4all.nl>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
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
// Class: Status_network
// Superclass representing an entry for a single status_network in a multi-
// site install.
//
// Properties:
// o __table = 'status_network' - table name
// o site_id  - int(4) primary_key not_null
// o nickname - varchar(64)   unique_key not_null
// o hostname - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o pathname - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o dbhost   - varchar(191)               not 255 because utf8mb4 takes more space
// o dbuser   - varchar(191)               not 255 because utf8mb4 takes more space
// o dbpass   - varchar(191)               not 255 because utf8mb4 takes more space
// o dbname   - varchar(191)               not 255 because utf8mb4 takes more space
// o sitename - varchar(191)               not 255 because utf8mb4 takes more space
// o theme    - varchar(191)               not 255 because utf8mb4 takes more space
// o logo     - varchar(191)               not 255 because utf8mb4 takes more space
// o created  - datetime()   not_null
// o modified - timestamp()   not_null default_CURRENT_TIMESTAMP
class Status_network extends Safe_DataObject {
   public $__table = 'status_network';                  // table name
   public $site_id;                         // int(4) primary_key not_null
   public $nickname;                        // varchar(64)   unique_key not_null
   public $hostname;                        // varchar(191)  unique_key   not 255 because utf8mb4 takes more space
   public $pathname;                        // varchar(191)  unique_key   not 255 because utf8mb4 takes more space
   public $dbhost;                          // varchar(191)               not 255 because utf8mb4 takes more space
   public $dbuser;                          // varchar(191)               not 255 because utf8mb4 takes more space
   public $dbpass;                          // varchar(191)               not 255 because utf8mb4 takes more space
   public $dbname;                          // varchar(191)               not 255 because utf8mb4 takes more space
   public $sitename;                        // varchar(191)               not 255 because utf8mb4 takes more space
   public $theme;                           // varchar(191)               not 255 because utf8mb4 takes more space
   public $logo;                            // varchar(191)               not 255 because utf8mb4 takes more space
   public $created;                         // datetime()   not_null
   public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP


   // -------------------------------------------------------------------------
   // Function: getKV
   // Static get
   static function getKV($k,$v=NULL) {
      // TODO: This must probably be turned into a non-static call
      $i = DB_DataObject::staticGet('Status_network',$k,$v);

      // Don't use local process cache; if we're fetching multiple
      // times it's because we're reloading it in a long-running
      // process; we need a fresh copy!
      global $_DB_DATAOBJECT;
      unset($_DB_DATAOBJECT['CACHE']['status_network']);
      return $i;
   }

   // XXX: made public so Status_network_tag can eff with it
   public static $cache = null;
   public static $cacheInitialized = false;
   static $base = null;
   static $wildcard = null;


   // -------------------------------------------------------------------------
   // Function: setupDB
   // Sets up the DB for a given multisite server.  This is every bit as
   // insecure as it looks and mostly kept as a legacy function.  You probably
   // shouldn't use it.
   //
   // Parameters:
   // o string $dbhost
   // o string $dbuser
   // o string $dbpass
   // o string $dbname
   // o array $servers memcached servers to use for caching config info
   static function setupDB($dbhost, $dbuser, $dbpass, $dbname, array $servers) {
      global $config;
      $config['db']['database_'.$dbname] = "mysqli://$dbuser:$dbpass@$dbhost/$dbname";
      $config['db']['ini_'.$dbname] = INSTALLDIR.'/classes/status_network.ini';
      foreach (array('status_network', 'status_network_tag', 'unavailable_status_network') as $table) {
         $config['db']['table_'.$table] = $dbname;
      }
      if (class_exists('Memcache')) {
         self::$cache = new Memcache();

         // If we're a parent command-line process we need
         // to be able to close out the connection after
         // forking, so disable persistence.
         //
         // We'll turn it back on again the second time
         // through which will either be in a child process,
         // or a single-process script which is switching
         // configurations.
         $persist = php_sapi_name() != 'cli' || self::$cacheInitialized;
         if (!is_array($servers)) {
            $servers = array($servers);
         }
         foreach($servers as $server) {
            $parts = explode(':', $server);
            $server = $parts[0];
            if (count($parts) > 1) {
               $port = $parts[1];
            } else {
               $port = 11211;
            }
            self::$cache->addServer($server, $port, $persist);
         }
         self::$cacheInitialized = true;
      }
      self::$base = $dbname;
   }


   // -------------------------------------------------------------------------
   // Function: cacheKey
   // Returns a cache key for a given status network.
   static function cacheKey($k, $v) {
      return 'postactiv:' . self::$base . ':status_network:'.$k.':'.$v;
   }


   // -------------------------------------------------------------------------
   // Function: memGet
   // Get a cached segment for a given status network.
   static function memGet($k, $v) {
      if (!self::$cache) {
            return self::getKV($k, $v);
      }

      $ck = self::cacheKey($k, $v);
      $sn = self::$cache->get($ck);
      if (empty($sn)) {
         $sn = self::getKV($k, $v);
         if (!empty($sn)) {
            self::$cache->set($ck, clone($sn));
         }
      }
      return $sn;
   }


   // -------------------------------------------------------------------------
   // Function: decache
   // Remove a cached segment for a given status network.
   function decache() {
      if (self::$cache) {
         $keys = array('nickname', 'hostname', 'pathname');
         foreach ($keys as $k) {
            $ck = self::cacheKey($k, $this->$k);
            self::$cache->delete($ck);
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: update
   // Update a cached segment for a given status network
   function update($dataObject=false) {
      if (is_object($dataObject)) {
         $dataObject->decache(); # might be different keys
      }
      return parent::update($dataObject);
   }


   // -------------------------------------------------------------------------
   // Function: updateKeys
   // DB_DataObject doesn't allow updating keys (even non-primary), so this is
   // a workaround
   function updateKeys(&$orig) {
      $this->_connect();
      foreach (array('hostname', 'pathname') as $k) {
         if (strcmp($this->$k, $orig->$k) != 0) {
            $parts[] = $k . ' = ' . $this->_quote($this->$k);
         }
      }
      if (count($parts) == 0) {
         // No changes
         return true;
      }

      $toupdate = implode(', ', $parts);
      $table = common_database_tablename($this->tableName());
      $qry = 'UPDATE ' . $table . ' SET ' . $toupdate .
            ' WHERE nickname = ' . $this->_quote($this->nickname);
      $orig->decache();
      $result = $this->query($qry);
      $this->decache();
      return $result;
   }
   

   // -------------------------------------------------------------------------
   // Function: delete
   // Delete a site from a multi-site install
   function delete($useWhere=false) {
      $this->decache(); # while we still have the values!
      return parent::delete($useWhere);
   }


   // -------------------------------------------------------------------------
   // Function: getFromHostname
   // Returns a site in a multi-site install based on its hostname
   //
   // Parameters:
   // o string $servername hostname
   // o string $wildcard hostname suffix to match wildcard config
   //
   // Returns:
   // o mixed Status_network or null
   static function getFromHostname($servername, $wildcard) {
      $sn = null;
      if (0 == strncasecmp(strrev($wildcard), strrev($servername), strlen($wildcard))) {
         // special case for exact match
         if (0 == strcasecmp($servername, $wildcard)) {
            $sn = self::memGet('nickname', '');
         } else {
            $parts = explode('.', $servername);
            $sn = self::memGet('nickname', strtolower($parts[0]));
         }
      } else {
         $sn = self::memGet('hostname', strtolower($servername));
         if (empty($sn)) {
            // Try for a no-www address
            if (0 == strncasecmp($servername, 'www.', 4)) {
               $sn = self::memGet('hostname', strtolower(substr($servername, 4)));
            }
         }
      }
      return $sn;
   }


   // -------------------------------------------------------------------------
   // Function: setupSite
   // Sets up an uninitialized site in a multi-site install.
   //
   // Parameters:
   // o string $servername hostname
   // o string $pathname URL base path
   // o string $wildcard hostname suffix to match wildcard config
   //
   // Returns:
   // o the created site's object class if successful, null if not
   static function setupSite($servername, $pathname, $wildcard) {
      global $config;
      $sn = null;

      // XXX I18N, probably not crucial for hostnames
      // XXX This probably needs a tune up
      $sn = self::getFromHostname($servername, $wildcard);
      if (!empty($sn)) {
         // Redirect to the right URL
         if (!empty($sn->hostname) && empty($_SERVER['HTTPS']) && 0 != strcasecmp($sn->hostname, $servername)) {
            $sn->redirectTo('http://'.$sn->hostname.$_SERVER['REQUEST_URI']);
         } else if (!empty($_SERVER['HTTPS']) && 0 != strcasecmp($sn->hostname, $servername) && 0 != strcasecmp($sn->nickname.'.'.$wildcard, $servername)) {
            $sn->redirectTo('https://'.$sn->nickname.'.'.$wildcard.$_SERVER['REQUEST_URI']);
         }

         $dbhost = (empty($sn->dbhost)) ? 'localhost' : $sn->dbhost;
         $dbuser = (empty($sn->dbuser)) ? $sn->nickname : $sn->dbuser;
         $dbpass = $sn->dbpass;
         $dbname = (empty($sn->dbname)) ? $sn->nickname : $sn->dbname;
         $config['db']['database'] = "mysqli://$dbuser:$dbpass@$dbhost/$dbname";
         $config['site']['name'] = $sn->sitename;
         $config['site']['nickname'] = $sn->nickname;
         self::$wildcard = $wildcard;
         $config['site']['wildcard'] =& self::$wildcard;
         if (!empty($sn->hostname)) {
            $config['site']['server'] = $sn->hostname;
         }
         if (!empty($sn->theme)) {
            $config['site']['theme'] = $sn->theme;
         }
         if (!empty($sn->logo)) {
            $config['site']['logo'] = $sn->logo;
         }
         return $sn;
      } else {
         return null;
      }
   }

   // -------------------------------------------------------------------------
   // Function: redirectTo
   // Perform a 301 redirect.
   //
   // Code partially mooked from http://www.richler.de/en/php-redirect/
   // (C) 2006 by Heiko Richler  http://www.richler.de/
   // LGPL
   function redirectTo($destination) {
      $old = 'http'.(($_SERVER['HTTPS'] == 'on') ? 'S' : ''). '://'.
         $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'];
      if ($old == $destination) { // this would be a loop!
         // error_log(...) ?
         return false;
      }
      header('HTTP/1.1 301 Moved Permanently');
      header("Location: $destination");
      print "<a href='$destination'>$destination</a>\n";
      exit;
   }


   // -------------------------------------------------------------------------
   // Function: getServerName
   // Return the server name of the current status network on a multi-
   // site install.
   function getServerName() {
      if (!empty($this->hostname)) {
         return $this->hostname;
      } else {
         return $this->nickname . '.' . self::$wildcard;
      }
   }


   // -------------------------------------------------------------------------
   // Function: getTags
   // Return site meta-info tags as an array
   //
   // Returns:
   // o array of strings
   function getTags() {
      return Status_network_tag::getTags($this->site_id);
   }


   // -------------------------------------------------------------------------
   // Function: saveTags
   // Save a given set of tags for a status network on a multi-site install.
   //
   // Parameters:
   // o array tags
   function setTags(array $tags) {
      $this->clearTags();
      foreach ($tags as $tag) {
         if (!empty($tag)) {
            $snt = new Status_network_tag();
            $snt->site_id = $this->site_id;
            $snt->tag = $tag;
            $snt->created = common_sql_now();
            $id = $snt->insert();
            if (!$id) {
               // TRANS: Exception thrown when a tag cannot be saved.
               throw new Exception(_("Unable to save tag."));
            }
         }
      }
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: clearTags
   // Delete all the tags for a status network on a multi-site install.
   function clearTags() {
      $tag = new Status_network_tag();
      $tag->site_id = $this->site_id;
      if ($tag->find()) {
         while($tag->fetch()) {
            $tag->delete();
         }
      }
      $tag->free();
   }


   // -------------------------------------------------------------------------
   // Function: hasTag
   // Check if this site record has a particular meta-info tag attached.
   //
   // Parameters:
   // o string $tag
   //
   // Returns:
   // o boolean
   function hasTag($tag) {
      return in_array($tag, $this->getTags());
   }
}

// END OF FILE
// ============================================================================
?>