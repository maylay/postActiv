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
 * A form for backing up the account.
 *
 * @category  Account
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2009-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

class BackupAccountForm extends Form
{
    /**
     * Class of the form.
     *
     * @return string the form's class
     */
    function formClass()
    {
        return 'form_profile_backup';
    }

    /**
     * URL the form posts to
     *
     * @return string the form's action URL
     */
    function action()
    {
        return common_local_url('backupaccount');
    }

    /**
     * Output form data
     *
     * Really, just instructions for doing a backup.
     *
     * @return void
     */
    function formData()
    {
        $msg =
            // TRANS: Information displayed on the backup account page.
            _('You can backup your account data in '.
              '<a href="http://activitystrea.ms/">Activity Streams</a> '.
              'format. This is an experimental feature and provides an '.
              'incomplete backup; private account '.
              'information like email and IM addresses is not backed up. '.
              'Additionally, uploaded files and direct messages are not '.
              'backed up.');
        $this->out->elementStart('p');
        $this->out->raw($msg);
        $this->out->elementEnd('p');
    }

    /**
     * Buttons for the form
     *
     * In this case, a single submit button
     *
     * @return void
     */
    function formActions()
    {
        $this->out->submit('submit',
                           // TRANS: Submit button to backup an account on the backup account page.
                           _m('BUTTON', 'Backup'),
                           'submit',
                           null,
                           // TRANS: Title for submit button to backup an account on the backup account page.
                           _('Backup your account.'));
    }
}
?>