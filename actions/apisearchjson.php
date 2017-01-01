<?php
/* ============================================================================
 * Title: APISearchJSON
 * Action for showing Twitter-like JSON search results
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
 * Action for showing Twitter-like JSON search results
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Evan Prodromou
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


/**
 * Action handler for Twitter-compatible API search
 */
class ApiSearchJSONAction extends ApiPrivateAuthAction
{
    var $query;
    var $lang;
    var $rpp;
    var $page;
    var $since_id;
    var $limit;
    var $geocode;

    /**
     * Initialization.
     *
     * @param array $args Web and URL arguments
     *
     * @return boolean true if nothing goes wrong
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        $this->query = $this->trimmed('q');
        $this->lang  = $this->trimmed('lang');
        $this->rpp   = $this->trimmed('rpp');

        if (!$this->rpp) {
            $this->rpp = 15;
        }

        if ($this->rpp > 100) {
            $this->rpp = 100;
        }

        $this->page = $this->trimmed('page');

        if (!$this->page) {
            $this->page = 1;
        }

        // TODO: Suppport max_id -- we need to tweak the backend
        // Search classes to support it.

        $this->since_id = $this->trimmed('since_id');
        $this->geocode  = $this->trimmed('geocode');

        return true;
    }

    /**
     * Handle a request
     *
     * @return void
     */
    function handle()
    {
        parent::handle();
        $this->showResults();
    }

    /**
     * Show search results
     *
     * @return void
     */
    function showResults()
    {
        // TODO: Support search operators like from: and to:, boolean, etc.

        $notice = new Notice();

        $this->notices = array();
        $search_engine = $notice->getSearchEngine('notice');
        $search_engine->set_sort_mode('chron');
        $search_engine->limit(($this->page - 1) * $this->rpp, $this->rpp + 1);
        if ($search_engine->query($this->query)) {
            $cnt = $notice->find();
            $this->notices = $notice->fetchAll();
        }

       $this->showJsonTimeline($this->notices);
    }

    /**
     * Do we need to write to the database?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }
}

// END OF FILE
// ============================================================================
?>