<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * PHP version 5
 *
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
 * @category  Database
 * @package   postActiv
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2015-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

class GS_DataObject extends DB_DataObject
{
    public function _autoloadClass($class, $table=false)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::_autoloadClass($class, $table);

        // reset
        error_reporting($old);
        return $res;
    }

    // wraps the _connect call so we don't throw E_STRICT warnings during it
    public function _connect()
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::_connect();

        // reset
        error_reporting($old);
        return $res;
    }

    // wraps the _loadConfig call so we don't throw E_STRICT warnings during it
    // doesn't actually return anything, but we'll follow the same model as the rest of the wrappers
    public function _loadConfig()
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::_loadConfig();

        // reset
        error_reporting($old);
        return $res;
    }

    // wraps the count call so we don't throw E_STRICT warnings during it
    public function count($countWhat = false,$whereAddOnly = false)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::count($countWhat, $whereAddOnly);

        // reset
        error_reporting($old);
        return $res;
    }

    static public function debugLevel($v = null)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::debugLevel($v);

        // reset
        error_reporting($old);
        return $res;
    }

    // delete calls PEAR::isError from DB_DataObject, so let's make that disappear too
    public function delete($useWhere = false)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::delete($useWhere);

        // reset
        error_reporting($old);
        return $res;
    }

    static public function factory($table = '')
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::factory($table);

        // reset
        error_reporting($old);
        return $res;
    }

    public function get($k = null, $v = null)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::get($k, $v);

        // reset
        error_reporting($old);
        return $res;
    }

    public function fetch()
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::fetch();

        // reset
        error_reporting($old);
        return $res;
    }

    public function find($n = false)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::find($n);

        // reset
        error_reporting($old);
        return $res;
    }

    public function fetchRow($row = null)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::fetchRow($row);

        // reset
        error_reporting($old);
        return $res;
    }

    // insert calls PEAR::isError from DB_DataObject, so let's make that disappear too
    public function insert()
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::insert();

        // reset
        error_reporting($old);
        return $res;
    }

    // DB_DataObject's joinAdd calls DB_DataObject::factory explicitly, so our factory-override doesn't work
    public function joinAdd($obj = false, $joinType='INNER', $joinAs=false, $joinCol=false)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::joinAdd($obj, $joinType, $joinAs, $joinCol);

        // reset
        error_reporting($old);
        return $res;
    }

    public function links()
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::links();

        // reset
        error_reporting($old);
        return $res;
    }

    // wraps the update call so we don't throw E_STRICT warnings during it
    public function update($dataObject = false)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::update($dataObject);

        // reset
        error_reporting($old);
        return $res;
    }

    static public function staticGet($class, $k, $v = null)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::staticGet($class, $k, $v);

        // reset
        error_reporting($old);
        return $res;
    }

    public function staticGetAutoloadTable($table)
    {
        // avoid those annoying PEAR::DB strict standards warnings it causes
        $old = error_reporting();
        error_reporting(error_reporting() & ~E_STRICT);

        $res = parent::staticGetAutoloadTable($table);

        // reset
        error_reporting($old);
        return $res;
    }
}
?>