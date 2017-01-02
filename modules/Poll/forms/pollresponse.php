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
 * Form for adding a new poll
 * ----------------------------------------------------------------------------
 * @category  Polls
 * @package   postActiv
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 * ============================================================================
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Form to add a new poll thingy
 */
class PollResponseForm extends Form
{
    protected $poll;

    /**
     * Construct a new poll form
     *
     * @param Poll $poll
     * @param HTMLOutputter $out         output channel
     *
     * @return void
     */
    function __construct(Poll $poll, HTMLOutputter $out)
    {
        parent::__construct($out);
        $this->poll = $poll;
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'pollresponse-form';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'form_settings ajax';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('respondpoll', array('id' => $this->poll->id));
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $poll = $this->poll;
        $out = $this->out;
        $id = "poll-" . $poll->id;

        $out->element('p', 'poll-question', $poll->question);
        $out->elementStart('ul', 'poll-options');
        foreach ($poll->getOptions() as $i => $opt) {
            $out->elementStart('li');
            $out->elementStart('label');
            $out->element('input', array('type' => 'radio', 'name' => 'pollselection', 'value' => $i + 1), '');
            $out->text(' ' . $opt);
            $out->elementEnd('label');
            $out->elementEnd('li');
        }
        $out->elementEnd('ul');
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
        // TRANS: Button text for submitting a poll response.
        $this->out->submit('poll-response-submit', _m('BUTTON', 'Submit'), 'submit', 'submit');
    }
}
?>