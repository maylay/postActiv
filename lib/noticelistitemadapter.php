<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * For use by microapps to customize notice list item output
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
 * @category  Microapp
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * For use by microapps to customize NoticeListItem output
 */
class NoticeListItemAdapter
{
    protected $nli;

    /**
     * Wrap a notice list item.
     *
     * @param NoticeListItem $nli item to wrap
     */
    function __construct($nli)
    {
        $this->nli = $nli;
    }

    /**
     * Delegate unimplemented methods to the notice list item attribute.
     *
     * @param string $name      Name of the method
     * @param array  $arguments Arguments called
     *
     * @return mixed Return value of the method.
     */
    function __call($name, $arguments)
    {
        return call_user_func_array(array($this->nli, $name), $arguments);
    }

    function __get($name)
    {
        return $this->nli->$name;
    }
}
?>