<?php
/* ============================================================================
 * Title: DeleteNotice
 * Form to delete a notice
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2017, Maiyannah Bishop
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
 * ----------------------------------------------------------------------------
 * About:
 * Form to delete a notice
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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

class DeletenoticeForm extends Form {
   protected $notice = null;

   function __construct(HTMLOutputter $out=null, array $formOpts=array()) {
      if (!array_key_exists('notice', $formOpts) || !$formOpts['notice'] instanceof Notice) {
         throw new ServerException('No notice provided to DeletenoticeForm');
      }

      parent::__construct($out);
      $this->notice = $formOpts['notice'];
   }

   function id() {
      return 'form_notice_delete-' . $this->notice->getID();
   }

   function formClass() {
      return 'form_settings';
   }

   function action() {
      return common_local_url('deletenotice', array('notice' => $this->notice->getID()));
   }

   function formLegend() {
      $this->out->element('legend', null, _('Delete notice'));
   }

   function formData() {
      $this->out->element('p', null, _('Are you sure you want to delete this notice?'));
   }

   /**
   * Action elements
   *
   * @return void
   */
   function formActions()
   {
        $this->out->submit('form_action-no',
                      // TRANS: Button label on the delete notice form.
                      _m('BUTTON','No'),
                      'submit form_action-primary',
                      'no',
                      // TRANS: Submit button title for 'No' when deleting a notice.
                      _('Do not delete this notice.'));
        $this->out->submit('form_action-yes',
                      // TRANS: Button label on the delete notice form.
                      _m('BUTTON','Yes'),
                      'submit form_action-secondary',
                      'yes',
                      // TRANS: Submit button title for 'Yes' when deleting a notice.
                      _('Delete this notice.'));
   }
}

// END OF FILE
// ============================================================================
?>