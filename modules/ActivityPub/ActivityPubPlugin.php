<?php
/* ============================================================================
 * Title: ActivityPubPlugin
 * ActivityPub federation plugin
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
 * ActivityPub federation plugin
 *
 * The code in this plugin is wholly (c) copyright Maiyannah Bishop.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.


// ============================================================================
// Class: ActivityPubPlugin
// ActivityPub federation plugin superclass - mostly handles bootstrapping
class ActivityPubPlugin extends Plugin {
   
   // -------------------------------------------------------------------------
   // Function: onPluginVersion
   // Return version information about the plugin
   function onPluginVersion(array &$versions) {
      $versions[] = array('name'     => 'ActivityPub',
                          'version'  => '0.0.1',
                          'author'   => 'Maiyannah Bishop',
                          'homepage' => 'https://www.postactiv.com',
                          // TRANS: Plugin description.
                          'rawdescription' => _m('Follow people across social networks that implement '.
                             '<a href="https://www.w3.org/TR/activitypub/">ActivityPub</a>.'));

      return true;
   }
}

// END OF FILE
// ============================================================================
?>