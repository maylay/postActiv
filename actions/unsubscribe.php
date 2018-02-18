<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: Unsubscribe
 * Unsubscribe handler
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
 * Unsubscribe handler
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Robin Millette
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
// Class: UnsubscribeAction
// Action class for unsubscribing from a user or group
class UnsubscribeAction extends Action
{
   // -------------------------------------------------------------------------
   // Function: handle
   // Do the actual unsubscription, then write the HTML saying it succeeded.
   //
   // Error States:
   // o raises a clientError if not logged in or CSRF check fails
   // o redirects to the subscription form if the HTTP method isn't POST
   // o raises a clientError if there is no profile ID supplied or its invalid
   function handle()
   {
      parent::handle();
      if (!common_logged_in()) {
         // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
         $this->clientError(_('Not logged in.'));
      }
      if ($_SERVER['REQUEST_METHOD'] != 'POST') {
         common_redirect(common_local_url('subscriptions',
                                          array('nickname' => $this->scoped->nickname)));
      }

      /* Use a session token for CSRF protection. */
      $token = $this->trimmed('token');
      if (!$token || $token != common_session_token()) {
          // TRANS: Client error displayed when the session token does not match or is not given.
          $this->clientError(_('There was a problem with your session token. ' .
                               'Try again, please.'));
      }

      $other_id = $this->arg('unsubscribeto');
      if (!$other_id) {
         // TRANS: Client error displayed when trying to unsubscribe without providing a profile ID.
         $this->clientError(_('No profile ID in request.'));
      }

      $other = Profile::getKV('id', $other_id);
      if (!($other instanceof Profile)) {
         // TRANS: Client error displayed when trying to unsubscribe while providing a non-existing profile ID.
         $this->clientError(_('No profile with that ID.'));
      }

      try {
         Subscription::cancel($this->scoped, $other);
      } catch (Exception $e) {
         $this->clientError($e->getMessage());
      }

      if ($this->boolean('ajax')) {
         $this->startHTML('text/xml;charset=utf-8');
         $this->elementStart('head');
         // TRANS: Page title for page to unsubscribe.
         $this->element('title', null, _('Unsubscribed'));
         $this->elementEnd('head');
         $this->elementStart('body');
         $subscribe = new SubscribeForm($this, $other);
         $subscribe->show();
         $this->elementEnd('body');
         $this->endHTML();
      } else {
            common_redirect(common_local_url('subscriptions', array('nickname' => $this->scoped->nickname)), 303);
      }
   }
}

// END OF FILE
// ============================================================================
?>