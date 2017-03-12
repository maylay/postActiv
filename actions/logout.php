<?php
/* ============================================================================
 * Title: Logout
 * Logout action.
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2017, Maiyannah Bishop
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
 * Logout action.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Zach Copley
 * o Robin Millette <robin@millette.info>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
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
 * Logout action class.
 */
class LogoutAction extends ManagedAction
{
    /**
     * This is read only.
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return false;
    }

    protected function doPreparation()
    {
        if (!common_logged_in()) {
            // TRANS: Error message displayed when trying to logout even though you are not logged in.
            throw new AlreadyFulfilledException(_('Cannot log you out if you are not logged in.'));
        }
        if (Event::handle('StartLogout', array($this))) {
            $this->logout();
        }
        Event::handle('EndLogout', array($this));

        common_redirect(common_local_url('top'));
    }

    // Accessed through the action on events
    public function logout()
    {
        common_set_user(null);
        common_real_login(false); // not logged in
        common_forgetme(); // don't log back in!
    }
}
?>