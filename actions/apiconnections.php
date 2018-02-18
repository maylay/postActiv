<?php
/* ============================================================================
 * Title: APIConnections
 * Simple API endpoint that displays the number of nodes that this one 
 * connects to.
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
 * Simple API endpoint that displays the number of nodes that this one 
 * connects to.
 *
 * Output in JSON:
 *     "connections": {"total":"123" ,unique:"69"}
 *
 * Output in XML:
 *     <connections>
 *        <total>123</total>
 *        <unique>69</unique>
 *     </connections>
 *
 * Output in YAML:
 *     connections:
 *         - total: 123
 *         - unique: 69
 *
 * PHP version:
 * Tested with PHP 7.0
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
// Class: ApiConnectionsAction
// Class which handles the API connections display endpoint.
class ApiConnectionsAction extends ApiAction {

   // -------------------------------------------------------------------------
   // Function: prepare
   // Get the endpoint ready for action
   protected function prepare(array $args=array()) {
      parent::prepare($args);
   }


   // -------------------------------------------------------------------------
   // Function: handle
   // Display the number of connections in the given format.
   protected function handle() {
      parent::handle();
   }
   

   // -------------------------------------------------------------------------
   // Function: isReadOnly
   // Is this end-point read-only?  (yes)
   function isReadOnly($args) {
      return true;
   }
}

// END OF FILE
// ============================================================================
?>