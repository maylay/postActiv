<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Base class for RSS 1.0 feed actions
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
 * @category  Mail
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @author    Earle Martin <earle@downlode.org>
 * @copyright 2008-9 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

class TargetedRss10Action extends Rss10Action
{
    protected $target = null;

    protected function doStreamPreparation()
    {
        $this->target = User::getByNickname($this->trimmed('nickname'))->getProfile();
    }

    public function getTarget()
    {
        return $this->target;
    }

    function getImage()
    {
        return $this->target->avatarUrl(AVATAR_PROFILE_SIZE);
    }
}
?>