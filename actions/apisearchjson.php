<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Action for showing Twitter-like JSON search results
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  API
 * @package   postActiv
 * @author    Zach Copley <zach@status.net>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('GNUSOCIAL')) { exit(1); }

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
?>