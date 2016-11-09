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
 * Redirect to the appropriate top-of-site
 *
 * @category  UI
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================ 
 */

if (!defined('POSTACTIV')) { exit(1); }

class TopAction extends ManagedAction
{
    public function showPage()
    {
        if (common_config('singleuser', 'enabled')) {
            $user = User::singleUser();
            common_redirect(common_local_url('showstream', array('nickname' => $user->getNickname())), 303);
        } elseif (common_config('public', 'localonly')) {
            common_redirect(common_local_url('public'), 303);
        } else {
            common_redirect(common_local_url('networkpublic'), 303);
        }
    }
}
?>