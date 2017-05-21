<?php
/* ============================================================================
 * Title: Subscription
 * Table definition for subscription
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
 * Table definition for subscription
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Brion Vibber <brion@pobox.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Zach Copley
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
// Class: Subscription
// Superclass containing the table definition for a subscription entry in the
// database and related interfaces.
//
// Properties:
// o $__table = 'subscription' -  table name
// o $subscriber - int(4)  primary_key not_null
// o $subscribed - int(4)  primary_key not_null
// o $jabber     - tinyint(1)   default_1
// o $sms        - tinyint(1)   default_1
// o $token      - varchar(191)   not 255 because utf8mb4 takes more space
// o $secret     - varchar(191)   not 255 because utf8mb4 takes more space
// o $uri        - varchar(191)   not 255 because utf8mb4 takes more space
// o $created    - datetime()   not_null
// o $modified   - timestamp()   not_null default_CURRENT_TIMESTAMP
class Subscription extends Managed_DataObject {
   const CACHE_WINDOW = 201;
   const FORCE = true;

   public $__table = 'subscription';                    // table name
   public $subscriber;                      // int(4)  primary_key not_null
   public $subscribed;                      // int(4)  primary_key not_null
   public $jabber;                          // tinyint(1)   default_1
   public $sms;                             // tinyint(1)   default_1
   public $token;                           // varchar(191)   not 255 because utf8mb4 takes more space
   public $secret;                          // varchar(191)   not 255 because utf8mb4 takes more space
   public $uri;                             // varchar(191)   not 255 because utf8mb4 takes more space
   public $created;                         // datetime()   not_null
   public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema for the subscription entry.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'subscriber' => array('type' => 'int', 'not null' => true, 'description' => 'profile listening'),
            'subscribed' => array('type' => 'int', 'not null' => true, 'description' => 'profile being listened to'),
            'jabber' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'deliver jabber messages'),
            'sms' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'deliver sms messages'),
            'token' => array('type' => 'varchar', 'length' => 191, 'description' => 'authorization token'),
            'secret' => array('type' => 'varchar', 'length' => 191, 'description' => 'token secret'),
            'uri' => array('type' => 'varchar', 'length' => 191, 'description' => 'universally unique identifier'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('subscriber', 'subscribed'),
         'unique keys' => array(
            'subscription_uri_key' => array('uri'),),
         'indexes' => array(
            'subscription_subscriber_idx' => array('subscriber', 'created'),
            'subscription_subscribed_idx' => array('subscribed', 'created'),
            'subscription_token_idx' => array('token'),),);
   }


   // -------------------------------------------------------------------------
   // Function: start
   // Make a new subscription, save the database entry, and fire the
   // neccesary events.
   //
   // Parameters:
   // o Profile $subscriber party to receive new notices
   // o Profile $other      party sending notices; publisher
   // o bool    $force      pass Subscription::FORCE to override local subscription approval
   //
   // Returns:
   // o mixed Subscription or Subscription_queue: new subscription info
   static function start(Profile $subscriber, Profile $other, $force=false) {
      // AuthorizationException if the user doesn't have Subscribe right
      if (!$subscriber->hasRight(Right::SUBSCRIBE)) {
         // TRANS: Exception thrown when trying to subscribe while being banned from subscribing.
         throw new AuthorizationException(_('You have been banned from subscribing.'));
         return false;
      }
      // AlreadyFulfilledException if they're already subscribed.
      if (self::exists($subscriber, $other)) {
         // TRANS: Exception thrown when trying to subscribe while already subscribed.
         throw new AlreadyFulfilledException(_('Already subscribed!'));
         return false;
      }
      // Fail if user has blocked the subscriber
      if ($other->hasBlocked($subscriber)) {
         // TRANS: Exception thrown when trying to subscribe to a user who has blocked the subscribing user.
         throw new PrivateStreamException(_('You are unable to subscribe to this user.'));
         return false;
      }
      // Fail if the subscriber matches the filter list
      if (Subscription::matchesFilterList($subscriber)) {
         // TRANS: Exception thrown when trying to subscribe to a user who has blocked the subscribing user.
         throw new AuthorizationException(_('You have been blocked from subscribing on this server.'));
         return false;
      }

      if (Event::handle('StartSubscribe', array($subscriber, $other))) {
         // unless subscription is forced, the user policy for subscription approvals is tested
         if (!$force && $other->requiresSubscriptionApproval($subscriber)) {
            try {
               $sub = Subscription_queue::saveNew($subscriber, $other);
               $sub->notify();
            } catch (AlreadyFulfilledException $e) {
               $sub = Subscription_queue::getSubQueue($subscriber, $other);
            }
         } else {
            $otherUser = User::getKV('id', $other->id);
            $sub = self::saveNew($subscriber, $other);
            $sub->notify();
            self::blow('user:notices_with_friends:%d', $subscriber->id);
            self::blow('subscription:by-subscriber:'.$subscriber->id);
            self::blow('subscription:by-subscribed:'.$other->id);
            $subscriber->blowSubscriptionCount();
            $other->blowSubscriberCount();
            if ($otherUser instanceof User &&
               $otherUser->autosubscribe &&
               !self::exists($other, $subscriber) &&
               !$subscriber->hasBlocked($other)) {
               try {
                  self::start($other, $subscriber);
               } catch (AlreadyFulfilledException $e) {
                  // This shouldn't happen due to !self::exists above
                  common_debug('Tried to autosubscribe a user to its new subscriber.');
               } catch (Exception $e) {
                  common_log(LOG_ERR, "Exception during autosubscribe of {$other->nickname} to profile {$subscriber->id}: {$e->getMessage()}");
               }
            }
         }
         if ($sub instanceof Subscription) { // i.e. not Subscription_queue
            Event::handle('EndSubscribe', array($subscriber, $other));
         }
      }
      return $sub;
   }


   // -------------------------------------------------------------------------
   // Function: matchesFilterList
   // Loads the site subscription filter setting and returns whether $subscriber
   // matches it or not.
   function matchesFilterList($subscriber) {
      // Get subscription filter list
      $configphpsettings = common_config('site','sanctions') ?: array();
      foreach($configphpsettings as $configphpsetting=>$value) {
         $settings[$configphpsetting] = $value;
      }
      $filters = $settings['subscription_filter'];
      common_debug("Subscription filter list currently: " . json_encode($filters));
      
      // Return true if matches, false if not
      if (preg_match($filters, $subscriber)) {
         return true;
      }
      return false;
   }

   // -------------------------------------------------------------------------
   // Function: ensureStart
   // Helper function to call the neccesary low-level functions to start a
   // subscription from $subscriber to the givern $other profile.
   static function ensureStart(Profile $subscriber, Profile $other, $force=false) {
      try {
         $sub = self::start($subscriber, $other, $force);
      } catch (AlreadyFulfilledException $e) {
         return self::getSubscription($subscriber, $other);
      }
      return $sub;
   }


   // -------------------------------------------------------------------------
   // Function: saveNew
   // Low-level subscription save.
   // Outside callers should use Subscription::start()
   protected static function saveNew(Profile $subscriber, Profile $other) {
      $sub = new Subscription();
      $sub->subscriber = $subscriber->getID();
      $sub->subscribed = $other->getID();
      $sub->jabber     = 1;
      $sub->sms        = 1;
      $sub->created    = common_sql_now();
      $sub->uri        = self::newUri($subscriber, $other, $sub->created);
      $result = $sub->insert();
      if ($result===false) {
         common_log_db_error($sub, 'INSERT', __FILE__);
         // TRANS: Exception thrown when a subscription could not be stored on the server.
         throw new Exception(_('Could not save subscription.'));
      }
      return $sub;
   }


   // -------------------------------------------------------------------------
   // Function: notify
   // Norify a user of a subscription.  Essentially a notifyEmail alias currently,
   // but we could also add other notifications such as Jabber, SMS, etc here.
   function notify() {
      $this->notifyEmail();
   }


   // -------------------------------------------------------------------------
   // Function: notifyEmail
   // Notify a user of a subscription by email
   function notifyEmail() {
      $subscribedUser = User::getKV('id', $this->subscribed);
      if ($subscribedUser instanceof User) {
         $subscriber = Profile::getKV('id', $this->subscriber);
         mail_subscribe_notify_profile($subscribedUser, $subscriber);
      }
   }


   // -------------------------------------------------------------------------
   // Function: cancel
   // Cancel a subscription, delete the entry, and fire related events.
   static function cancel(Profile $subscriber, Profile $other) {
      if (!self::exists($subscriber, $other)) {
         // TRANS: Exception thrown when trying to unsibscribe without a subscription.
         throw new AlreadyFulfilledException(_('Not subscribed!'));
      }

      // Don't allow deleting self subs
      if ($subscriber->id == $other->id) {
         // TRANS: Exception thrown when trying to unsubscribe a user from themselves.
         throw new Exception(_('Could not delete self-subscription.'));
      }

      if (Event::handle('StartUnsubscribe', array($subscriber, $other))) {
         $sub = Subscription::pkeyGet(array('subscriber' => $subscriber->id,
                                            'subscribed' => $other->id));

         // note we checked for existence above
         assert(!empty($sub));
         $result = $sub->delete();
         if (!$result) {
            common_log_db_error($sub, 'DELETE', __FILE__);
            // TRANS: Exception thrown when a subscription could not be deleted on the server.
            throw new Exception(_('Could not delete subscription.'));
         }
         self::blow('user:notices_with_friends:%d', $subscriber->id);
         self::blow('subscription:by-subscriber:'.$subscriber->id);
         self::blow('subscription:by-subscribed:'.$other->id);
         $subscriber->blowSubscriptionCount();
         $other->blowSubscriberCount();
         Event::handle('EndUnsubscribe', array($subscriber, $other));
      }
      return;
   }


   // -------------------------------------------------------------------------
   // Function: exists
   // Returns true/false whether a subscription between a user and another
   // profile.
   static function exists(Profile $subscriber, Profile $other) {
      try {
         $sub = self::getSubscription($subscriber, $other);
      } catch (NoResultException $e) {
         return false;
      }
      return true;
   }


   // ------------------------------------------------------------------------
   // Function: getSubscription
   // Returns the subscription entry for a given subscriber and the profile
   // they subscribe to, if one exists.
   //
   // This is essentially a pkeyGet but we have an object to return in
   // NoResultException.
   static function getSubscription(Profile $subscriber, Profile $other) {
      $sub = new Subscription();
      $sub->subscriber = $subscriber->id;
      $sub->subscribed = $other->id;
      if (!$sub->find(true)) {
         throw new NoResultException($sub);
      }
      return $sub;
   }


   // -------------------------------------------------------------------------
   // Function: getSubscriber
   // Returns the user doing the subscribing in this subscription table entry.
   public function getSubscriber() {
      return Profile::getByID($this->subscriber);
   }


   // -------------------------------------------------------------------------
   // Function: getSubscribed
   // Returns the user subscribed to in this subscription table entry.
   public function getSubscribed() {
      return Profile::getByID($this->subscribed);
   }


   // -------------------------------------------------------------------------
   // Function: asActivity
   // Returns an OStatus (0.5) ActivityVerb representation of a subscription.
   function asActivity() {
      $subscriber = $this->getSubscriber();
      $subscribed = $this->getSubscribed();
      $act = new Activity();
      $act->verb = ActivityVerb::FOLLOW;
      $act->id   = $this->getUri();
      $act->time    = strtotime($this->created);
      // TRANS: Activity title when subscribing to another person.
      $act->title = _m('TITLE','Follow');
      // TRANS: Notification given when one person starts following another.
      // TRANS: %1$s is the subscriber, %2$s is the subscribed.
      $act->content = sprintf(_('%1$s is now following %2$s.'),
                             $subscriber->getBestName(),
                             $subscribed->getBestName());
      $act->actor     = $subscriber->asActivityObject();
      $act->objects[] = $subscribed->asActivityObject();
      $url = common_local_url('AtomPubShowSubscription',
                              array('subscriber' => $subscriber->id,
                                    'subscribed' => $subscribed->id));
      $act->selfLink = $url;
      $act->editLink = $url;
      return $act;
   }


   // -------------------------------------------------------------------------
   // Function: bySubscriber
   // Stream of subscriptions with the same subscriber
   //
   // Useful for showing pages that list subscriptions in reverse
   // chronological order. Has offset & limit to make paging
   // easy.
   //
   // Parameters:
   // o integer $profile_id - ID of the subscriber profile
   // o integer $offset     - Offset from latest
   // o $limit              - Maximum number to fetch
   //
   // Returns:
   // o Subscription stream of subscriptions; use fetch() to iterate
   public static function bySubscriber($profile_id, $offset = 0, $limit = PROFILES_PER_PAGE) {
      // "by subscriber" means it is the list of subscribed users we want
      $ids = self::getSubscribedIDs($profile_id, $offset, $limit);
      return Subscription::listFind('subscribed', $ids);
   }


   // -------------------------------------------------------------------------
   // Function: bySubscribed
   // Returns a stream of subscriptions with the same subscriber
   //
   // Useful for showing pages that list subscriptions in reverse
   // chronological order. Has offset & limit to make paging
   // easy.
   //
   // o integer $profile_id - ID of the subscribed profile
   // o integer $offset     - Offset from latest
   // o integer $limit      - Maximum number to fetch
   //
   // Returns:
   // o Subscription stream of subscriptions; use fetch() to iterate
   public static function bySubscribed($profile_id, $offset = 0, $limit = PROFILES_PER_PAGE) {
      // "by subscribed" means it is the list of subscribers we want
      $ids = self::getSubscriberIDs($profile_id, $offset, $limit);
      return Subscription::listFind('subscriber', $ids);
   }


   // -------------------------------------------------------------------------
   // Function: getSubscribedIDs
   // Returns an array of all the people who the given profile has subscribed to
   public static function getSubscribedIDs($profile_id, $offset, $limit) {
      return self::getSubscriptionIDs('subscribed', $profile_id, $offset, $limit);
   }


   // ------------------------------------------------------------------------
   // Function: getSubcriberIDs
   // Returns an array of all the people who subscribe to the given profile
   public static function getSubscriberIDs($profile_id, $offset, $limit) {
      return self::getSubscriptionIDs('subscriber', $profile_id, $offset, $limit);
   }


   // -------------------------------------------------------------------------
   // Function: getSubscriptionIDs
   // Returns an array of subscription entries associated with the given 
   // profile_id of the type specified in get_type.
   //
   // Parameters:
   // o get_type   - one of "subscriber", or "subscribed"
   // o profile_id - profile id of user we're looking up subs for
   // o offset     - start at subscription entry X
   // o limit      - number of records to pull
   private static function getSubscriptionIDs($get_type, $profile_id, $offset, $limit) {
      switch ($get_type) {
      case 'subscribed':
            $by_type  = 'subscriber';
            break;
      case 'subscriber':
            $by_type  = 'subscribed';
            break;
      default:
            throw new Exception('Bad type argument to getSubscriptionIDs');
      }

      $cacheKey = 'subscription:by-'.$by_type.':'.$profile_id;
      $queryoffset = $offset;
      $querylimit = $limit;

      if ($offset + $limit <= self::CACHE_WINDOW) {
         // Oh, it seems it should be cached
         $ids = self::cacheGet($cacheKey);
         if (is_array($ids)) {
            return array_slice($ids, $offset, $limit);
         }
         // Being here indicates we didn't find anything cached
         // so we'll have to fill it up simultaneously
         $queryoffset = 0;
         $querylimit  = self::CACHE_WINDOW;
      }

      $sub = new Subscription();
      $sub->$by_type = $profile_id;
      $sub->selectAdd($get_type);
      $sub->whereAdd("{$get_type} != {$profile_id}");
      $sub->orderBy('created DESC');
      $sub->limit($queryoffset, $querylimit);
      if (!$sub->find()) {
         return array();
      }
      
      $ids = $sub->fetchAll($get_type);
      // If we're simultaneously filling up cache, remember to slice
      if ($queryoffset === 0 && $querylimit === self::CACHE_WINDOW) {
         self::cacheSet($cacheKey, $ids);
         return array_slice($ids, $offset, $limit);
      }
      return $ids;
   }


   // -------------------------------------------------------------------------
   // Function: update
   // Updates the entry for a subscription.
   //
   // Because we cache subscriptions, it's useful to flush them
   // here.
   //
   // Parameters:
   // o mixed $dataObject Original version of object
   //
   // Returns:
   // o boolean success flag.
   function update($dataObject=false) {
      self::blow('subscription:by-subscriber:'.$this->subscriber);
      self::blow('subscription:by-subscribed:'.$this->subscribed);
      return parent::update($dataObject);
   }


   // -------------------------------------------------------------------------
   // Function: getUri
   // Returns the URI of a subscription
   public function getUri() {
      return $this->uri ?: self::newUri($this->getSubscriber(), $this->getSubscribed(), $this->created);
   }
}

// END OF LIFE
// ============================================================================
?>