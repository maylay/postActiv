<?php
/* ============================================================================
 * Title: UserByID
 * User by ID action class.
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
 * User by ID action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Robin Millette <robin@millette.info>
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

/**
 * User by ID action class.
 */
class UserbyidAction extends ShowstreamAction
{
    protected function doPreparation()
    {
        // accessing by ID just requires an ID, not a nickname
        $this->target = Profile::getByID($this->trimmed('id'));

        // For local users when accessed by id number, redirect with
        // the nickname as argument instead of id.
        if ($this->target->isLocal()) {
            // Support redirecting to FOAF rdf/xml if the agent prefers it...
            // Internet Explorer doesn't specify "text/html" and does list "*/*"
            // at least through version 8. We need to list text/html up front to
            // ensure that only user-agents who specifically ask for RDF get it.
            $page_prefs = 'text/html,application/xhtml+xml,application/rdf+xml,application/xml;q=0.3,text/xml;q=0.2';
            $httpaccept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
            $type       = common_negotiate_type(common_accept_to_prefs($httpaccept),
                                                common_accept_to_prefs($page_prefs));
            $page       = $type === 'application/rdf+xml' ? 'foaf' : 'showstream';
            $url        = common_local_url($page, array('nickname' => $this->target->getNickname()));
            common_redirect($url, 303);
        }
    }
}

// END OF FILE
// ============================================================================
?>