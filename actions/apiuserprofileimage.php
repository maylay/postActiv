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
 * Return a user's avatar image
 *
 * @category  API
 * @package   postActiv
 * @author    Brion Vibber <brion@status.net>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
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

/**
 * Ouputs avatar URL for a user, specified by screen name.
 * Unlike most API endpoints, this returns an HTTP redirect rather than direct data.
 */
class ApiUserProfileImageAction extends ApiPrivateAuthAction
{
    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     *
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);
        $user = User::getKV('nickname', $this->arg('screen_name'));
        if (!($user instanceof User)) {
            // TRANS: Client error displayed when requesting user information for a non-existing user.
            $this->clientError(_('User not found.'), 404);
        }
        $this->target = $user->getProfile();
        $this->size = $this->arg('size');

        return true;
    }

    /**
     * Handle the request
     *
     * Check the format and show the user info
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        $size = $this->avatarSize();
        $url  = $this->target->avatarUrl($size);

        // We don't actually output JSON or XML data -- redirect!
        common_redirect($url, 302);
    }

    /**
     * Get the appropriate pixel size for an avatar based on the request...
     *
     * @return int
     */
    private function avatarSize()
    {
        switch ($this->size) {
            case 'mini':
                return AVATAR_MINI_SIZE; // 24x24
            case 'bigger':
                return AVATAR_PROFILE_SIZE; // Twitter does 73x73, but we do 96x96
            case 'normal': // fall through
            default:
                return AVATAR_STREAM_SIZE; // 48x48
        }
    }

    /**
     * Return true if read only.
     *
     * MAY override
     *
     * @param array $args other arguments
     *
     * @return boolean is read only action?
     */
    function isReadOnly($args)
    {
        return true;
    }
}
?>