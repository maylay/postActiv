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
 * Form for user poll preferences
 * ----------------------------------------------------------------------------
 * @category  Polls
 * @package   postActiv
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2015-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 * ============================================================================
 */

if (!defined('POSTACTIV')) { exit(1); }

class PollPrefsForm extends Form
{
   function __construct(Action $out, User_poll_prefs $prefs=null) {
      parent::__construct($out);
      $this->prefs = $prefs;
   }

   /* -------------------------------------------------------------------------
    * function formData
    *    Visible or invisible data elements
    *
    *    Display the form fields that make up the data of the form.
    *    Sub-classes should overload this to show their data.
    *
    *    @return void
    */
   function formData() {
      $this->elementStart('fieldset');
      $this->elementStart('ul', 'form_data');
      $this->elementStart('li');
      $this->checkbox('hide_responses',
                   _('Do not deliver poll responses to my home timeline'),
                   ($this->prefs instanceof User_poll_prefs && $this->prefs->hide_responses));
      $this->elementEnd('li');
      $this->elementEnd('ul');
      $this->elementEnd('fieldset');
   }

   /* -------------------------------------------------------------------------
    * function formActions
    *    Buttons for form actions
    *
    *    Submit and cancel buttons (or whatever)
    *    Sub-classes should overload this to show their own buttons.
    *
    *    @return void
    */
   function formActions() {
      $this->submit('submit', _('Save'));
   }

    /* ------------------------------------------------------------------------
     * function id
     *    ID of the form
     *
     *    Should be unique on the page. Sub-classes should overload this
     *    to show their own IDs.
     *
     *    @return int ID of the form
     */
    function id()
    {
        return 'form_poll_prefs';
    }

   /* -------------------------------------------------------------------------
    * function action()
    *    Action of the form.
    *
    *    URL to post to. Should be overloaded by subclasses to give
    *    somewhere to post to.
    *
    *    @return string URL to post to
    */
   function action() {
      return common_local_url('pollsettings');
   }

   /* -------------------------------------------------------------------------
    * function formClass
    *    CSS class of the form. May include space-separated list of multiple
    *    classes.
    *
    *    @return string the form's class
    */
   function formClass() {
      return 'form_settings';
   }
}
?>