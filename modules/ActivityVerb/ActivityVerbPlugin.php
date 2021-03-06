<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Plugin that handles activity verb interact (like 'favorite' etc.)
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
 * @category  Plugin
 * @package   GNUsocial
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @copyright 2014 Free Software Foundation http://fsf.org
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      https://www.gnu.org/software/social/
 */

if (!defined('GNUSOCIAL')) { exit(1); }

class ActivityVerbPlugin extends Plugin
{

    public function onRouterInitialized(URLMapper $m)
    {
        $m->connect('notice/:id/:verb',
                    array('action' => 'activityverb'),
                    array('id'     => '[0-9]+',
                          'verb'   => '[a-z]+'));
        $m->connect('activity/:id/:verb',
                    array('action' => 'activityverb'),
                    array('id'     => '[0-9]+',
                          'verb'   => '[a-z]+'));
    }

    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'Activity Verb',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'https://www.gnu.org/software/social/',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Adds more standardized verb handling for activities.'));
        return true;
    }
}
