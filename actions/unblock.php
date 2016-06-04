<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Unblock a user action class.
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
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Robin Millette <millette@status.net>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.net>
 * @copyright 2008-2011 StatusNet, Inc
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

/**
 * Unblock a user action class.
 */
class UnblockAction extends ProfileFormAction
{
    function prepare(array $args = array())
    {
        if (!parent::prepare($args)) {
            return false;
        }

        $cur = common_current_user();

        assert(!empty($cur)); // checked by parent

        if (!$cur->hasBlocked($this->profile)) {
            // TRANS: Client error displayed when trying to unblock a non-blocked user.
            $this->clientError(_("You haven't blocked that user."));
        }

        return true;
    }

    /**
     * Unblock a user.
     *
     * @return void
     */
    function handlePost()
    {
        $cur = common_current_user();

        $result = false;

        if (Event::handle('StartUnblockProfile', array($cur, $this->profile))) {
            $result = $cur->unblock($this->profile);
            if ($result) {
                Event::handle('EndUnblockProfile', array($cur, $this->profile));
            }
        }

        if (!$result) {
            // TRANS: Server error displayed when removing a user block.
            $this->serverError(_('Error removing the block.'));
        }
    }
}
?>