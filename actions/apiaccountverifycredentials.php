<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Test if supplied user credentials are valid.
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  API
 * @package   postActiv
 * @author    Zach Copley <zach@status.net>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Check a user's credentials. Returns an HTTP 200 OK response code and a
 * representation of the requesting user if authentication was successful;
 * returns a 401 status code and an error message if not.
 */
class ApiAccountVerifyCredentialsAction extends ApiAuthAction
{
    protected function handle()
    {
        parent::handle();

        if (!in_array($this->format, array('xml', 'json'))) {
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), $code = 404);
        }

        $twitter_user = $this->twitterUserArray($this->auth_user->getProfile(), true);

        if ($this->format == 'xml') {
            $this->initDocument('xml');
            $this->showTwitterXmlUser($twitter_user, 'user', true);
            $this->endDocument('xml');
        } elseif ($this->format == 'json') {
            $this->initDocument('json');
            $this->showJsonObjects($twitter_user);
            $this->endDocument('json');
        }

    }

    /**
     * Is this action read only?
     *
     * @param array $args other arguments
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }
}
?>