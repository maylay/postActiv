<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
class NewpollForm extends Form
{
    protected $question = null;
    protected $options = array();

    /**
     * Construct a new poll form
     *
     * @param HTMLOutputter $out         output channel
     *
     * @return void
     */
    function __construct($out=null, $question=null, $options=null)
    {
        parent::__construct($out);
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'newpoll-form';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'form_settings ajax-notice';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('newpoll');
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $this->out->elementStart('fieldset', array('id' => 'newpoll-data'));
        $this->out->elementStart('ul', 'form_data');

        $this->li();
        $this->out->input('question',
                          // TRANS: Field label on the page to create a poll.
                          _m('Question'),
                          $this->question,
                          // TRANS: Field title on the page to create a poll.
                          _m('What question are people answering?'),
                          'question',
                          true);    // HTML5 "required" attribute
        $this->unli();

        $max = 5;
        if (count($this->options) + 1 > $max) {
            $max = count($this->options) + 2;
        }
        for ($i = 0; $i < $max; $i++) {
            // @fixme make extensible
            if (isset($this->options[$i])) {
                $default = $this->options[$i];
            } else {
                $default = '';
            }
            $this->li();
            $this->out->input('poll-option' . ($i + 1),
                              // TRANS: Field label for an answer option on the page to create a poll.
                              // TRANS: %d is the option number.
                              sprintf(_m('Option %d'), $i + 1),
                              $default,
                              null,
                              'option' . ($i + 1),
                              $i<2);   // HTML5 "required" attribute for 2 options
            $this->unli();
        }

        $this->out->elementEnd('ul');

        $toWidget = new ToSelector($this->out,
                                   common_current_user(),
                                   null);
        $toWidget->show();

        $this->out->elementEnd('fieldset');
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
        // TRANS: Button text for saving a new poll.
        $this->out->submit('poll-submit', _m('BUTTON', 'Save'), 'submit', 'submit');
    }
}
?>