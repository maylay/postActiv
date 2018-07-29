<?php
/* ============================================================================
 * Title: NodeinfoJRD
 * NodeInfo 2.0 statistics endpoint for the API
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
 * NodeInfo 2.0 statistics endpoint.
 * Originally a GNU Social plugin at <https://github.com/chimo/gs-nodeinfo> by
 * Chimo
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Chimo
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * ============================================================================
 */


// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

// ============================================================================
// Class: NodeinfoJRDAction
// Abstraction for the XML conversion of the NodeInfo 2.0 endpoint
class NodeinfoJRDAction extends XrdAction {
   const NODEINFO_2_0_REL = 'http://nodeinfo.diaspora.software/ns/schema/2.0';
   protected $defaultformat = 'json';

   protected function setXRD() {
      $this->xrd->links[] = new XML_XRD_Element_link(self::NODEINFO_2_0_REL,  common_local_url('nodeinfo_2_0'));
   }
}

// END OF FILE
// ============================================================================
?>