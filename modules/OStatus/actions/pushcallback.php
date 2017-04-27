<?php
/* ============================================================================
 * Title: PuSH Callback Action
 * Handler for callbacks to our PuSH hub
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
 * Handler for callbacks to our PUsH hub
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
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

class PushCallbackAction extends Action
{
   // -------------------------------------------------------------------------
   // Function: handle
   // Handle the callback
   protected function handle() {
      common_debug("New PuSH packet rec'd: " . json_encode($this));
      postActiv::setApi(true); // Minimize error messages to aid in debugging
      if ($this->checkIfBanned()) {
         common_log(LOG_INFO, "Received packet from server on sanctions list, discarding.");
         return null;
      }
      parent::handle();
      if ($this->isPost()) {
         return $this->handlePost();
      }
      return $this->handleGet();
   }


   // -------------------------------------------------------------------------
   // Function: checkIfBanned
   // Helper function to compare against a banned domains list and return true
   // if the host trying access the hub is banned.
   //
   // Returns:
   // o boolean True, if the feed matches to a banned instance
   // o boolean False, if the feed doesn't match to a banned instance
   protected function checkIfBanned() {
      // Get list of banned instances
		$configphpsettings = common_config('site','sanctions') ?: array();
		foreach($configphpsettings as $configphpsetting=>$value) {
			$settings[$configphpsetting] = $value;
		}
		$bans = $settings['banned_instances'];
      common_log(LOG_INFO, "Banned instances currently: " . json_encode($bans));

		// If we have no banned instances we can bail
      if ($bans==null) {
         return false;
      }

      // Map bans to an array
      $bans = explode(',', $bans);

      // Look up feed
      $feedid = $this->arg('feed');
      if (!$feedid) {
         // TRANS: Server exception thrown when referring to a non-existing or empty feed.
         throw new ServerException(_m('Empty or invalid feed id.'), 400);
      }
      $feedsub = FeedSub::getKV('id', $feedid);
      if (!$feedsub instanceof FeedSub) {
         // TRANS: Server exception. %s is a feed ID.
         throw new ServerException(sprintf(_m('Unknown PuSH feed id %s'),$feedid), 400);
      }
      common_debug("Feed for this packet: " . json_encode($feedsub));

      // Get feed URI
      $feed_loc = $feedsub->huburi;
      common_log(LOG_INFO, "Huburi detected: " . $feed_loc);

      // Return whether this feed from a banned instance?
      $is_banned = false;
      foreach ($bans as $banned_instance) {
         if (strpos($feed_loc, $banned_instance)) {
            $is_banned = true;
            break;
         }
      }
      common_log(LOG_INFO, "This packet's owning instance ban status: " . (($is_banned) ? "true" : "false"));
      return $is_banned;
   }


   // -------------------------------------------------------------------------
   // Function: handlePost
   // Handler for POST content updates from the hub
   function handlePost() {
      $feedid = $this->arg('feed');
      common_log(LOG_INFO, "POST for feed id $feedid");
      if (!$feedid) {
         // TRANS: Server exception thrown when referring to a non-existing or empty feed.
         throw new ServerException(_m('Empty or invalid feed id.'), 400);
      }

      $feedsub = FeedSub::getKV('id', $feedid);
      if (!$feedsub instanceof FeedSub) {
         // TRANS: Server exception. %s is a feed ID.
         throw new ServerException(sprintf(_m('Unknown PuSH feed id %s'),$feedid), 400);
      }

      $hmac = '';
      if (isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
         $hmac = $_SERVER['HTTP_X_HUB_SIGNATURE'];
      }

      $post = file_get_contents('php://input');

      // Queue this to a background process; we should return
      // as quickly as possible from a distribution POST.
      // If queues are disabled this'll process immediately.
      $data = array('feedsub_id' => $feedsub->id,
                    'post' => $post,
                    'hmac' => $hmac);
      $qm = QueueManager::get();
      $qm->enqueue($data, 'pushin');
   }


   // -------------------------------------------------------------------------
   // Function: handleGet
   // Handler for GET verification requests from the hub.
   function handleGet() {
      $mode = $this->arg('hub_mode');
      $topic = $this->arg('hub_topic');
      $challenge = $this->arg('hub_challenge');
      $lease_seconds = $this->arg('hub_lease_seconds');   // Must be >0 for PuSH 0.4! And only checked on mode='subscribe' of course
      common_log(LOG_INFO, __METHOD__ . ": sub verification mode: $mode topic: $topic challenge: $challenge lease_seconds: $lease_seconds");

      if ($mode != 'subscribe' && $mode != 'unsubscribe') {
         // TRANS: Client exception. %s is an invalid value for hub.mode.
         throw new ClientException(sprintf(_m('Bad hub.mode "$s".',$mode)), 404);
      }
      $feedsub = FeedSub::getKV('uri', $topic);
      if (!$feedsub instanceof FeedSub) {
         // TRANS: Client exception. %s is an invalid feed name.
         throw new ClientException(sprintf(_m('Bad hub.topic feed "%s".'),$topic), 404);
      }
      if ($mode == 'subscribe') {
         // We may get re-sub requests legitimately.
         if ($feedsub->sub_state != 'subscribe' && $feedsub->sub_state != 'active') {
             // TRANS: Client exception. %s is an invalid topic.
             throw new ClientException(sprintf(_m('Unexpected subscribe request for %s.'),$topic), 404);
         }
      } else {
         if ($feedsub->sub_state != 'unsubscribe') {
             // TRANS: Client exception. %s is an invalid topic.
             throw new ClientException(sprintf(_m('Unexpected unsubscribe request for %s.'),$topic), 404);
         }
      }
      if ($mode == 'subscribe') {
         if ($feedsub->sub_state == 'active') {
            common_log(LOG_INFO, __METHOD__ . ': sub update confirmed');
         } else {
            common_log(LOG_INFO, __METHOD__ . ': sub confirmed');
         }
         $feedsub->confirmSubscribe($lease_seconds);
      } else {
         common_log(LOG_INFO, __METHOD__ . ": unsub confirmed; deleting sub record for $topic");
         $feedsub->confirmUnsubscribe();
      }
      print $challenge;
   }
}

// END OF FILE
// ============================================================================
?>