<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Form for approving or reject a pending subscription request
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
 * @category  Form
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @author    Sarven Capadisli <csarven@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Form for approving or reject a pending subscription request
 *
 * @category Form
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @author   Sarven Capadisli <csarven@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 *
 * @see      UnsubscribeForm
 */
class ApproveSubForm extends Form
{
    var $profile = null;

    /**
     * Constructor
     *
     * @param HTMLOutputter $out   output channel
     * @param Profile       $profile user whose request to accept or drop
     */
    function __construct($out=null, $profile=null)
    {
        parent::__construct($out);

        $this->profile = $profile;
    }

    /**
     * ID of the form
     *
     * @return string ID of the form
     */
    function id()
    {
        return 'sub-queue-' . $this->profile->id;
    }

    /**
     * class of the form
     *
     * @return string of the form class
     */
    function formClass()
    {
        return 'form_sub_queue ajax';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        $params = array();
        if ($this->profile) {
            $params['profile_id'] = $this->profile->id;
        }
        return common_local_url('approvesub',
                                array(), $params);
    }

    /**
     * Action elements
     *
     * @return void
     */

    function formActions()
    {
        // TRANS: Submit button text to accept a subscription request on approve sub form.
        $this->out->submit($this->id().'-approve', _m('BUTTON','Accept'), 'submit approve', 'approve');
        // TRANS: Submit button text to reject a subscription request on approve sub form.
        $this->out->submit($this->id().'-cancel', _m('BUTTON','Reject'), 'submit cancel', 'cancel');
    }
}
?>