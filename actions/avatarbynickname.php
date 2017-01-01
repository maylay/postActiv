<?php
/* ============================================================================
 * Title: AvatarByNickname
 * Retrieve user avatar by nickname action class.
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
 * Retrieve user avatar by nickname action class.
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
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
 * Retrieve user avatar by nickname action class.
 */
class AvatarbynicknameAction extends Action
{
    /**
     * Class handler.
     *
     * @param array $args query arguments
     *
     * @return boolean false if nickname or user isn't found
     */
    protected function handle()
    {
        parent::handle();
        $nickname = $this->trimmed('nickname');
        if (!$nickname) {
            // TRANS: Client error displayed trying to get an avatar without providing a nickname.
            $this->clientError(_('No nickname.'));
        }
        $size = $this->trimmed('size') ?: 'original';

        $user = User::getKV('nickname', $nickname);
        if (!$user) {
            // TRANS: Client error displayed trying to get an avatar for a non-existing user.
            $this->clientError(_('No such user.'));
        }
        $profile = $user->getProfile();
        if (!$profile) {
            // TRANS: Error message displayed when referring to a user without a profile.
            $this->clientError(_('User has no profile.'));
        }

        if ($size === 'original') {
            try {
                $avatar = Avatar::getUploaded($profile);
                $url = $avatar->displayUrl();
            } catch (NoAvatarException $e) {
                $url = Avatar::defaultImage(AVATAR_PROFILE_SIZE);
            }
        } else {
            $url = $profile->avatarUrl($size);
        }

        common_redirect($url, 302);
    }

    function isReadOnly($args)
    {
        return true;
    }
}
?>