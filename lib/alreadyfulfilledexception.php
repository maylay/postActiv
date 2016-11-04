<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
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
 * @category Exception
 * @package  GNUsocial
 * @author   Mikael Nordfeldth <mmn@hethane.se>
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link     http://gnu.io/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Parent class for an exception when trying to do something that was
 * probably already done.
 *
 * This is a common case for example when remote sites are not up to
 * date with our database. For example subscriptions, where a remote
 * user may be unsubscribed from our user, but they request it anyway.
 *
 * This exception is usually caught in a manner that lets the execution
 * continue _as if_ the desired action did what it was supposed to do.
 */

class AlreadyFulfilledException extends ServerException
{
    public function __construct($msg=null)
    {
        if ($msg === null) {
            // TRANS: Exception text when attempting to perform something which seems already done.
            $msg = _('Trying to do something that was already done.');
        }

        parent::__construct($msg, 409);
    }
}
?>