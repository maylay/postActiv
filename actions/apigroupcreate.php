<?php
/* ============================================================================
 * Title: APIGroupCreate
 * Create a group via the API
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 * Create a group via the API
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Brion Vibber <brion@pobox.com>
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


/**
 * Make a new group. Sets the authenticated user as the administrator of the group.
 */
class ApiGroupCreateAction extends ApiAuthAction
{
    protected $needPost = true;

    var $group       = null;
    var $nickname    = null;
    var $fullname    = null;
    var $homepage    = null;
    var $description = null;
    var $location    = null;
    var $aliasstring = null;
    var $aliases     = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $this->nickname    = Nickname::normalize($this->arg('nickname'), true);
        $this->fullname    = $this->arg('full_name');
        $this->homepage    = $this->arg('homepage');
        $this->description = $this->arg('description');
        $this->location    = $this->arg('location');
        $this->aliasstring = $this->arg('aliases');

        return true;
    }

    /**
     * Handle the request
     *
     * Save the new group
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (empty($this->user)) {
            // TRANS: Client error given when a user was not found (404).
            $this->clientError(_('No such user.'), 404);
        }

        if ($this->validateParams() == false) {
            return;
        }

        $group = User_group::register(array('nickname' => $this->nickname,
                                            'fullname' => $this->fullname,
                                            'homepage' => $this->homepage,
                                            'description' => $this->description,
                                            'location' => $this->location,
                                            'aliases'  => $this->aliases,
                                            'userid'   => $this->user->id,
                                            'local'    => true));

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlGroup($group);
            break;
        case 'json':
            $this->showSingleJsonGroup($group);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }

    /**
     * Validate params for the new group
     *
     * @return void
     */
    function validateParams()
    {
        if (!is_null($this->homepage)
                && strlen($this->homepage) > 0
                && !common_valid_http_url($this->homepage)) {
            // TRANS: Client error in form for group creation.
            $this->clientError(_('Homepage is not a valid URL.'), 403);

        } elseif (!is_null($this->fullname)
                && mb_strlen($this->fullname) > 255) {
            // TRANS: Client error in form for group creation.
            $this->clientError(_('Full name is too long (maximum 255 characters).'), 403);

        } elseif (User_group::descriptionTooLong($this->description)) {
            // TRANS: Client error shown when providing too long a description during group creation.
            // TRANS: %d is the maximum number of allowed characters.
            $this->clientError(sprintf(_m('Description is too long (maximum %d character).',
                                'Description is too long (maximum %d characters).',
                                User_group::maxDescription()), User_group::maxDescription()), 403);

        } elseif (!is_null($this->location)
                && mb_strlen($this->location) > 255) {
            // TRANS: Client error shown when providing too long a location during group creation.
            $this->clientError(_('Location is too long (maximum 255 characters).'), 403);
        }

        if (!empty($this->aliasstring)) {
            $this->aliases = array_map(
                array('Nickname', 'normalize'), // static call to Nickname::normalize
                array_unique(preg_split('/[\s,]+/', $this->aliasstring))
            );
        } else {
            $this->aliases = array();
        }

        if (count($this->aliases) > common_config('group', 'maxaliases')) {
            $this->clientError(sprintf(
                    // TRANS: Client error shown when providing too many aliases during group creation.
                    // TRANS: %d is the maximum number of allowed aliases.
                    _m('Too many aliases! Maximum %d allowed.',
                       'Too many aliases! Maximum %d allowed.',
                       common_config('group', 'maxaliases')),
                    common_config('group', 'maxaliases')),
                403);
        }

        // Everything looks OK

        return true;
    }
}

// END OF FILE
// ============================================================================
?>