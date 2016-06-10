<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Update the authenticating user's profile image
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
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://postactiv.com/
 */

if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * Updates the authenticating user's profile image. Note that this API method
 * expects raw multipart data, not a URL to an image.
 */
class ApiAccountUpdateProfileImageAction extends ApiAuthAction
{
    protected $needPost = true;

    /**
     * Handle the request
     *
     * Check whether the credentials are valid and output the result
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        // Workaround for PHP returning empty $_POST and $_FILES when POST
        // length > post_max_size in php.ini

        if (empty($_FILES)
            && empty($_POST)
            && ($_SERVER['CONTENT_LENGTH'] > 0)
        ) {
            // TRANS: Client error displayed when the number of bytes in a POST request exceeds a limit.
            // TRANS: %s is the number of bytes of the CONTENT_LENGTH.
            $msg = _m('The server was unable to handle that much POST data (%s byte) due to its current configuration.',
                      'The server was unable to handle that much POST data (%s bytes) due to its current configuration.',
                      intval($_SERVER['CONTENT_LENGTH']));
            $this->clientError(sprintf($msg, $_SERVER['CONTENT_LENGTH']));
        }

        if (empty($this->user)) {
            // TRANS: Client error displayed updating profile image without having a user object.
            $this->clientError(_('No such user.'), 404);
        }

        try {
            $imagefile = ImageFile::fromUpload('image');
        } catch (Exception $e) {
            $this->clientError($e->getMessage());
        }

        $type = $imagefile->preferredType();
        $filename = Avatar::filename(
            $user->id,
            image_type_to_extension($type),
            null,
            'tmp'.common_timestamp()
        );

        $filepath = Avatar::path($filename);

        $imagefile->copyTo($filepath);

        $profile = $this->user->getProfile();
        $profile->setOriginal($filename);

        $twitter_user = $this->twitterUserArray($profile, true);

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
}
?>