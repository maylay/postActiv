<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
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
 * PHP version 5
 *
 * Returns 1 if nickname is available on this instance, 0 if not. Error if site is private.
 *
 * @category  API
 * @package   postActiv
 * @author    Hannes Mannerheim <h@nnesmannerhe.im>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2013-2016 Hannes Mannerheim
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

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
?>