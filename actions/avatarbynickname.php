<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Retrieve user avatar by nickname action class.
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
 * @category  Accounts
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Robin Millette <robin@millette.info>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link      http://postactiv.com/
 */

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