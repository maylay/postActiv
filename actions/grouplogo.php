<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: GroupLogo
 * Upload an avatar for a group
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 *
 * <https://www.gnu.org/licenses/agpl.html>
 * ----------------------------------------------------------------------------
 * About:
 * Upload an avatar for a group
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Sarven Capadisli
 * o Meitar Moscovitz <meitarm@gmail.com>
 * o Sean Murphy <sgmurphy@gmail.com>
 * o Adrian Lang <mail@adrianlang.de>
 * o Zach Copley
 * o Craig Andrews <candrews@integralblue.com>
 * o Jeffery To <jeffery.to@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Akio Nishimura <akio@akionux.net>
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

/**
 * Upload an avatar
 *
 * We use jCrop plugin for jQuery to crop the image after upload.
 */
class GrouplogoAction extends GroupAction
{
    var $mode = null;
    var $imagefile = null;
    var $filename = null;
    var $msg = null;
    var $success = null;

    /**
     * Prepare to run
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        if (!common_logged_in()) {
            // TRANS: Client error displayed when trying to create a group while not logged in.
            $this->clientError(_('You must be logged in to create a group.'));
        }

        $nickname_arg = $this->trimmed('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            common_redirect(common_local_url('grouplogo', $args), 301);
        }

        if (!$nickname) {
            // TRANS: Client error displayed when trying to change group logo settings without providing a nickname.
            $this->clientError(_('No nickname.'), 404);
        }

        $groupid = $this->trimmed('groupid');

        if ($groupid) {
            $this->group = User_group::getKV('id', $groupid);
        } else {
            $local = Local_group::getKV('nickname', $nickname);
            if ($local) {
                $this->group = User_group::getKV('id', $local->group_id);
            }
        }

        if (!$this->group) {
            // TRANS: Client error displayed when trying to update logo settings for a non-existing group.
            $this->clientError(_('No such group.'), 404);
        }

        $cur = common_current_user();

        if (!$cur->isAdmin($this->group)) {
            // TRANS: Client error displayed when trying to change group logo settings while not being a group admin.
            $this->clientError(_('You must be an admin to edit the group.'), 403);
        }

        return true;
    }

    protected function handle()
    {
        parent::handle();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->handlePost();
        } else {
            $this->showForm();
        }
    }

    function showForm($msg = null, $success = false)
    {
        $this->msg     = $msg;
        $this->success = $success;

        $this->showPage();
    }

    /**
     * Title of the page
     *
     * @return string Title of the page
     */
    function title()
    {
        // TRANS: Title for group logo settings page.
        return _('Group logo');
    }

    /**
     * Instructions for use
     *
     * @return instructions for use
     */
    function getInstructions()
    {
        // TRANS: Instructions for group logo page.
        // TRANS: %s is the maximum file size for that site.
        return sprintf(_('You can upload a logo image for your group. The maximum file size is %s.'), ImageFile::maxFileSize());
    }

    /**
     * Content area of the page
     *
     * Shows a form for uploading an avatar.
     *
     * @return void
     */
    function showContent()
    {
        if ($this->mode == 'crop') {
            $this->showCropForm();
        } else {
            $this->showUploadForm();
        }
    }

    function showUploadForm()
    {
        $user = common_current_user();

        $profile = $user->getProfile();

        if (!$profile) {
            common_log_db_error($user, 'SELECT', __FILE__);
            // TRANS: Error message displayed when referring to a user without a profile.
            $this->serverError(_('User has no profile.'));
        }

        $original = $this->group->original_logo;

        $this->elementStart('form', array('enctype' => 'multipart/form-data',
                                          'method' => 'post',
                                          'id' => 'form_settings_avatar',
                                          'class' => 'form_settings',
                                          'action' =>
                                          common_local_url('grouplogo',
                                                           array('nickname' => $this->group->nickname))));
        $this->elementStart('fieldset');
        // TRANS: Group logo form legend.
        $this->element('legend', null, _('Group logo'));
        $this->hidden('token', common_session_token());

        $this->elementStart('ul', 'form_data');
        if ($original) {
            $this->elementStart('li', array('id' => 'avatar_original',
                                            'class' => 'avatar_view'));
            // TRANS: Uploaded original file in group logo form.
            $this->element('h2', null, _('Original'));
            $this->elementStart('div', array('id'=>'avatar_original_view'));
            $this->element('img', array('src' => $this->group->original_logo,
                                        'alt' => $this->group->nickname));
            $this->elementEnd('div');
            $this->elementEnd('li');
        }

        if ($this->group->homepage_logo) {
            $this->elementStart('li', array('id' => 'avatar_preview',
                                            'class' => 'avatar_view'));
            // TRANS: Header for preview of to be displayed group logo.
            $this->element('h2', null, _('Preview'));
            $this->elementStart('div', array('id'=>'avatar_preview_view'));
            $this->element('img', array('src' => $this->group->homepage_logo,
                                        'width' => AVATAR_PROFILE_SIZE,
                                        'height' => AVATAR_PROFILE_SIZE,
                                        'alt' => $this->group->nickname));
            $this->elementEnd('div');
            if (!empty($this->group->homepage_logo)) {
                // TRANS: Button on group logo upload page to delete current group logo.
                $this->submit('delete', _('Delete'));
            }
            $this->elementEnd('li');
        }

        $this->elementStart('li', array ('id' => 'settings_attach'));
        $this->element('input', array('name' => 'MAX_FILE_SIZE',
                                      'type' => 'hidden',
                                      'id' => 'MAX_FILE_SIZE',
                                      'value' => ImageFile::maxFileSizeInt()));
        $this->element('input', array('name' => 'avatarfile',
                                      'type' => 'file',
                                      'id' => 'avatarfile'));
        $this->elementEnd('li');
        $this->elementEnd('ul');

        $this->elementStart('ul', 'form_actions');
        $this->elementStart('li');
        // TRANS: Submit button for uploading a group logo.
        $this->submit('upload', _('Upload'));
        $this->elementEnd('li');
        $this->elementEnd('ul');

        $this->elementEnd('fieldset');
        $this->elementEnd('form');
    }

