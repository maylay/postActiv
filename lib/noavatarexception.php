<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Class for an exception when unable to retrieve or resize a profile's avatar.
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
 * @category  Exception
 * @package   GNUsocial
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @copyright 2013 Free Software Foundation, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.gnu.org/software/social/
 */

if (!defined('POSTACTIV')) { exit(1); }

class NoAvatarException extends NoResultException
{
    public $target;

    public function __construct(Profile $target, Avatar $obj)
    {
        $this->target = $target;
        parent::__construct($obj);
    }
}
?>