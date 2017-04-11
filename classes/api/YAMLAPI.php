<?php
/* ============================================================================
 * Title: YAML API
 * Class abstraction of the brand shiny new YAML API
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016, Maiyannah Bishop
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
 * Class abstraction of the brand shiny new YAML API
 *
 * PHP version:
 * Tested with PHP 5.6
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


// -----------------------------------------------------------------------------
// Class: YAMLAPIAction
// Re-implementation of the "Twitter-alike" API in YAML
class YAMLAPIAction extends Action {
   const READ_ONLY  = 1;
   const READ_WRITE = 2;

   var $user      = null;
   var $auth_user = null;
   var $page      = null;
   var $count     = null;
   var $offset    = null;
   var $limit     = null;
   var $lbound_id = null;
   var $ubound_id = null;
   var $source    = null;
   var $callback  = null;
   var $format    = null;
   
   var $access    = self::READ_ONLY;  // read (default) or read-write

   static $reserved_sources = array('web', 'omb', 'ostatus', 'mail', 'xmpp', 'api');


   // -------------------------------------------------------------------------
   // Function: prepare
   //
   // Parameters:
   // o array $args
   protected function prepare(array $args=array()) {
      throw new NotYetImplemented("YAMLAPIAction::prepare not yet implemented");
   }
   
   
   // -------------------------------------------------------------------------
   // Function: handle
   // Handle a request
   //
   // Parameters:
   // o array $args Arguments from $_REQUEST
   //
   // Returns:
   // o void
   protected function handle() {
      throw new NotYetImplemented("YAMLAPIAction::handle not yet implemented");
   }


   // -------------------------------------------------------------------------
   // Function: element
   // Override HTMLOutputter::element to output YAML
   function element($tag, $attrs=null, $content=null) {
      throw new NotYetImplemented("YAMLAPIAction::element not yet implemented");
   }
}

// END OF FILE
// ============================================================================
?>