    function showCropForm()
    {
        $this->elementStart('form', array('method' => 'post',
                                          'id' => 'form_settings_avatar',
                                          'class' => 'form_settings',
                                          'action' =>
                                          common_local_url('grouplogo',
                                                           array('nickname' => $this->group->nickname))));
        $this->elementStart('fieldset');
        // TRANS: Legend for group logo settings fieldset.
        $this->element('legend', null, _('Avatar settings'));
        $this->hidden('token', common_session_token());

        $this->elementStart('ul', 'form_data');

        $this->elementStart('li',
                            array('id' => 'avatar_original',
                                  'class' => 'avatar_view'));
        // TRANS: Header for originally uploaded file before a crop on the group logo page.
        $this->element('h2', null, _('Original'));
        $this->elementStart('div', array('id'=>'avatar_original_view'));
        $this->element('img', array('src' => Avatar::url($this->filedata['filename']),
                                    'width' => $this->filedata['width'],
                                    'height' => $this->filedata['height'],
                                    'alt' => $this->group->nickname));
        $this->elementEnd('div');
        $this->elementEnd('li');

        $this->elementStart('li',
                            array('id' => 'avatar_preview',
                                  'class' => 'avatar_view'));
        // TRANS: Header for the cropped group logo on the group logo page.
        $this->element('h2', null, _('Preview'));
        $this->elementStart('div', array('id'=>'avatar_preview_view'));
        $this->element('img', array('src' => Avatar::url($this->filedata['filename']),
                                    'width' => AVATAR_PROFILE_SIZE,
                                    'height' => AVATAR_PROFILE_SIZE,
                                    'alt' => $this->group->nickname));
        $this->elementEnd('div');

        foreach (array('avatar_crop_x', 'avatar_crop_y',
                       'avatar_crop_w', 'avatar_crop_h') as $crop_info) {
            $this->element('input', array('name' => $crop_info,
                                          'type' => 'hidden',
                                          'id' => $crop_info));
        }

        // TRANS: Button text for cropping an uploaded group logo.
        $this->submit('crop', _('Crop'));

        $this->elementEnd('li');
        $this->elementEnd('ul');
        $this->elementEnd('fieldset');
        $this->elementEnd('form');
    }

    /**
     * Handle a post
     *
     * We mux on the button name to figure out what the user actually wanted.
     *
     * @return void
     */
    function handlePost()
    {
        // CSRF protection

        $token = $this->trimmed('token');
        if (!$token || $token != common_session_token()) {
            // TRANS: Form validation error message.
            $this->show_form(_('There was a problem with your session token. '.
                               'Try again, please.'));
            return;
        }

        if ($this->arg('upload')) {
            $this->uploadLogo();
        } else if ($this->arg('crop')) {
            $this->cropLogo();
        } else if ($this->arg('delete')) {
            $this->deleteLogo();
        } else {
            // TRANS: Form validation error message when an unsupported argument is used.
            $this->showForm(_('Unexpected form submission.'));
        }
    }

