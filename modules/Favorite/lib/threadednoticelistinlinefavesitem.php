<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 * @license   https://www.gnu.org/licenses/agpl.html 
 */

if (!defined('GNUSOCIAL')) { exit(1); }

// @todo FIXME: needs documentation.
class ThreadedNoticeListInlineFavesItem extends ThreadedNoticeListFavesItem
{
    function showStart()
    {
        $this->out->elementStart('div', array('class' => 'notice-faves'));
    }

    function showEnd()  
    {
        $this->out->elementEnd('div');
    }
}
