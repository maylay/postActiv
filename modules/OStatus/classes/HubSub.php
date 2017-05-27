<?php
/* ============================================================================
 * Title: HubSub
 * Class representation of a WebSub hub subscription
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
 * HubSub handles low-level WebSub (formerly PubHubSubbub aka PuSH) 
 * subscriptions.  Higher-level behavior building OStatus stuff on top is 
 * handled under Ostatus_profile.
 *
 * WebSub (formerly PuSH) subscription flow:
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
 * o James Walker <walkah@walkah.net>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chimo <chimo@chromic.org>
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
// Class: HubSub
// WebSub feed subscription record
//
// Variables:
// o hashkey   - sha1(topic . '|' . $callback); (topic, callback) key is too long for myisam in utf8
// o topic     - varchar(191)   not 255 because utf8mb4 takes more space
// o callback  - varchar(191)   not 255 because utf8mb4 takes more space
// o secret    - text
// o sub_start - datetime
// o sub_end   - datetime
// o created   - datetime
// o modified  - timestamp
class HubSub extends Managed_DataObject {
   public $__table = 'hubsub';
   public $hashkey; // sha1(topic . '|' . $callback); (topic, callback) key is too long for myisam in utf8
   public $topic;      // varchar(191)   not 255 because utf8mb4 takes more space
   public $callback;   // varchar(191)   not 255 because utf8mb4 takes more space
   public $secret;
   public $sub_start;
   public $sub_end;
   public $created;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array describing the table schema in the database
   public static function schemaDef() {
      return array(
         'fields' => array(
            'hashkey' => array('type' => 'char', 'not null' => true, 'length' => 40, 'description' => 'HubSub hashkey'),
            'topic' => array('type' => 'varchar', 'not null' => true, 'length' => 191, 'description' => 'HubSub topic'),
            'callback' => array('type' => 'varchar', 'not null' => true, 'length' => 191, 'description' => 'HubSub callback'),
            'secret' => array('type' => 'text', 'description' => 'HubSub stored secret'),
            'sub_start' => array('type' => 'datetime', 'description' => 'subscription start'),
            'sub_end' => array('type' => 'datetime', 'description' => 'subscription end'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('hashkey'),
         'indexes' => array(
            'hubsub_callback_idx' => array('callback'),
            'hubsub_topic_idx' => array('topic'),),);
   }


   // -------------------------------------------------------------------------
   // Function: hashkey
   // Given a topic and callback, compute the sha1 hash for a HubSub
   //
   // Parameters:
   // o topic    - topic of the hubsub
   // o callback - callback URL of the hubsub
   //
   // Returns:
   // o sha1 Hash
   static function hashkey($topic, $callback) {
      return sha1($topic . '|' . $callback);
   }


   // -------------------------------------------------------------------------
   // Function: getByHashkey
   // Given the topic and callback of a HubSub, find it in the database
   //
   // Parameters:
   // o topic    - topic of the hubsub
   // o callback - callback URL of the hubsub
   //
   // Returns:
   // o found hubsub
   public static function getByHashkey($topic, $callback) {
      return self::getKV('hashkey', self::hashkey($topic, $callback));
   }


   // -------------------------------------------------------------------------
   // Function: setLease
   // Validates a requested lease length, sets length plus
   // subscription start & end dates.
   //
   // Does not save to database -- use before insert() or update().
    //
   // Parameters:
   // o int $length - length of lease in seconds
   function setLease($length) {
      common_debug('WebSub hub got requested lease_seconds=='._ve($length));
      assert(is_int($length));
      $min = 86400;   // 3600*24 (one day)
      $max = 86400 * 30;

      if ($length == 0) {
         // We want to garbage collect dead subscriptions!
         $length = $max;
      } elseif( $length < $min) {
         $length = $min;
      } else if ($length > $max) {
         $length = $max;
      }

      common_debug('WebSub hub after sanitation: lease_seconds=='._ve($length));
      $this->sub_start = common_sql_now();
      $this->sub_end = common_sql_date(time() + $length);
   }


   // -------------------------------------------------------------------------
   // Function: getLeaseTime
   // Returns the remaining time on a hubsub lease
   //
   // Returns:
   // o int $length
   function getLeaseTime() {
      if (empty($this->sub_start) || empty($this->sub_end)) {
         return null;
      }
      $length = strtotime($this->sub_end) - strtotime($this->sub_start);
      assert($length > 0);
      return $length;
   }


   // -------------------------------------------------------------------------
   // Function: getLeaseRemaining
   // Essentially a call to getLeaseTime but with some extra checking
   //
   // Returns:
   // o null if the lease is expired
   // o int $length otherwise
   function getLeaseRemaining() {
      if (empty($this->sub_end)) {
         return null;
      }
      return strtotime($this->sub_end) - time();
   }


   // -------------------------------------------------------------------------
   // Function: scheduleVerify
   // Schedule a future verification ping to the subscriber.
   // If queues are disabled, will be immediate.
   //
   // Parameters:
   // o string $mode  - 'subscribe' or 'unsubscribe'
   // o string $token - hub.verify_token value, if provided by client
   function scheduleVerify($mode, $token=null, $retries=null) {
      if ($retries === null) {
         $retries = intval(common_config('ostatus', 'hub_retries'));
      }
      $data = array('sub' => clone($this),
                    'mode' => $mode,
                    'token' => $token,    // let's put it in there if remote uses PuSH <0.4
                    'retries' => $retries);
      $qm = QueueManager::get();
      $qm->enqueue($data, 'hubconf');
   }


   // -------------------------------------------------------------------------
   // Function: getTopic
   // Returns the HubSub's topic
   //
   // Returns:
   // o $topic
   public function getTopic() {
      return $this->topic;
   }

   // -------------------------------------------------------------------------
   // Function: verify
   // Send a verification ping to subscriber, and if confirmed apply the changes.
   // This may create, update, or delete the database record.
   //
   // Parameters:
   // o string $mode  - 'subscribe' or 'unsubscribe'
   // o string $token - hub.verify_token value, if provided by client
   //
   // Error States:
   // o throws ClientException on failure
   function verify($mode, $token=null) {
      assert($mode == 'subscribe' || $mode == 'unsubscribe');
      $challenge = common_random_hexstr(32);
      $params = array('hub.mode' => $mode,
                      'hub.topic' => $this->getTopic(),
                      'hub.challenge' => $challenge);
      if ($mode == 'subscribe') {
         $params['hub.lease_seconds'] = $this->getLeaseTime();
      }
      if ($token !== null) {  // TODO: deprecated in PuSH 0.4
         $params['hub.verify_token'] = $token;   // let's put it in there if remote uses PuSH <0.4
      }
      
      // Any existing query string parameters must be preserved
      $url = $this->callback;
      if (strpos($url, '?') !== false) {
         $url .= '&';
      } else {
         $url .= '?';
      }
      $url .= http_build_query($params, '', '&');

      $request = new HTTPClient();
      $response = $request->get($url);
      $status = $response->getStatus();

      if ($status >= 200 && $status < 300) {
         common_log(LOG_INFO, "Verified {$mode} of {$this->callback}:{$this->getTopic()}");
      } else {
         // TRANS: Client exception. %s is a HTTP status code.
         throw new ClientException(sprintf(_m('Hub subscriber verification returned HTTP %s.'),$status));
      }

      $old = HubSub::getByHashkey($this->getTopic(), $this->callback);
      if ($mode == 'subscribe') {
         if ($old instanceof HubSub) {
            $this->update($old);
         } else {
            $ok = $this->insert();
         }
      } else if ($mode == 'unsubscribe') {
         if ($old instanceof HubSub) {
            $old->delete();
         } else {
            // That's ok, we're already unsubscribed.
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: onInsert
   // Sets the hashkey automagically on insert
   protected function onInsert() {
      $this->setHashkey();
      $this->created = common_sql_now();
      $this->modified = common_sql_now();
   }


   // -------------------------------------------------------------------------
   // Function: onUpdateKeys
   // Update the hashkey automagically if needed
   protected function onUpdateKeys(Managed_DataObject $orig) {
      if ($this->topic !== $orig->topic || $this->callback !== $orig->callback) {
          $this->setHashkey();
      }
   }


   // -------------------------------------------------------------------------
   // Function: setHashkey
   // Helper function to actually set the hashkey
   protected function setHashkey() {
      $this->hashkey = self::hashkey($this->topic, $this->callback);
   }

   
   // -------------------------------------------------------------------------
   // Function: distribute
   // Schedule delivery of a 'fat ping' to the subscriber's callback
   // endpoint. If queues are disabled, this will run immediately.
   //
   // Parameters:
   // o string $atom - well-formed Atom feed
   // o int $retries - optional count of retries if POST fails; defaults to hub_retries from config or 0 if unset
   function distribute($atom, $retries=null) {
      if ($retries === null) {
         $retries = intval(common_config('ostatus', 'hub_retries'));
      }

      // We dare not clone() as when the clone is discarded it'll
      // destroy the result data for the parent query.
      // @fixme use clone() again when it's safe to copy an
      // individual item from a multi-item query again.
      $sub = HubSub::getByHashkey($this->getTopic(), $this->callback);
      $data = array('sub' => $sub,
                    'atom' => $atom,
                    'retries' => $retries);
      common_log(LOG_INFO, "Queuing WebSub: {$this->getTopic()} to {$this->callback}");
      $qm = QueueManager::get();
      $qm->enqueue($data, 'hubout');
   }


   // -------------------------------------------------------------------------
   // Function: bulkDistribute
   // Queue up a large batch of pushes to multiple subscribers
   // for this same topic update.
   //
   // If queues are disabled, this will run immediately.
   //
   // Parameters:
   // o string $atom well-formed Atom feed
   // o array $pushCallbacks list of callback URLs
   //
   // Returns:
   // o boolean True for success, False for failure
   function bulkDistribute($atom, array $pushCallbacks) {
      if (empty($pushCallbacks)) {
         common_log(LOG_ERR, 'Callback list empty for bulkDistribute.');
         return false;
      }
      $data = array('atom' => $atom,
                    'topic' => $this->getTopic(),
                    'pushCallbacks' => $pushCallbacks);
      common_log(LOG_INFO, "Queuing WebSub batch: {$this->getTopic()} to ".count($pushCallbacks)." sites");
      $qm = QueueManager::get();
      $qm->enqueue($data, 'hubprep');
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: push
   // Send a 'fat ping' to the subscriber's callback endpoint containing the 
   // given Atom feed chunk.
   //
   // Determination of which items to send should be done at a higher level;
   // don't just shove in a complete feed!
   //
   // Parameters:
   // o string $atom - well-formed Atom feed
   //
   // Error States:
   // o throws Exception (HTTP or general)
   //
   // XXX:
   // DO NOT trust a Location header here, _especially_ from 'http' protocols,
   // but not 'https' either at least if we don't do proper CA verification. Trust that
   // the most common change here is simply switching 'http' to 'https' and we will
   // solve 99% of all of these issues for now. There should be a proper mechanism
   // if we want to change the callback URLs, preferrably just manual resubscriptions
   // from the remote side, combined with implemented PuSH subscription timeouts.
   //
   // FIXME: 
   // Add 'failed' incremental count for this callback.   
   function push($atom) {
      $headers = array('Content-Type: application/atom+xml');
      if ($this->secret) {
         $hmac = hash_hmac('sha1', $atom, $this->secret);
         $headers[] = "X-Hub-Signature: sha1=$hmac";
      } else {
         $hmac = '(none)';
      }
      common_log(LOG_INFO, "About to push feed to $this->callback for {$this->getTopic()}, HMAC $hmac");

      $request = new HTTPClient();
      $request->setConfig(array('follow_redirects' => false));
      $request->setBody($atom);
      try {
         $response = $request->post($this->callback, $headers);
         if ($response->isOk()) {
            return true;
         }
      } catch (Exception $e) {
          $response = null;
          common_debug('WebSub callback to '._ve($this->callback).' for '._ve($this->getTopic()).' failed with exception: '._ve($e->getMessage()));
      }

      // We failed the PuSH, but it might be that the remote site has changed their configuration to HTTPS
      if ('http' === parse_url($this->callback, PHP_URL_SCHEME)) {
         // Test if the feed callback for this node has migrated to HTTPS
         $httpscallback = preg_replace('/^http/', 'https', $this->callback, 1);
         $alreadyreplaced = self::getByHashKey($this->getTopic(), $httpscallback);
         if ($alreadyreplaced instanceof HubSub) {
            $this->delete();
            throw new AlreadyFulfilledException('The remote side has already established an HTTPS callback, deleting the legacy HTTP entry.');
         }

         common_debug('WebSub callback to '._ve($this->callback).' for '._ve($this->getTopic()).' trying HTTPS callback: '._ve($httpscallback));
         $response = $request->post($httpscallback, $headers);

         if ($response->isOk()) {
            $orig = clone($this);
            $this->callback = $httpscallback;
            // NOTE: hashkey will be set in $this->onUpdateKeys($orig) through updateWithKeys
            $this->updateWithKeys($orig);
            return true;
         }
      }

      if (is_null($response)) {
         // This means we got a lower-than-HTTP level error, like domain not found or maybe connection refused
         // This should be using a more distinguishable exception class, but for now this will do.
         throw new Exception(sprintf(_m('HTTP request failed without response to URL: %s'), _ve(isset($httpscallback) ? $httpscallback : $this->callback)));
      }

      // TRANS: Exception. %1$s is a response status code, %2$s is the body of the response.
      throw new Exception(sprintf(_m('Callback returned status: %1$s. Body: %2$s'),
                            $response->getStatus(),trim($response->getBody())));
   }
}

// END OF FILE
// ============================================================================
?>