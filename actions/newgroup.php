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
 * Add a new group
 *
 * @category  Group
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Robin Millette <robin@millette.name>
 * @author    Eric Helgeson <erichelgeson@gmail.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Roland Haeder <roland@mxchange.org>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2009 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Add a new group
 *
 * This is the form for adding a new group
 */
class NewgroupAction extends FormAction
{
    protected $group;

    protected $form = 'GroupEdit';

    function getGroup() {
        return $this->group;
    }

    function title()
    {
        // TRANS: Title for form to create a group.
        return _('New group');
    }

    protected function doPreparation()
    {
        // $this->scoped is the current user profile
        if (!$this->scoped->hasRight(Right::CREATEGROUP)) {
            // TRANS: Client exception thrown when a user tries to create a group while banned.
            $this->clientError(_('You are not allowed to create groups on this site.'), 403);
        }
    }

    protected function getInstructions()
    {
        // TRANS: Form instructions for group create form.
        return _('Use this form to create a new group.');
    }

    protected function doPost()
    {
        if (Event::handle('StartGroupSaveForm', array($this))) {
            $nickname = Nickname::normalize($this->trimmed('newnickname'), true);

            $fullname    = $this->trimmed('fullname');
            $homepage    = $this->trimmed('homepage');
            $description = $this->trimmed('description');
            $location    = $this->trimmed('location');
            $private     = $this->boolean('private');
            $aliasstring = $this->trimmed('aliases');

            if (!is_null($homepage) && (strlen($homepage) > 0) &&
                       !common_valid_http_url($homepage)) {
                // TRANS: Group create form validation error.
                throw new ClientException(_('Homepage is not a valid URL.'));
            } else if (!is_null($fullname) && mb_strlen($fullname) > 255) {
                // TRANS: Group create form validation error.
                throw new ClientException(_('Full name is too long (maximum 255 characters).'));
            } else if (User_group::descriptionTooLong($description)) {
                // TRANS: Group create form validation error.
                // TRANS: %d is the maximum number of allowed characters.
                throw new ClientException(sprintf(_m('Description is too long (maximum %d character).',
                                           'Description is too long (maximum %d characters).',
                                           User_group::maxDescription()),
                                        User_group::maxDescription()));
            } else if (!is_null($location) && mb_strlen($location) > 255) {
                // TRANS: Group create form validation error.
                throw new ClientException(_('Location is too long (maximum 255 characters).'));
            }

            if (!empty($aliasstring)) {
                $aliases = array_map(array('Nickname', 'normalize'), array_unique(preg_split('/[\s,]+/', $aliasstring)));
            } else {
                $aliases = array();
            }

            if (count($aliases) > common_config('group', 'maxaliases')) {
                // TRANS: Group create form validation error.
                // TRANS: %d is the maximum number of allowed aliases.
                throw new ClientException(sprintf(_m('Too many aliases! Maximum %d allowed.',
                                           'Too many aliases! Maximum %d allowed.',
                                           common_config('group', 'maxaliases')),
                                        common_config('group', 'maxaliases')));
            }

            if ($private) {
                $force_scope = 1;
                $join_policy = User_group::JOIN_POLICY_MODERATE;
            } else {
                $force_scope = 0;
                $join_policy = User_group::JOIN_POLICY_OPEN;
            }

            // This is set up in parent->prepare and checked in self->prepare
            assert(!is_null($this->scoped));

            $group = User_group::register(array('nickname' => $nickname,
                                                'fullname' => $fullname,
                                                'homepage' => $homepage,
                                                'description' => $description,
                                                'location' => $location,
                                                'aliases'  => $aliases,
                                                'userid'   => $this->scoped->id,
                                                'join_policy' => $join_policy,
                                                'force_scope' => $force_scope,
                                                'local'    => true));

            $this->group = $group;

            Event::handle('EndGroupSaveForm', array($this));

            common_redirect($group->homeUrl(), 303);
        }
    }
}
?>