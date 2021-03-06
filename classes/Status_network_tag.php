<?php
/* ============================================================================
 * Title: Status_network_tag
 * Table definition for status_network_tag
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
 * Table definition for status_network_tag
 *
 * Note: Not entirely sure this is neccesary in postActiv anymore. -mb
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o James Walker <walkah@walkah.net>
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Evan Prodromou
 * o Mikeal Nordfeldth <mmn@hethane.se>
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

class Status_network_tag extends Safe_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'status_network_tag';                      // table name
    public $site_id;                  // int(4)  primary_key not_null
    public $tag;                      // varchar(64)  primary_key not_null
    public $created;                 // datetime()   not_null


    function __construct()
    {
        global $config;
        global $_DB_DATAOBJECT;

        $sn = new Status_network();
        $sn->_connect();

        $config['db']['table_'. $this->tableName()] = $sn->_database;

        $this->_connect();
    }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /* Static get */
    static function getKV($k,$v=null)
    {
        // TODO: This probably has to be converted to a non-static call
        $i = DB_DataObject::staticGet('Status_network_tag',$k,$v);

        // Don't use local process cache; if we're fetching multiple
        // times it's because we're reloading it in a long-running
        // process; we need a fresh copy!
        global $_DB_DATAOBJECT;
        unset($_DB_DATAOBJECT['CACHE']['status_network_tag']);
        return $i;
    }

    static function pkeyGet($kv)
    {
        return Memcached_DataObject::pkeyGetClass('Status_network_tag', $kv);
    }

    /**
     * Fetch the (possibly cached) tag entries for the given site id.
     * Uses status_network's cache settings.
     *
     * @param string $site_id
     * @return array of strings
     */
    static function getTags($site_id)
    {
        $key = 'status_network_tags:' . $site_id;
        if (Status_network::$cache) {
            $packed = Status_network::$cache->get($key);
            if (is_string($packed)) {
                if ($packed == '') {
                    return array();
                } else {
                    return explode('|', $packed);
                }
            }
        }

        $result = array();

        $tags = new Status_network_tag();
        $tags->site_id = $site_id;
        if ($tags->find()) {
            while ($tags->fetch()) {
                $result[] = $tags->tag;
            }
        }

        if (Status_network::$cache) {
            $packed = implode('|', $result);
            Status_network::$cache->set($key, $packed, 0, 3600);
        }

        return $result;
    }

    /**
     * Drop the cached tag entries for this site.
     * Needed after inserting/deleting a tag entry.
     */
    function decache()
    {
        $key = 'status_network_tags:' . $this->site_id;
        if (Status_network::$cache || Status_network::$cacheInitialized) {
            // FIXME: this was causing errors, so I'm hiding them.
            // I'm a big chicken and lazy.
            @Status_network::$cache->delete($key);
        }
    }

    function insert()
    {
        $ret = parent::insert();
        $this->decache();
        return $ret;
    }

    function delete($useWhere=false)
    {
        $this->decache();
        return parent::delete($useWhere);
    }

    static function withTag($tag)
    {
        $snt = new Status_network_tag();

        $snt->tag = $tag;

        $snt->find();

        return $snt;
    }
}

// END OF FILE
// =============================================================================
?>