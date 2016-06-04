<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Section for an invite button
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
 * @category  Section
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Invite button
 *
 * @category  Section
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class InviteButtonSection extends Section
{
    protected $buttonText;

    function __construct($out = null, $buttonText = null)
    {
        $this->out = $out;
        if (empty($buttonText)) {
            // TRANS: Default button text for inviting more users to the StatusNet instance.
            $this->buttonText = _m('BUTTON', 'Invite more people');
        } else {
            $this->buttonText = $buttonText;
        }
    }

    function showTitle()
    {
        return false;
    }

    function divId()
    {
        return 'invite_button';
    }

    function showContent()
    {
        $this->out->element(
            'a',
            array(
                'href' => common_local_url('invite'),
                'class' => 'invite_button'
            ),
            $this->buttonText
        );
        return false;
    }
}
?>