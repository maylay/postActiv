<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * A version stamp for the API
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
 * @author    Evan Prodromou <evan@status.net>
 * @author    Zach Copley <zach@status.net>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2009-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Returns a version number for this version of GNU social, which
 * should make things a bit easier for upgrades.
 * URL: http://identi.ca/api/statusnet/version.(xml|json)
 * Formats: xml, js
 *
 * @category API
 * @package  GNUsocial
 * @author   Evan Prodromou <evan@status.net>
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://www.gnu.org/software/social/
 */
class ApiGNUsocialVersionAction extends ApiPrivateAuthAction
{
    protected function handle()
    {
        parent::handle();

        switch ($this->format) {
        case 'xml':
            $this->initDocument('xml');
            $this->element('version', null, GNUSOCIAL_VERSION);
            $this->endDocument('xml');
            break;
        case 'json':
            $this->initDocument('json');
            print '"'.GNUSOCIAL_VERSION.'"';
            $this->endDocument('json');
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
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