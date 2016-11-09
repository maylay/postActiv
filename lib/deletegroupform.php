<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Form for joining a group
 *
 * @category  Form
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Brion Vibber <brion@status.net>
 * @copyright 2009, 2010 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 *
 * @see      UnsubscribeForm
 * @fixme    merge a bunch of this stuff with similar form types to reduce boilerplate
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Form for deleting a group
 */
class DeleteGroupForm extends Form
{
    /**
     * group for user to delete
     */
    var $group = null;

    /**
     * Constructor
     *
     * @param HTMLOutputter $out   output channel
     * @param User_group    $group group to join
     */
    function __construct(HTMLOutputter $out=null, User_group $group=null)
    {
        parent::__construct($out);

        $this->group = $group;
    }

    /**
     * ID of the form
     *
     * @return string ID of the form
     */
    function id()
    {
        return 'group-delete-' . $this->group->getID();
    }

    /**
     * class of the form
     *
     * @return string of the form class
     */
    function formClass()
    {
        return 'form_group_delete';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('deletegroup', array('id' => $this->group->getID()));
    }

    function formData()
    {
        $this->out->hidden($this->id() . '-returnto-action', 'groupbyid', 'returnto-action');
        $this->out->hidden($this->id() . '-returnto-id', $this->group->getID(), 'returnto-id');
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
        // TRANS: Button text for deleting a group.
        $this->out->submit('submit', _m('BUTTON','Delete'));
    }
}
?>