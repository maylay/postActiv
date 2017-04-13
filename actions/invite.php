<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: Invite
 * Send an invitation to join the instance to an email address given.
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
 *
 * <https://www.gnu.org/licenses/agpl.html>
 * ----------------------------------------------------------------------------
 * About:
 * Send an invitation to join the instance to an email address given.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Zach Copley
 * o Sarven Capasdisli
 * o Jeffrey To <jeffery.to@gmail.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

// ============================================================================
// Class: InviteAction
// Action class to display invite send form and a received invite
//
// Variables:
// o mode - current mode
// o error - last error if any
// o already - if an invite was already sent to this address
// o subbed - if the invited user was subbed to inviter
// o sent - if the invite was sent
class InviteAction extends Action {
   var $mode = null;
   var $error = null;
   var $already = null;
   var $subbed = null;
   var $sent = null;


   // -------------------------------------------------------------------------
   // Function: showNoticeForm
   // Dummy function because we don't use this in this Action
   //
   // Returns:
   // o void
   function showNoticeForm() {
      return;
   }


   // -------------------------------------------------------------------------
   // Function: isReadOnly
   // Function to indicate this class both reads and writes
   //
   // Returns:
   // o boolean False
   function isReadOnly($args) {
      return false;
   }


   // -------------------------------------------------------------------------
   // Function: handle
   // Display the invite form or send an invite as appropriate
   //
   // Returns:
   // o void
   //
   // Error states:
   // If invites are disabled or you are not logged in, throws a clientError
   function handle() {
      parent::handle();
      if (!common_config('invite', 'enabled')) {
         // TRANS: Client error displayed when trying to sent invites while they have been disabled.
         $this->clientError(_('Invites have been disabled.'));
      } else if (!common_logged_in()) {
         // TRANS: Client error displayed when trying to sent invites while not logged in.
         // TRANS: %s is the StatusNet site name.
         $this->clientError(sprintf(_('You must be logged in to invite other users to use %s.'),
                                      common_config('site', 'name')));
         return;
      } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
          $this->sendInvitations();
      } else {
          $this->showForm();
      }
   }


   // -------------------------------------------------------------------------
   // Function: sendInvitations
   // Meat of the system, constructs and sends an invitation email.
   //
   // Returns:
   // o void
   //
   function sendInvitations() {
      if (Event::handle('StartSendInvitations', array(&$this))) {
         // CSRF protection
         $token = $this->trimmed('token');
         if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->showForm(_('There was a problem with your session token. Try again, please.'));
            return;
         }

         $user = common_current_user();
         $profile = $user->getProfile();
         $bestname = $profile->getBestName();
         $sitename = common_config('site', 'name');
         $personal = $this->trimmed('personal');
         $addresses = explode("\n", $this->trimmed('addresses'));
         
         foreach ($addresses as $email) {
            $email = trim($email);
            $valid = null;

            try {
               if (Event::handle('StartValidateUserEmail', array(null, $email, &$valid))) {
                  $valid = Validate::email($email, common_config('email', 'check_domain'));
                  Event::handle('EndValidateUserEmail', array(null, $email, &$valid));
               }
               if ($valid) {
                  if (Event::handle('StartValidateEmailInvite', array($user, $email, &$valid))) {
                     $valid = true;
                     Event::handle('EndValidateEmailInvite', array($user, $email, &$valid));
                  }
               }
               if (!$valid) {
                  // TRANS: Form validation message when providing an e-mail address that does not validate.
                  // TRANS: %s is an invalid e-mail address.
                  $this->showForm(sprintf(_('Invalid email address: %s.'), $email));
                  return;
               }
            } catch (ClientException $e) {
               $this->showForm($e->getMessage());
               return;
            }
         }
         $this->already = array();
         $this->subbed = array();

         foreach ($addresses as $email) {
            $email = common_canonical_email($email);
            try {
               // If this user is already registered, subscribe to it!
               $other = Profile::getByEmail($email);
               if ($user->isSubscribed($other)) {
                  $this->already[] = $other;
               } else {
                  try {
                     Subscription::ensureStart($profile, $other);
                     $this->subbed[] = $other;
                  } catch (Exception $e) {
                     // subscription failed, but keep working
                     common_debug('Invitation-based subscription failed: '.$e->getMessage());
                  }
               }
            } catch (NoSuchUserException $e) {
               // If email was not known, let's send an invite!
               $this->sent[] = $email;
               $this->sendInvitation($email, $user, $personal);
            }
         }
         $this->mode = 'sent';
         $this->showPage();
         Event::handle('EndSendInvitations', array($this));
      }
   }


   // -------------------------------------------------------------------------
   // Function: showScripts
   // Loads the scripts neccesary for the invite page
   //
   // Returns:
   // o void
   function showScripts() {
      parent::showScripts();
      $this->autofocus('addresses');
   }


   // -------------------------------------------------------------------------
   // Function: title
   // Returns the title of the invitation page
   function title() {
      if ($this->mode == 'sent') {
         // TRANS: Page title when invitations have been sent.
         return _('Invitations sent');
      } else {
         // TRANS: Page title when inviting potential users.
         return _('Invite new users');
      }
   }


   // -------------------------------------------------------------------------
   // Function: showContent
   // Constructs the HTML of the invite form or for a message the invites were
   // sent, as appropriate.
   //
   // Returns:
   // o void
   function showContent() {
      if ($this->mode == 'sent') {
         $this->showInvitationSuccess();
      } else {
         $this->showInviteForm();
      }
   }


   // -------------------------------------------------------------------------
   // Function: showInvitationSuccess
   // Subfunction for the code to show the invitation was successful.
   //
   // Returns:
   // o void
   function showInvitationSuccess() {
      if (Event::handle('StartShowInvitationSuccess', array($this))) {
         if ($this->already) {
            // TRANS: Message displayed inviting users to use a StatusNet site while the inviting user
            // TRANS: is already subscribed to one or more users with the given e-mail address(es).
            // TRANS: Plural form is based on the number of reported already subscribed e-mail addresses.
            // TRANS: Followed by a bullet list.
            $this->element('p', null, _m('You are already subscribed to this user:',
                                         'You are already subscribed to these users:',
                                         count($this->already)));
            $this->elementStart('ul');
            foreach ($this->already as $other) {
               // TRANS: Used as list item for already subscribed users (%1$s is nickname, %2$s is e-mail address).
               $this->element('li', null, sprintf(_m('INVITE','%1$s (%2$s)'), $other->nickname, $other->email));
            }
            $this->elementEnd('ul');
         }
         if ($this->subbed) {
            // TRANS: Message displayed inviting users to use a StatusNet site while the invited user
            // TRANS: already uses a this StatusNet site. Plural form is based on the number of
            // TRANS: reported already present people. Followed by a bullet list.
            $this->element('p', null, _m('This person is already a user and you were automatically subscribed:',
                                         'These people are already users and you were automatically subscribed to them:',
                                          count($this->subbed)));
            $this->elementStart('ul');
            foreach ($this->subbed as $other) {
               // TRANS: Used as list item for already registered people (%1$s is nickname, %2$s is e-mail address).
               $this->element('li', null, sprintf(_m('INVITE','%1$s (%2$s)'), $other->nickname, $other->email));
            }
            $this->elementEnd('ul');
         }
         if ($this->sent) {
            // TRANS: Message displayed inviting users to use a StatusNet site. Plural form is
            // TRANS: based on the number of invitations sent. Followed by a bullet list of
            // TRANS: e-mail addresses to which invitations were sent.
            $this->element('p', null, _m('Invitation sent to the following person:',
                                          'Invitations sent to the following people:',
                                          count($this->sent)));
            $this->elementStart('ul');
            foreach ($this->sent as $other) {
               $this->element('li', null, $other);
            }
            $this->elementEnd('ul');
            // TRANS: Generic message displayed after sending out one or more invitations to
            // TRANS: people to join a StatusNet site.
            $this->element('p', null, _('You will be notified when your invitees accept the invitation and register on the site. Thanks for growing the community!'));
         }
         Event::handle('EndShowInvitationSuccess', array($this));
      }
   }


   // Function: showPageNotice
   // Subfunction to display errors, or the instructions for invites
   //
   // Returns:
   // o void
   function showPageNotice() {
      if ($this->mode != 'sent') {
         if ($this->error) {
            $this->element('p', 'error', $this->error);
         } else {
            $this->elementStart('div', 'instructions');
            $this->element('p', null,
                            // TRANS: Form instructions.
                            _('Use this form to invite your friends and colleagues to use this service.'));
            $this->elementEnd('div');
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: showForm
   // Switches the invite system into invite form display mode
   //
   // Returns:
   // o void
   function showForm($error=null) {
      $this->mode = 'form';
      $this->error = $error;
      $this->showPage();
   }


   // -------------------------------------------------------------------------
   // Function: showInviteForm
   // Displays the actual invite form
   //
   // Returns:
   // o void
   function showInviteForm() {
      if (Event::handle('StartShowInviteForm', array($this))) {
         $form = new InviteForm($this);
         $form->show();
         Event::handle('EndShowInviteForm', array($this));
      }
   }


   // -------------------------------------------------------------------------
   // Function: sendInvitation
   // Subfunction to construct and send a single invitation
   //
   // Returns:
   // o void
   //
   // Error States:
   // o if it couldn't insert the invitation record into the DB, it logs this and
   //   returns false
   function sendInvitation($email, $user, $personal) {
      $profile = $user->getProfile();
      $bestname = $profile->getBestName();
      $sitename = common_config('site', 'name');
      $invite = new Invitation();
      $invite->address = $email;
      $invite->address_type = 'email';
      $invite->code = common_confirmation_code(128);
      $invite->user_id = $user->id;
      $invite->created = common_sql_now();

      if (!$invite->insert()) {
         common_log_db_error($invite, 'INSERT', __FILE__);
         return false;
      }

      $confirmUrl = common_local_url('register', array('code' => $invite->code));
      $recipients = array($email);
      $headers['From'] = mail_notify_from();
      $headers['To'] = trim($email);
      $headers['Content-Type'] = 'text/html; charset=UTF-8';

      // TRANS: Subject for invitation email. Note that 'them' is correct as a gender-neutral
      // TRANS: singular 3rd-person pronoun in English. %1$s is the inviting user, $2$s is
      // TRANS: the StatusNet sitename.
      $headers['Subject'] = sprintf(_('%1$s has invited you to join them on %2$s'), $bestname, $sitename);
      $title = (empty($personal)) ? 'invite' : 'invitepersonal';

      // @todo FIXME: i18n issue.
      $inviteTemplate = DocFile::forTitle($title, DocFile::mailPaths());

      $body = $inviteTemplate->toHTML(array('inviter' => $bestname,
                                            'inviterurl' => $profile->profileurl,
                                            'confirmurl' => $confirmUrl,
                                            'personal' => $personal));

      common_debug('Confirm URL is ' . common_local_url('register', array('code' => $invite->code)));
      mail_send($recipients, $headers, $body);
   }
}

// END OF FILE
// ============================================================================
?>