<?php
/* ============================================================================
 * Title: APICheckNickname
 * Indicate whether a nickname is available on an instance.
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
 * Returns 1 if nickname is available on this instance, 0 if not.
 * Error if site is private.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Hannes Mannerheim <h@nnesmannerhe.im>
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


class ApiCheckNicknameAction extends ApiAction
{

    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        if (common_config('site', 'private')) {
            $this->clientError(_('This site is private.'), 403);
        }

        if ($this->format !== 'json') {
            $this->clientError('This method currently only serves JSON.', 415);
        }

        return true;
    }

    protected function handle()
    {
        parent::handle();

        $nickname = $this->trimmed('nickname');

        try {
            Nickname::normalize($nickname, true);
            $nickname_ok = 1;
        } catch (NicknameException $e) {
            $nickname_ok = 0;
        }

        $this->initDocument('json');
        $this->showJsonObjects($nickname_ok);
        $this->endDocument('json');
    }
}

// END OF FILE
// ============================================================================
?>