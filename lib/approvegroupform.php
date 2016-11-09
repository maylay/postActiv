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
 * Form for leaving a group
 *
 * @category  Form
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Sarven Capadisli
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 *
 * @see      UnsubscribeForm 
 */

if (!defined('POSTACTIV')) { exit(1); }

require_once INSTALLDIR.'/lib/form.php';

/**
 * Form for leaving a group
 */
class ApproveGroupForm extends Form
{
    /**
     * group for user to leave
     */

    var $group = null;
    var $profile = null;

    /**
     * Constructor
     *
     * @param HTMLOutputter $out   output channel
     * @param group         $group group to leave
     */
    function __construct($out=null, $group=null, $profile=null)
    {
        parent::__construct($out);

        $this->group = $group;
        $this->profile = $profile;
    }

    /**
     * ID of the form
     *
     * @return string ID of the form
     */
    function id()
    {
        return 'group-queue-' . $this->group->id;
    }

    /**
     * class of the form
     *
     * @return string of the form class
     */
    function formClass()
    {
        return 'form_group_queue ajax';
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
        return common_local_url('approvegroup',
                                array('id' => $this->group->id), $params);
    }

    /**
     * Action elements
     *
     * @return void
     */

    function formActions()
    {
        // TRANS: Submit button text to accept a group membership request on approve group form.
        $this->out->submit('approve', _m('BUTTON','Accept'));
        // TRANS: Submit button text to reject a group membership request on approve group form.
        $this->out->submit('cancel', _m('BUTTON','Reject'));
    }
}
?>