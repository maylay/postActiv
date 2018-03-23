<?php
/* ============================================================================
 * Title: FeedSub
 * Class representation of an individual feed subscription
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
 * Class representation of an individual feed subscription
 *
 * FeedSub handles low-level PubHubSubbub (PuSH) subscriptions.
 * Higher-level behavior building OStatus stuff on top is handled
 * under Ostatus_profile.
 *
 * WebSub (previously PubSubHubbub/PuSH) subscription flow:
 *
 *     $profile->subscribe()
 *         sends a sub request to the hub...
 *
 *     main/push/callback
 *         hub sends confirmation back to us via GET
 *         We verify the request, then echo back the challenge.
 *         On our end, we save the time we subscribed and the lease expiration
 *
 *     main/push/callback
 *         hub sends us updates via POST
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chimo <chimo@chromic.org>
 * o Stephan Paul Weber <singpolyma@singpolyma.net>
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
// Class: FeedSub
// Table definition for an ostatus feed subscription
class FeedSub extends Managed_DataObject {
   public $__table = 'feedsub';

   public $id;
   public $uri;    // varchar(191)   not 255 because utf8mb4 takes more space

   // PuSH subscription data
   public $huburi;
   public $secret;
   public $sub_state; // subscribe, active, unsubscribe, inactive, nohub
   public $sub_start;
   public $sub_end;
   public $last_update;
   public $created;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array that represents the database schema of the FeedSub
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'serial', 'not null' => true, 'description' => 'FeedSub local unique id'),
             'uri' => array('type' => 'varchar', 'not null' => true, 'length' => 191, 'description' => 'FeedSub uri'),
             'huburi' => array('type' => 'text', 'description' => 'FeedSub hub-uri'),
             'secret' => array('type' => 'text', 'description' => 'FeedSub stored secret'),
             'sub_state' => array('type' => 'enum("subscribe","active","unsubscribe","inactive","nohub")', 'not null' => true, 'description' => 'subscription state'),
             'sub_start' => array('type' => 'datetime', 'description' => 'subscription start'),
             'sub_end' => array('type' => 'datetime', 'description' => 'subscription end'),
             'last_update' => array('type' => 'datetime', 'description' => 'when this record was last updated'),
             'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
             'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('id'),
         'unique keys' => array(
         'feedsub_uri_key' => array('uri'),),);
   }

   // -------------------------------------------------------------------------
   // Function: getUri
   // Returns the feed uri (http/https)
   //
   // Returns:
   // string URI
   //
   // Error States:
   // o throws a NoUriException if there is no saved URI of the FeedSub
   public function getUri() {
      if (empty($this->uri)) {
            throw new NoUriException($this);
      }
      return $this->uri;
   }


   // -------------------------------------------------------------------------
   // Function: getLeaseRemaining
   // Returns how long before the feedsub expires and must be renewed
   //
   // Returns:
   // o integer representing remaining time before expiry
   // o null if the sub is expired
   function getLeaseRemaining() {
      if (empty($this->sub_end)) {
            return null;
      }
      return strtotime($this->sub_end) - time();
   }


   // -------------------------------------------------------------------------
   // Function: isPuSH
   // Returns whether or not this is a PuSH feed
   //
   // Do we have a hub? Then we are a PuSH feed.
   // https://en.wikipedia.org/wiki/PubSubHubbub
   //
   // If huburi is empty, then doublecheck that we are not using
   // a fallback hub. If there is a fallback hub, it is only if the
   // sub_state is "nohub" that we assume it's not a PuSH feed.
   //
   // Returns:
   // o boolean True or False
   public function isPuSH() {
      if (empty($this->huburi)
             && (!common_config('feedsub', 'fallback_hub')
             || $this->sub_state === 'nohub')) {
         // Here we have no huburi set. Also, either there is no
         // fallback hub configured or sub_state is "nohub".
         return false;
      }
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: localProfile
   // Fetch the profile for a local user we are subscribed to
   //
   // Returns:
   // o Profile Local_profile
   public function localProfile() {
      if ($this->profile_id) {
         return Profile::getKV('id', $this->profile_id);
      }
      return null;
   }


   // -------------------------------------------------------------------------
   // Functionn: localGroup
   // Fetch the profile for a local group we are subscribed to
   //
   // Returns:
   // o Profile User_group
   // o null if the profile does not exist
   public function localGroup() {
      if ($this->group_id) {
         return User_group::getKV('id', $this->group_id);
      }
      return null;
   }


   // -------------------------------------------------------------------------
   // Function: ensureFeed
   // Run discovery on a URI to see if it is a feed we can subscribe to.
   //
   // Parameters:
   // o string $feeduri
   // 
   // Returns:
   // o created or existing FeedSub
   //
   // Error States:
   // o throws FeedSubException if feed is invalid or lacks PuSH setup
   public static function ensureFeed($feeduri) {
      $current = self::getKV('uri', $feeduri);
      if ($current instanceof FeedSub) {
            return $current;
      }

      $discover = new FeedDiscovery();
      $discover->discoverFromFeedURL($feeduri);

      $huburi = $discover->getHubLink();
      if (!$huburi && !common_config('feedsub', 'fallback_hub') && !common_config('feedsub', 'nohub')) {
         throw new FeedSubNoHubException();
      }

      $feedsub = new FeedSub();
      $feedsub->uri = $feeduri;
      $feedsub->huburi = $huburi;
      $feedsub->sub_state = 'inactive';
      $feedsub->created = common_sql_now();
      $feedsub->modified = common_sql_now();

      $result = $feedsub->insert();
      if ($result === false) {
         throw new FeedDBException($feedsub);
      }

      return $feedsub;
    }
    

   // -------------------------------------------------------------------------
   // Function: ensureHub
   // ensureHub will only do $this->update if !empty($this->id) because
   // otherwise the object has not been created yet.
   //
   // Parameters:
   // o bool $autorenew - Whether to autorenew the feed after ensuring the hub URL
   //
   // Returns:
   // o null - if actively avoiding the database
   // o int  - number of rows updated in the database (0 means untouched)
   //
   // Error States:
   // o throws ServerException if something went wrong when updating the database
   // o throws FeedSubNoHubException if no hub URL was discovered
   public function ensureHub($autorenew=false) {
      if ($this->sub_state !== 'inactive') {
         common_log(LOG_INFO, sprintf(__METHOD__ . ': Running hub discovery a possibly active feed in %s state for URI %s', _ve($this->sub_state), _ve($this->uri)));
      }

      $discover = new FeedDiscovery();
      $discover->discoverFromFeedURL($this->uri);
      $huburi = $discover->getHubLink();
      if (empty($huburi)) {
         // Will be caught and treated with if statements in regards to
         // fallback hub and feed polling (nohub) configuration.
         throw new FeedSubNoHubException();
      }

      // if we've already got a DB object stored, we want to UPDATE, not INSERT
      $orig = !empty($this->id) ? clone($this) : null;
      $old_huburi = $this->huburi;    // most likely null if we're INSERTing
      $this->huburi = $huburi;
      if (!empty($this->id)) {
         common_debug(sprintf(__METHOD__ . ': Feed uri==%s huburi before=%s after=%s (identical==%s)', _ve($this->uri), _ve($old_huburi), _ve($this->huburi), _ve($old_huburi===$this->huburi)));
         $result = $this->update($orig);
         if ($result === false) {
            // TODO: Get a DB exception class going...
            common_debug('Database update failed for FeedSub id=='._ve($this->id).' with new huburi: '._ve($this->huburi));
            throw new ServerException('Database update failed for FeedSub.');
         }
         if ($autorenew) {
            $this->renew();
         }
         return $result;
      }
      return null;    // we haven't done anything with the database
   }


   // -------------------------------------------------------------------------
   // Function: subscribe
   // Send a subscription request to the hub for this feed.
   // The hub will later send us a confirmation POST to /main/push/callback.
   //
   // Error States:
   // o throws ServerException if feed state is not valid
   public function subscribe() {
      if ($this->sub_state && $this->sub_state != 'inactive') {
         common_log(LOG_WARNING, sprintf('Attempting to (re)start PuSH subscription to %s in unexpected state %s', $this->getUri(), $this->sub_state));
      }
      if (!Event::handle('FeedSubscribe', array($this))) {
         // A plugin handled it
         return;
      }
      if (empty($this->huburi)) {
         if (common_config('feedsub', 'fallback_hub')) {
            // No native hub on this feed?
            // Use our fallback hub, which handles polling on our behalf.
         } else if (common_config('feedsub', 'nohub')) {
            // For this to actually work, we'll need some polling mechanism.
            // The FeedPoller plugin should take care of it.
            return;
         } else {
            // TRANS: Server exception.
            throw new ServerException(_m('Attempting to start PuSH subscription for feed with no hub.'));
         }
      }
      $this->doSubscribe('subscribe');
   }


   // -------------------------------------------------------------------------
   // Function: unsubscribe
   // Send a PuSH unsubscription request to the hub for this feed.
   // The hub will later send us a confirmation POST to /main/push/callback.
   //
   // Warning: this will cancel the subscription even if someone else in
   // the system is using it. Most callers will want garbageCollect() instead,
   // which confirms there's no uses left.
   //
   // Error States:
   // o throws ServerException if feed state is not valid
   public function unsubscribe() {
      if ($this->sub_state != 'active') {
         common_log(LOG_WARNING, sprintf('Attempting to (re)end PuSH subscription to %s in unexpected state %s', $this->getUri(), $this->sub_state));
      }
      if (!Event::handle('FeedUnsubscribe', array($this))) {
         // A plugin handled it
         return;
      }
      if (empty($this->huburi)) {
         if (common_config('feedsub', 'fallback_hub')) {
                // No native hub on this feed?
                // Use our fallback hub, which handles polling on our behalf.
         } else if (common_config('feedsub', 'nohub')) {
                // We need a feedpolling plugin (like FeedPoller) active so it will
                // set the 'nohub' state to 'inactive' for us.
                return;
         } else {
                // TRANS: Server exception.
                throw new ServerException(_m('Attempting to end PuSH subscription for feed with no hub.'));
         }
      }
      $this->doSubscribe('unsubscribe');
   }


   // -------------------------------------------------------------------------
   // Function: garbageCollect
   // Check if there are any active local uses of this feed, and if not then
   // make sure it's inactive, unsubscribing if necessary.
   //
   // Returns:
   // o boolean true if the subscription is now inactive, false if still active.
   //
   // Error States:
   // o throws NoProfileException in FeedSubSubscriberCount for missing Profile entries
   // o throws Exception if something goes wrong in unsubscribe() method
   public function garbageCollect() {
      if ($this->sub_state == '' || $this->sub_state == 'inactive') {
         // No active PuSH subscription, we can just leave it be.
         return true;
      }

      // PuSH subscription is either active or in an indeterminate state.
      // Check if we're out of subscribers, and if so send an unsubscribe.
      $count = 0;
      Event::handle('FeedSubSubscriberCount', array($this, &$count));
      
      if ($count > 0) {
         common_log(LOG_INFO, __METHOD__ . ': ok, ' . $count . ' user(s) left for ' . $this->getUri());
         return false;
      }
      common_log(LOG_INFO, __METHOD__ . ': unsubscribing, no users left for ' . $this->getUri());
      
      // Unsubscribe throws various Exceptions on failure
      $this->unsubscribe();
      return true;
   }


   // ------------------------------------------------------------------------
   // Function: renewalCheck
   // Search for FeedSubs that need renewed
   static public function renewalCheck() {
      $fs = new FeedSub();
      // the "" empty string check is because we historically haven't saved unsubscribed feeds as NULL
      $fs->whereAdd('sub_end IS NOT NULL AND sub_end!="" AND sub_end < NOW() + INTERVAL 1 day');
      if (!$fs->find()) { // find can be both false and 0, depending on why nothing was found
         throw new NoResultException($fs);
      }
      return $fs;
   }


   // ------------------------------------------------------------------------
   // Function: renew
   // Renews a feedsub, which is basically just running the subscription again.
   public function renew() {
      $this->subscribe();
   }


   // -------------------------------------------------------------------------
   // Function: doSubscribe
   // Setting to subscribe means it is _waiting_ to become active. This
   // cannot be done in a transaction because there is a chance that the
   // remote script we're calling (as in the case of PuSHpress) performs
   // the lookup _while_ we're POSTing data, which means the transaction
   // never completes (PushcallbackAction gets an 'inactive' state).
   //
   // Returns:
   // o boolean true when everything is ok (throws Exception on fail)
   //
   // Error States:
   // o throws Exception on failure, can be HTTPClient's or our own.
   protected function doSubscribe($mode) {
      $msg = null;    // carries descriptive error message to enduser (no remote data strings!)

      $orig = clone($this);
      if ($mode == 'subscribe') {
            $this->secret = common_random_hexstr(32);
      }
      $this->sub_state = $mode;
      $this->update($orig);
      unset($orig);

      try {
         $callback = common_local_url('pushcallback', array('feed' => $this->id));
         $headers = array('Content-Type: application/x-www-form-urlencoded');
         $post = array('hub.mode' => $mode,
                       'hub.callback' => $callback,
                       'hub.verify' => 'async',  // TODO: deprecated, remove when noone uses PuSH <0.4 (only 'async' method used there)
                       'hub.verify_token' => 'Deprecated-since-PuSH-0.4', // TODO: rm!
                       'hub.lease_seconds' => 2592000,   // 3600*24*30, request approximately month long lease (may be changed by hub)
                       'hub.secret' => $this->secret,
                       'hub.topic' => $this->getUri());
         $client = new HTTPClient();
         if ($this->huburi) {
            $hub = $this->huburi;
         } else {
            if (common_config('feedsub', 'fallback_hub')) {
               $hub = common_config('feedsub', 'fallback_hub');
               if (common_config('feedsub', 'hub_user')) {
                  $u = common_config('feedsub', 'hub_user');
                  $p = common_config('feedsub', 'hub_pass');
                  $client->setAuth($u, $p);
               }
            } else {
               throw new FeedSubException('Server could not find a usable PuSH hub.');
            }
         }
         $response = $client->post($hub, $headers, $post);
         $status = $response->getStatus();
         // PuSH specificed response status code
         if ($status == 202  || $status == 204) {
            common_log(LOG_INFO, __METHOD__ . ': sub req ok, awaiting verification callback');
            return;
         } else if ($status >= 200 && $status < 300) {
            common_log(LOG_ERR, __METHOD__ . ": sub req returned unexpected HTTP $status: " . $response->getBody());
            $msg = sprintf(_m("Unexpected HTTP status: %d"), $status);
         } else if ($status == 422) {
            // Error code regarding something wrong in the data (it seems
            // that we're talking to a PuSH hub at least, so let's check
            // our own data to be sure we're not mistaken somehow.
            $this->ensureHub(true);
         } else {
            common_log(LOG_ERR, __METHOD__ . ": sub req failed with HTTP $status: " . $response->getBody());
         }
      } catch (Exception $e) {
         common_log(LOG_ERR, __METHOD__ . ": error \"{$e->getMessage()}\" hitting hub {$this->huburi} subscribing to {$this->getUri()}");

         // Reset the subscription state.
         $orig = clone($this);
         $this->sub_state = 'inactive';
         $this->update($orig);

         // Throw the Exception again.
         throw $e;
      }
      throw new ServerException("{$mode} request failed" . (!is_null($msg) ? " ($msg)" : '.'));
   }


   // -------------------------------------------------------------------------
   // Function: confirmSubscribe
   // Save PuSH subscription confirmation.
   // Sets approximate lease start and end times and finalizes state.
   //
   // Parameters:
   // o int $lease_seconds - provided hub.lease_seconds parameter, if given
    public function confirmSubscribe($lease_seconds)
    {
        $original = clone($this);

        $this->sub_state = 'active';
        $this->sub_start = common_sql_date(time());
        if ($lease_seconds > 0) {
            $this->sub_end = common_sql_date(time() + $lease_seconds);
        } else {
            $this->sub_end = null;  // Backwards compatibility to StatusNet (PuSH <0.4 supported permanent subs)
        }
        $this->modified = common_sql_now();

        return $this->update($original);
    }

   // -------------------------------------------------------------------------
   // Function: confirmUnsubscribe
   // Save PuSH unsubscription confirmation.  Wipes active PuSH sub info and 
   // resets state.
   //
   // Returns:
   // o FeedSub Updated
   public function confirmUnsubscribe() {
      $original = clone($this);
      // @fixme these should all be null, but DB_DataObject doesn't save null values...?????
      $this->secret = '';
      $this->sub_state = '';
      $this->sub_start = '';
      $this->sub_end = '';
      $this->modified = common_sql_now();

      return $this->update($original);
   }


   // ------------------------------------------------------------------------
   // Function: receive
   // Accept updates from a PuSH feed. If validated, this object and the
   // feed (as a DOMDocument) will be passed to the StartFeedSubHandleFeed
   // and EndFeedSubHandleFeed events for processing.
   //
   // Not guaranteed to be running in an immediate POST context; may be run
   // from a queue handler.
   //
   // Parameters:
   // o string $post source of Atom or RSS feed
   // o string $hmac X-Hub-Signature header, if present
   //
   // Side effects:
   // o the feedsub record's lastupdate field will be updated to the current time
   //   (not published time) if we got a legit update.
   //
   // Returns:
   // o void
   public function receive($post, $hmac) {
        common_log(LOG_INFO, __METHOD__ . ": packet for \"" . $this->getUri() . "\"! $hmac $post");

        if (!in_array($this->sub_state, array('active', 'nohub'))) {
            common_log(LOG_ERR, __METHOD__ . ": ignoring PuSH for inactive feed " . $this->getUri() . " (in state '$this->sub_state')");
            return;
        }

        if ($post === '') {
            common_log(LOG_ERR, __METHOD__ . ": ignoring empty post");
            return;
        }

        if (!$this->validatePushSig($post, $hmac)) {
            // Per spec we silently drop input with a bad sig,
            // while reporting receipt to the server.
            return;
        }

        $feed = new DOMDocument();
        if (!$feed->loadXML($post)) {
            // @fixme might help to include the err message
            common_log(LOG_ERR, __METHOD__ . ": ignoring invalid XML");
            return;
        }

        $orig = clone($this);
        $this->last_update = common_sql_now();
        $this->update($orig);
        Event::handle('StartFeedSubReceive', array($this, $feed));        
        Event::handle('EndFeedSubReceive', array($this, $feed));
   }


   // ------------------------------------------------------------------------
   // Function: validatePushSig
   // Validate the given Atom chunk and HMAC signature against our
   // shared secret that was set up at subscription time.
   //
   // If we don't have a shared secret, there should be no signature.
   // If we do, our calculated HMAC should match theirs.
   //
   // Parameters:
   // o string $post raw XML source as POSTed to us
   // o string $hmac X-Hub-Signature HTTP header value, or empty
   //
   // Returns:
   // o boolean true for a match
   protected function validatePushSig($post, $hmac) {
      if ($this->secret) {
         // {3,16} because shortest hash algorithm name is 3 characters (md2,md4,md5) and longest
         // is currently 11 characters, but we'll leave some margin in the end...
         if (preg_match('/^([0-9a-zA-Z\-\,]{3,16})=([0-9a-fA-F]+)$/', $hmac, $matches)) {
            $hash_algo  = strtolower($matches[1]);
            $their_hmac = strtolower($matches[2]);
            common_debug(sprintf(__METHOD__ . ': PuSH from feed %s uses HMAC algorithm %s with value: %s', _ve($this->getUri()), _ve($hash_algo), _ve($their_hmac)));
            if (!in_array($hash_algo, hash_algos())) {
               // We can't handle this at all, PHP doesn't recognize the algorithm name ('md5', 'sha1', 'sha256' etc: https://secure.php.net/manual/en/function.hash-algos.php)
               common_log(LOG_ERR, sprintf(__METHOD__.': HMAC algorithm %s unsupported, not found in PHP hash_algos()', _ve($hash_algo)));
               return false;
            } elseif (!is_null(common_config('security', 'hash_algos')) && !in_array($hash_algo, common_config('security', 'hash_algos'))) {
               // We _won't_ handle this because there is a list of accepted hash algorithms and this one is not in it.
               common_log(LOG_ERR, sprintf(__METHOD__.': Whitelist for HMAC algorithms exist, but %s is not included.', _ve($hash_algo)));
               return false;
            }
            $our_hmac = hash_hmac($hash_algo, $post, $this->secret);
            if ($their_hmac !== $our_hmac) {
               common_log(LOG_ERR, sprintf(__METHOD__.': ignoring PuSH with bad HMAC hash: got %s, expected %s for feed %s from hub %s', _ve($their_hmac), _ve($our_hmac), _ve($this->getUri()), _ve($this->huburi)));
               throw new FeedSubBadPushSignatureException('Incoming PuSH signature did not match expected HMAC hash.');
            }
            return true;
         } else {
            common_log(LOG_ERR, sprintf(__METHOD__.': ignoring PuSH with bogus HMAC==', _ve($hmac)));
         }
      } else {
         if (empty($hmac)) {
            return true;
         } else {
            common_log(LOG_ERR, sprintf(__METHOD__.': ignoring PuSH with unexpected HMAC==%s', _ve($hmac)));
         }
      }
      return false;
   }


   // -------------------------------------------------------------------------
   // Function: delete
   // Deletes a FeedSub from the database
   //
   // Returns:
   // o boolean Success
   public function delete($useWhere=false) {
      try {
         $oprofile = Ostatus_profile::getKV('feeduri', $this->getUri());
         if ($oprofile instanceof Ostatus_profile) {
            // Check if there's a profile. If not, handle the NoProfileException below
            $profile = $oprofile->localProfile();
         }
      } catch (NoProfileException $e) {
         // If the Ostatus_profile has no local Profile bound to it, let's clean it out at the same time
         $oprofile->delete();
      } catch (NoUriException $e) {
         // FeedSub->getUri() can throw a NoUriException, let's just go ahead and delete it
      }
      return parent::delete($useWhere);
   }
}

// END OF FILE
// ============================================================================
?>