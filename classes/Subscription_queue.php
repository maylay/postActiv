<?php
/* ============================================================================
 * Title: Subscription_queue
 * Table Definition for subscription_queue
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
 * Table Definition for subscription_queue
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
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
// Class: Subscription_queue
// Superclass to hold queued subscriptions in the database for users and groups
// that require prior approval for subscriptions.
//
// Properties:
// o $__table = 'subscription_queue' - table name
// o subscriber
// o subscribed
// o created
class Subscription_queue extends Managed_DataObject {
   public $__table = 'subscription_queue';       // table name
   public $subscriber;
   public $subscribed;
   public $created;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema in the database.
   public static function schemaDef() {
      return array(
         'description' => 'Holder for subscription requests awaiting moderation.',
         'fields' => array(
            'subscriber' => array('type' => 'int', 'not null' => true, 'description' => 'remote or local profile making the request'),
            'subscribed' => array('type' => 'int', 'not null' => true, 'description' => 'remote or local profile being subscribed to'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),),
         'primary key' => array('subscriber', 'subscribed'),
         'indexes' => array(
            'subscription_queue_subscriber_created_idx' => array('subscriber', 'created'),
            'subscription_queue_subscribed_created_idx' => array('subscribed', 'created'),),
         'foreign keys' => array(
            'subscription_queue_subscriber_fkey' => array('profile', array('subscriber' => 'id')),
            'subscription_queue_subscribed_fkey' => array('profile', array('subscribed' => 'id')),));
   }


   // -------------------------------------------------------------------------
   // Function: saveNew
   // Save a new subscription request queue item
   public static function saveNew(Profile $subscriber, Profile $subscribed) {
      if (self::exists($subscriber, $subscribed)) {
         throw new AlreadyFulfilledException(_('This subscription request is already in progress.'));
      }
      $rq = new Subscription_queue();
      $rq->subscriber = $subscriber->id;
      $rq->subscribed = $subscribed->id;
      $rq->created = common_sql_now();
      $rq->insert();
      return $rq;
   }


   // ------------------------------------------------------------------------
   // Function: exists
   // Returns whether the given subscriber to subscribee has a queued
   // subscription.
   static function exists(Profile $subscriber, Profile $other) {
      $sub = Subscription_queue::pkeyGet(array('subscriber' => $subscriber->getID(),
                                               'subscribed' => $other->getID()));
      return ($sub instanceof Subscription_queue);
   }


   // -------------------------------------------------------------------------
   // Function: getSubQueue
   // Returns an object representing the subscription queue
   static function getSubQueue(Profile $subscriber, Profile $other) {
      // This is essentially a pkeyGet but we have an object to return in NoResultException
      $sub = new Subscription_queue();
      $sub->subscriber = $subscriber->id;
      $sub->subscribed = $other->id;
      if (!$sub->find(true)) {
         throw new NoResultException($sub);
      }
      return $sub;
   }


   // -------------------------------------------------------------------------
   // Function: complete
   // Complete a pending subscription, as we've got approval of some sort.
   //
   // Returns:
   // o Subscription
   public function complete() {
      $subscriber = Profile::getKV('id', $this->subscriber);
      $subscribed = Profile::getKV('id', $this->subscribed);
      try {
         $sub = Subscription::start($subscriber, $subscribed, Subscription::FORCE);
         $this->delete();
      } catch (AlreadyFulfilledException $e) {
         common_debug('Tried to start a subscription which already existed.');
      }
      return $sub;
   }


   // -------------------------------------------------------------------------
   // Function: abort
   // Cancel an outstanding subscription request to the other profile and fire
   // the appropriate events.
   public function abort() {
      $subscriber = Profile::getKV('id', $this->subscriber);
      $subscribed = Profile::getKV('id', $this->subscribed);
      if (Event::handle('StartCancelSubscription', array($subscriber, $subscribed))) {
         $this->delete();
         Event::handle('EndCancelSubscription', array($subscriber, $subscribed));
      }
    }


   // -------------------------------------------------------------------------
   // Function: notify
   // Send notifications via email etc to group administrators about
   // this exciting new pending moderation queue item!
   public function notify() {
      $other = Profile::getKV('id', $this->subscriber);
      $listenee = User::getKV('id', $this->subscribed);
      mail_subscribe_pending_notify_profile($listenee, $other);
   }
}

// END OF FILE
// ============================================================================
?>