    /**
     * Handle an image upload
     *
     * Does all the magic for handling an image upload, and crops the
     * image by default.
     *
     * @return void
     */
    function uploadLogo()
    {
        try {
            $imagefile = ImageFile::fromUpload('avatarfile');
        } catch (Exception $e) {
            $this->showForm($e->getMessage());
            return;
        }

        $type = $imagefile->preferredType();
        $filename = Avatar::filename($this->group->id,
                                     image_type_to_extension($type),
                                     null,
                                     'group-temp-'.common_timestamp());

        $filepath = Avatar::path($filename);

        $imagefile->copyTo($filepath);

        $filedata = array('filename' => $filename,
                          'filepath' => $filepath,
                          'width' => $imagefile->width,
                          'height' => $imagefile->height,
                          'type' => $type);

        $_SESSION['FILEDATA'] = $filedata;

        $this->filedata = $filedata;

        $this->mode = 'crop';

        // TRANS: Form instructions on the group logo page.
        $this->showForm(_('Pick a square area of the image to be the logo.'),
                        true);
    }

    /**
     * Handle the results of jcrop.
     *
     * @return void
     */
    function cropLogo()
    {
        $filedata = $_SESSION['FILEDATA'];

        if (!$filedata) {
            // TRANS: Server error displayed trying to crop an uploaded group logo that is no longer present.
            $this->serverError(_('Lost our file data.'));
        }

        // If image is not being cropped assume pos & dimentions of original
        $dest_x = $this->arg('avatar_crop_x') ? $this->arg('avatar_crop_x'):0;
        $dest_y = $this->arg('avatar_crop_y') ? $this->arg('avatar_crop_y'):0;
        $dest_w = $this->arg('avatar_crop_w') ? $this->arg('avatar_crop_w'):$filedata['width'];
        $dest_h = $this->arg('avatar_crop_h') ? $this->arg('avatar_crop_h'):$filedata['height'];
        $size = min($dest_w, $dest_h, common_config('avatar', 'maxsize'));
        $box = array('width' => $size, 'height' => $size,
                     'x' => $dest_x,   'y' => $dest_y,
                     'w' => $dest_w,   'h' => $dest_h);

        $profile = $this->group->getProfile();

        $imagefile = new ImageFile(null, $filedata['filepath']);
        $filename = Avatar::filename($profile->getID(), image_type_to_extension($imagefile->preferredType()),
                                     $size, common_timestamp());

        $imagefile->resizeTo(Avatar::path($filename), $box);

        if ($profile->setOriginal($filename)) {
            @unlink($filedata['filepath']);
            unset($_SESSION['FILEDATA']);
            $this->mode = 'upload';
            // TRANS: Form success message after updating a group logo.
            $this->showForm(_('Logo updated.'), true);
        } else {
            // TRANS: Form failure message after failing to update a group logo.
            $this->showForm(_('Failed updating logo.'));
        }
    }

    /**
     * Get rid of the current group logo.
     *
     * @return void
     */
    function deleteLogo()
    {
        $orig = clone($this->group);
        Avatar::deleteFromProfile($this->group->getProfile());
        @unlink(Avatar::path(basename($this->group->original_logo)));
        @unlink(Avatar::path(basename($this->group->homepage_logo)));
        @unlink(Avatar::path(basename($this->group->stream_logo)));
        @unlink(Avatar::path(basename($this->group->mini_logo)));
        $this->group->original_logo=User_group::defaultLogo(AVATAR_PROFILE_SIZE);
        $this->group->homepage_logo=User_group::defaultLogo(AVATAR_PROFILE_SIZE);
        $this->group->stream_logo=User_group::defaultLogo(AVATAR_STREAM_SIZE);
        $this->group->mini_logo=User_group::defaultLogo(AVATAR_MINI_SIZE);
        $this->group->update($orig);

        // TRANS: Success message for deleting the group logo.
        $this->showForm(_('Logo deleted.'));
    }

    function showPageNotice()
    {
        if ($this->msg) {
            $this->element('div', ($this->success) ? 'success' : 'error',
                           $this->msg);
        } else {
            $inst   = $this->getInstructions();
            $output = common_markup_to_html($inst);

            $this->elementStart('div', 'instructions');
            $this->raw($output);
            $this->elementEnd('div');
        }
    }

    /**
     * Add the jCrop stylesheet
     *
     * @return void
     */
    function showStylesheets()
    {
        parent::showStylesheets();
        $this->cssLink($this->js_path . '/extlib/jquery-jcrop/css/jcrop.css','base','screen, projection, tv');
    }

    /**
     * Add the jCrop scripts
     *
     * @return void
     */
    function showScripts()
    {
        parent::showScripts();

        if ($this->mode == 'crop') {
            $this->script('extlib/jquery-jcrop/jcrop.js');
            $this->script('jcrop.go.js');
        }

        $this->autofocus('avatarfile');
    }
}

// END OF FILE
// ============================================================================
?>
