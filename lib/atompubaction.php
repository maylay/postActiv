<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * @category  Feed
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

abstract class AtompubAction extends ApiAuthAction
{
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        return $this->atompubPrepare();
    }

    protected function atompubPrepare() {
        return true;
    }

    protected function handle()
    {
        parent::handle();

        switch ($_SERVER['REQUEST_METHOD']) {
        case 'HEAD':
            $this->handleHead();
            break;
        case 'GET':
            $this->handleGet();
            break;
        case 'POST':
            $this->handlePost();
            break;
        case 'DELETE':
            $this->handleDelete();
            break;
        default:
            // TRANS: Client exception thrown when using an unsupported HTTP method.
            throw new ClientException(_('HTTP method not supported.'), 405);
        }

        return true;
    }

    protected function handleHead()
    {
        $this->handleGet();
    }

    protected function handleGet()
    {
        throw new ClientException(_('HTTP method not supported.'), 405);
    }

    protected function handlePost()
    {
        throw new ClientException(_('HTTP method not supported.'), 405);
    }

    protected function handleDelete()
    {
        throw new ClientException(_('HTTP method not supported.'), 405);
    }

    function isReadOnly($args)
    {
        // GET/HEAD is readonly, POST and DELETE (etc?) are readwrite.
        return in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'));
    }

    function requiresAuth()
    {
        // GET/HEAD don't require auth, POST and DELETE (etc?) require it.
        return !in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'));
    }
}
?>