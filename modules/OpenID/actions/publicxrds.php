<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Public XRDS for OpenID
 *
 * PHP version 5
 *
 * @category Action
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @author   Craig Andrews <candrews@integralblue.com>
 * @author   Robin Millette <millette@status.net>
 * @copyright 2009 Free Software Foundation, Inc http://www.fsf.org
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link     http://status.net/
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
 */

if (!defined('GNUSOCIAL')) { exit(1); }

require_once __DIR__.'/../openid.php';

/**
 * Public XRDS
 */
class PublicxrdsAction extends Action
{
    /**
     * Is read only?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    /**
     * Class handler.
     *
     * @param array $args array of arguments
     *
     * @return nothing
     */
    protected function handle()
    {
        parent::handle();
        $xrdsOutputter = new XRDSOutputter();
        $xrdsOutputter->startXRDS();
        Event::handle('StartPublicXRDS', array($this,&$xrdsOutputter));
        Event::handle('EndPublicXRDS', array($this,&$xrdsOutputter));
        $xrdsOutputter->endXRDS();
    }
}
