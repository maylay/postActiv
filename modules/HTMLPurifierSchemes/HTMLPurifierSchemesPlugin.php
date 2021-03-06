<?php
/*
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 * @license   https://www.gnu.org/licenses/agpl.html
 */

if (!defined('GNUSOCIAL')) { exit(1); }

// because autoloading classes won't work otherwise
require_once INSTALLDIR.'/extlib/HTMLPurifier/HTMLPurifier.auto.php';

/**
 * @package     Activity
 * @maintainer  Mikael Nordfeldth <mmn@hethane.se>
 */
class HTMLPurifierSchemesPlugin extends Plugin
{
    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'HTMLPurifier Schemes',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'https://gnu.io/social',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Additional URI schemes for HTMLPurifier.'));

        return true;
    }
}
