<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Form for blocking a user from a group
 *
 * PHP version 5
 *
 * @category Form
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @author   Sarven Capadisli <csarven@status.net>
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link     http://status.net/
 *
 * @see      BlockForm
 */
if (!defined('POSTACTIV')) { exit(1); } 

class GroupBlockForm extends Form
{
    /**
     * Profile of user to block
     */

    var $profile = null;

    /**
     * Group to block the user from
     */

    var $group = null;

    /**
     * Return-to args
     */

    var $args = null;

    /**
     * Constructor
     *
     * @param HTMLOutputter $out     output channel
     * @param Profile       $profile profile of user to block
     * @param User_group    $group   group to block user from
     * @param array         $args    return-to args
     */
    function __construct($out=null, $profile=null, $group=null, $args=null)
    {
        parent::__construct($out);

        $this->profile = $profile;
        $this->group   = $group;
        $this->args    = $args;
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        // This should be unique for the page.
        return 'block-' . $this->profile->id;
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'form_group_block';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('groupblock');
    }

    /**
     * Legend of the Form
     *
     * @return void
     */
    function formLegend()
    {
        // TRANS: Form legend for form to block user from a group.
        $this->out->element('legend', null, _('Block user from group'));
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $this->out->hidden('blockto-' . $this->profile->id,
                           $this->profile->id,
                           'blockto');
        $this->out->hidden('blockgroup-' . $this->group->id,
                           $this->group->id,
                           'blockgroup');
        if ($this->args) {
            foreach ($this->args as $k => $v) {
                $this->out->hidden('returnto-' . $k, $v);
            }
        }
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
        $this->out->submit(
            'submit',
            // TRANS: Button text for the form that will block a user from a group.
            _m('BUTTON','Block'),
            'submit',
            null,
            // TRANS: Submit button title.
            _m('TOOLTIP', 'Block this user so that they can no longer post messages to it.'));
    }
}
?>