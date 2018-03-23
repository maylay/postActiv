<?php
/* ============================================================================
 * Title: Unblock
 * Unblock a user action class.
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
 * Unblock a user action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Robin Millette <robin@millette.info>
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

// END OF FILE
// ============================================================================
?>