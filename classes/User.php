<?php
/* ============================================================================
 * Title: User
 * User table definition
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
 * User table definition
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou <evan@prodromou.name>
 * o Matthew Gregg <matthew.gregg@gmail.com>
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Ori Avtalion <ori@avtalion.name>
 * o Zach Copley <zach@copley.name>
 * o Ciaran Gultnieks <ciaran@ciarang.com>
 * o Michele Azzolari <macno@macno.org>
 * o Zach Copley <zach@copley.name>
 * o Robin Millette <robin@millette.info>
 * o Leslie Michael Orchard <l.m.orchard@pobox.com>
 * o Brenda Wallace <shiny@cpan.org>
 * o Craig Andrews <candrews@integralblue.com>
 * o Carlos Perilla <deepspawn@valkertown.org>
 * o Sarven Capadisli <csarven@status.net>
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Joshua Wise <jwise@nvidia.com>
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

// =============================================================================
// Class: User
// Superclass containing the representation of a user in the database as well as
// the related interfaces.
//
// It should be noted this is the USER object, not the PROFILE object for that
// user, although it does contain many interfaces to the profile object to the
// associated profile for ease of use.
//
// Properties:
// o __table = 'user'     - table name
// o id                   - int(4)  primary_key not_null
// o nickname             - varchar(64)  unique_key
// o password             - varchar(191)               not 255 because utf8mb4 takes more space
// o email                - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o incomingemail        - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o emailnotifysub       - tinyint(1)   default_1
// o emailnotifyfav       - tinyint(1)   default_1
// o emailnotifynudge     - tinyint(1)   default_1
// o emailnotifymsg       - tinyint(1)   default_1
// o emailnotifyattn      - tinyint(1)   default_1
// o language             - varchar(50)
// o timezone             - varchar(50)
// o emailpost            - tinyint(1)   default_1
// o sms                  - varchar(64)  unique_key
// o carrier              - int(4)
// o smsnotify            - tinyint(1)
// o smsreplies           - tinyint(1)
// o smsemail             - varchar(191)               not 255 because utf8mb4 takes more space
// o uri                  - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o autosubscribe        - tinyint(1)
// o subscribe_policy     - tinyint(1)
// o urlshorteningservice - varchar(50)   default_ur1.ca
// o private_stream       - tinyint(1)   default_0
// o created              - datetime()   not_null
// o modified             - timestamp()   not_null default_CURRENT_TIMESTAMP
class User extends Managed_DataObject {
   const SUBSCRIBE_POLICY_OPEN = 0;
   const SUBSCRIBE_POLICY_MODERATE = 1;

   public $__table = 'user';                            // table name
   public $id;
   public $nickname;
   public $password;
   public $email;
   public $incomingemail;
   public $emailnotifysub;
   public $emailnotifyfav;
   public $emailnotifynudge;
   public $emailnotifymsg;
   public $emailnotifyattn;
   public $language;
   public $timezone;
   public $emailpost;
   public $sms;
   public $carrier;
   public $smsnotify;
   public $smsreplies;
   public $smsemail;
   public $uri;
   public $autosubscribe;
   public $subscribe_policy;
   public $urlshorteningservice;
   public $private_stream;
   public $created;
   public $modified;

   protected $_profile = array();

   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array describing the underlying schema for the user data in
   // the database.
   public static function schemaDef() {
      return array(
         'description' => 'local users',
         'fields' => array(
            'id' => array('type' => 'int', 'not null' => true, 'description' => 'foreign key to profile table'),
            'nickname' => array('type' => 'varchar', 'length' => 64, 'description' => 'nickname or username, duped in profile'),
            'password' => array('type' => 'varchar', 'length' => 191, 'description' => 'salted password, can be null for OpenID users'),
            'email' => array('type' => 'varchar', 'length' => 191, 'description' => 'email address for password recovery etc.'),
            'incomingemail' => array('type' => 'varchar', 'length' => 191, 'description' => 'email address for post-by-email'),
            'emailnotifysub' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'Notify by email of subscriptions'),
            'emailnotifyfav' => array('type' => 'int', 'size' => 'tiny', 'default' => null, 'description' => 'Notify by email of favorites'),
            'emailnotifynudge' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'Notify by email of nudges'),
            'emailnotifymsg' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'Notify by email of direct messages'),
            'emailnotifyattn' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'Notify by email of @-replies'),
            'language' => array('type' => 'varchar', 'length' => 50, 'description' => 'preferred language'),
            'timezone' => array('type' => 'varchar', 'length' => 50, 'description' => 'timezone'),
            'emailpost' => array('type' => 'int', 'size' => 'tiny', 'default' => 1, 'description' => 'Post by email'),
            'sms' => array('type' => 'varchar', 'length' => 64, 'description' => 'sms phone number'),
            'carrier' => array('type' => 'int', 'description' => 'foreign key to sms_carrier'),
            'smsnotify' => array('type' => 'int', 'size' => 'tiny', 'default' => 0, 'description' => 'whether to send notices to SMS'),
            'smsreplies' => array('type' => 'int', 'size' => 'tiny', 'default' => 0, 'description' => 'whether to send notices to SMS on replies'),
            'smsemail' => array('type' => 'varchar', 'length' => 191, 'description' => 'built from sms and carrier'),
            'uri' => array('type' => 'varchar', 'length' => 191, 'description' => 'universally unique identifier, usually a tag URI'),
            'autosubscribe' => array('type' => 'int', 'size' => 'tiny', 'default' => 0, 'description' => 'automatically subscribe to users who subscribe to us'),
            'subscribe_policy' => array('type' => 'int', 'size' => 'tiny', 'default' => 0, 'description' => '0 = anybody can subscribe; 1 = require approval'),
            'urlshorteningservice' => array('type' => 'varchar', 'length' => 50, 'default' => 'internal', 'description' => 'service to use for auto-shortening URLs'),
            'private_stream' => array('type' => 'int', 'size' => 'tiny', 'default' => 0, 'description' => 'whether to limit all notices to followers only'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('id'),
         'unique keys' => array(
            'user_nickname_key' => array('nickname'),
            'user_email_key' => array('email'),
            'user_incomingemail_key' => array('incomingemail'),
            'user_sms_key' => array('sms'),
            'user_uri_key' => array('uri'),),
         'foreign keys' => array(
            'user_id_fkey' => array('profile', array('id' => 'id')),
            'user_carrier_fkey' => array('sms_carrier', array('carrier' => 'id')),),
         'indexes' => array(
            'user_smsemail_idx' => array('smsemail'),),);
   }


   // -------------------------------------------------------------------------
   // Function: __sleep
   // Magic function called at serialize() time.
   //
   // We use this to drop a couple process-specific references from 
   // DB_DataObject which can cause trouble in future processes.
   //
   // Returns:
   // o array of variable names to include in serialization.
   function __sleep() {
      $vars = parent::__sleep();
      $skip = array('_profile');
      return array_diff($vars, $skip);
   }


   // -------------------------------------------------------------------------
   // Function: getProfile
   // Returns the profile associated with this user.
   //
   // Error States:
   // o throws UserNoProfileException if user has no profile
   public function getProfile() {
      if (!isset($this->_profile[$this->id])) {
         $profile = Profile::getKV('id', $this->id);
         if (!$profile instanceof Profile) {
                throw new UserNoProfileException($this);
         }
         $this->_profile[$this->id] = $profile;
      }
      return $this->_profile[$this->id];
   }


   // -------------------------------------------------------------------------
   // Function: sameAs
   // Duplication - check - returns if the profile for this user is identical
   // to a given $other profile.
   public function sameAs(Profile $other) {
      return $this->getProfile()->sameAs($other);
   }


   // -------------------------------------------------------------------------
   // Function: getUri
   // Returns the URI of this user.
   public function getUri() {
      return $this->uri;
   }


   // -------------------------------------------------------------------------
   // Function: getNickname
   // Returns the nickname of this user from their profile.
   public function getNickname() {
      return $this->getProfile()->getNickname();
   }


   // -------------------------------------------------------------------------
   // Function: getByNickname
   // Returns a user account by a given nickname.
   //
   // Error States:
   // o throws NoSuchUserException if the user is not found.
   static function getByNickname($nickname) {
      $user = User::getKV('nickname', $nickname);
      if (!$user instanceof User) {
         throw new NoSuchUserException(array('nickname' => $nickname));
      }
      return $user;
   }


   // --------------------------------------------------------------------------
   // Function: isSubscribed
   // Returns true/false whether this user is subscribed to the profile $other.
   function isSubscribed(Profile $other) {
      return $this->getProfile()->isSubscribed($other);
   }


   // -------------------------------------------------------------------------
   // Function: hasPendingSubscription
   // Returns true/false whether this user has a pending subscription to the
   // profile $other.
   function hasPendingSubscription(Profile $other) {
      return $this->getProfile()->hasPendingSubscription($other);
   }


   // -------------------------------------------------------------------------
   // Function: getCurrentNotice
   // Get the most recent notice posted by this user, if any.
   //
   // @return mixed Notice or null
   function getCurrentNotice() {
      return $this->getProfile()->getCurrentNotice();
   }


   // Function: getCarrier
   // Returns the SMS carrier gateway used by a user, if one is being used.
   function getCarrier() {
      return Sms_carrier::getKV('id', $this->carrier);
   }


   // -------------------------------------------------------------------------
   // Function: hasBlocked
   // Returns true/false whether the user has profile $other blocked.
   function hasBlocked(Profile $other) {
      return $this->getProfile()->hasBlocked($other);
   }



   // -------------------------------------------------------------------------
   // Function: register
   // Register a new user account and profile and set up default subscriptions.
   // If a new-user welcome message is configured, this will be sent.
   //
   // Parameters:
   // o array $fields - associative array of optional properties
   //    o string 'bio'
   //    o string 'xmpp'
   //    o string 'toxid'
   //    o string 'matrix'
   //    o string 'donateurl'
   //    o string 'gpgpubkey'
   //    o string 'email'
   //    o bool 'email_confirmed' pass true to mark email as pre-confirmed
   //    o string 'fullname'
   //    o string 'homepage'
   //    o string 'location' informal string description of geolocation
   //    o float 'lat' decimal latitude for geolocation
   //    o float 'lon' decimal longitude for geolocation
   //    o int 'location_id' geoname identifier
   //    o int 'location_ns' geoname namespace to interpret location_id
   //    o string 'nickname' REQUIRED
   //    o string 'password' (may be missing for eg OpenID registrations)
   //    o string 'code' invite code
   //    o ?string 'uri' permalink to notice; defaults to local notice URL
   // o boolean accept_email_fail - what it says on the tin
   //
   // Returns:
   // o User object representing newly-created user.
   //
   // Error States:
   // o throws  Exception on failure
   static function register(array $fields, $accept_email_fail=false) {
      // MAGICALLY put fields into current scope
      extract($fields);

      $profile = new Profile();
      if (!empty($email)) {
         $email = common_canonical_email($email);
      }

      // Normalize _and_ check whether it is in use. Throw NicknameException on failure.
      $profile->nickname = Nickname::normalize($nickname, true);
      $profile->profileurl = common_profile_url($profile->nickname);
      if (!empty($fullname)) {
         $profile->fullname = $fullname;
      }
      if (!empty($homepage)) {
         $profile->homepage = $homepage;
      }
      if (!empty($bio)) {
         $profile->bio = $bio;
      }
      if (!empty($location)) {
         $profile->location = $location;
         $loc = Location::fromName($location);
         if (!empty($loc)) {
            $profile->lat         = $loc->lat;
            $profile->lon         = $loc->lon;
            $profile->location_id = $loc->location_id;
            $profile->location_ns = $loc->location_ns;
         }
      }

      $profile->created = common_sql_now();

      if (!empty($xmpp)) {
         $profile->xmpp = $xmpp;
      }
      if (!empty($toxid)) {
         $profile->toxid = $toxid;
      }
      if (!empty($matrix)) {
         $profile->matrix = $matrix;
      }
      if (!empty($donateurl)) {
         $profile->donateurl = $donateurl;
      }
      if (!empty($gpgpubkey)) {
         $profile->gpgpubkey = $gpgpubkey;
      }

      $user = new User();
      $user->nickname = $profile->nickname;
      $invite = null;

      // Users who respond to invite email have proven their ownership of that address
      if (!empty($code)) {
         $invite = Invitation::getKV($code);
         if ($invite instanceof Invitation && $invite->address && $invite->address_type == 'email' && $invite->address == $email) {
            $user->email = $invite->address;
         }
      }
      if(isset($email_confirmed) && $email_confirmed) {
         $user->email = $email;
      }

      // Set default-on options here, otherwise they'll be disabled
      // initially for sites using caching, since the initial encache
      // doesn't know about the defaults in the database.
      $user->emailnotifysub = 1;
      $user->emailnotifynudge = 1;
      $user->emailnotifymsg = 1;
      $user->emailnotifyattn = 1;
      $user->emailpost = 1;
      $user->created = common_sql_now();

      if (Event::handle('StartUserRegister', array($profile))) {
         $profile->query('BEGIN');
         $id = $profile->insert();
         if ($id === false) {
            common_log_db_error($profile, 'INSERT', __FILE__);
            $profile->query('ROLLBACK');
            // TRANS: Profile data could not be inserted for some reason.
            throw new ServerException(_m('Could not insert profile data for new user.'));
         }

         // Necessary because id has been known to be reissued.
         if ($profile->hasRole(Profile_role::DELETED)) {
            $profile->revokeRole(Profile_role::DELETED);
         }

         $user->id = $id;
         if (!empty($uri)) {
            $user->uri = $uri;
         } else {
            $user->uri = common_user_uri($user);
         }

         if (!empty($password)) { // may not have a password for OpenID users
            $user->password = common_munge_password($password);
         }

         $result = $user->insert();
         if ($result === false) {
            common_log_db_error($user, 'INSERT', __FILE__);
            $profile->query('ROLLBACK');
            // TRANS: User data could not be inserted for some reason.
            throw new ServerException(_m('Could not insert user data for new user.'));
         }

         // Everyone is subscribed to themself
         $subscription = new Subscription();
         $subscription->subscriber = $user->id;
         $subscription->subscribed = $user->id;
         $subscription->created = $user->created;
         $result = $subscription->insert();
         if (!$result) {
            common_log_db_error($subscription, 'INSERT', __FILE__);
            $profile->query('ROLLBACK');
            // TRANS: Subscription data could not be inserted for some reason.
            throw new ServerException(_m('Could not insert subscription data for new user.'));
         }

         // Mark that this invite was converted
         if (!empty($invite)) {
            $invite->convert($user);
         }
         if (!empty($email) && empty($user->email)) {
            // The actual email will be sent further down, after the database COMMIT
            $confirm = new Confirm_address();
            $confirm->code = common_confirmation_code(128);
            $confirm->user_id = $user->id;
            $confirm->address = $email;
            $confirm->address_type = 'email';
            $result = $confirm->insert();
            if ($result===false) {
               common_log_db_error($confirm, 'INSERT', __FILE__);
               $profile->query('ROLLBACK');
               // TRANS: Email confirmation data could not be inserted for some reason.
               throw new ServerException(_m('Could not insert email confirmation data for new user.'));
            }
         }

         if (!empty($code) && $user->email) {
            $user->emailChanged();
         }

         // Default system subscription
         $defnick = common_config('newuser', 'default');
         if (!empty($defnick)) {
            $defuser = User::getKV('nickname', $defnick);
            if (empty($defuser)) {
               common_log(LOG_WARNING, sprintf("Default user %s does not exist.", $defnick), __FILE__);
            } else {
               Subscription::ensureStart($profile, $defuser->getProfile());
            }
         }

         $profile->query('COMMIT');
         if (!empty($email) && empty($user->email)) {
            try {
               $confirm->sendConfirmation();
            } catch (EmailException $e) {
               common_log(LOG_ERR, "Could not send user registration email for user id=={$profile->getID()}: {$e->getMessage()}");
               if (!$accept_email_fail) {
                  throw $e;
               }
            }
         }

         // Welcome message
         $welcome = common_config('newuser', 'welcome');

         if (!empty($welcome)) {
            $welcomeuser = User::getKV('nickname', $welcome);
            if (empty($welcomeuser)) {
               common_log(LOG_WARNING, sprintf("Welcome user %s does not exist.", $defnick), __FILE__);
            } else {
               $notice = Notice::saveNew($welcomeuser->id,
                                              // TRANS: Notice given on user registration.
                                              // TRANS: %1$s is the sitename, $2$s is the registering user's nickname.
                                              sprintf(_('Welcome to %1$s, @%2$s!'),
                                                      common_config('site', 'name'),
                                                      $profile->getNickname()),
                                              'system');
            }
         }

         Event::handle('EndUserRegister', array($profile));
      }

      if (!$user instanceof User || empty($user->id)) {
         throw new ServerException('User could not be registered. Probably an event hook that failed.');
      }

      return $user;
   }

   
   // -------------------------------------------------------------------------
   // Function: emailChanged
   // Things we do when the email changes
   function emailChanged() {
      $invites = new Invitation();
      $invites->address = $this->email;
      $invites->address_type = 'email';
      if ($invites->find()) {
         while ($invites->fetch()) {
            try {
               $other = Profile::getByID($invites->user_id);
               Subscription::start($other, $this->getProfile());
            } catch (NoResultException $e) {
               // profile did not exist
            } catch (AlreadyFulfilledException $e) {
               // already subscribed to this profile
            } catch (Exception $e) {
               common_log(LOG_ERR, 'On-invitation-completion subscription failed when subscribing '._ve($invites->user_id).' to '.$this->getProfile()->getID().': '._ve($e->getMessage()));
            }
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: mutuallySubscribed
   // Returns true/false whether this user is subscribed to profile $other and
   // that profile is in turn subscribed to them.
   function mutuallySubscribed(Profile $other) {
      return $this->getProfile()->mutuallySubscribed($other);
   }


   // -------------------------------------------------------------------------
   // Function: mutuallySubscribedUsers
   // Returns an array of users that mutually subscribe to this user.
   //
   // FIXME:
   // o 3-way join; probably should get cached
   function mutuallySubscribedUsers() {
      $UT = common_config('db','type')=='pgsql'?'"user"':'user';
      $qry = "SELECT $UT.* " .
         "FROM subscription sub1 JOIN $UT ON sub1.subscribed = $UT.id " .
         "JOIN subscription sub2 ON $UT.id = sub2.subscriber " .
         'WHERE sub1.subscriber = %d and sub2.subscribed = %d ' .
         "ORDER BY $UT.nickname";
      $user = new User();
      $user->query(sprintf($qry, $this->id, $this->id));
      return $user;
   }


   // -------------------------------------------------------------------------
   // Function: getReplies
   // Returns an array of replies to this user.
   function getReplies($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0) {
      return $this->getProfile()->getReplies($offset, $limit, $since_id, $before_id);
   }


   // -------------------------------------------------------------------------
   // Function: getTaggedNotices
   // Returns an array of notices tagged by this user.
   function getTaggedNotices($tag, $offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0) {
      return $this->getProfile()->getTaggedNotices($tag, $offset, $limit, $since_id, $before_id);
   }


   // -------------------------------------------------------------------------
   // Function: getReplies
   // Returns an array of notices from this user.
   function getNotices($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0) {
      return $this->getProfile()->getNotices($offset, $limit, $since_id, $before_id);
   }


   // -------------------------------------------------------------------------
   // Function: block
   // Add a new block record for this user, indicating they blocked target
   // profile $other.  This will also remove subscriptions where they exist,
   // locally.  (But not remotely.)
   function block(Profile $other) {
      // no blocking (and thus unsubbing from) yourself
      if ($this->id == $other->id) {
         common_log(LOG_WARNING, sprintf("Profile ID %d (%s) tried to block themself.", $this->id, $this->nickname));
         return false;
      }

      $block = new Profile_block();

      // Begin a transaction
      $block->query('BEGIN');
      $block->blocker = $this->id;
      $block->blocked = $other->id;
      $result = $block->insert();
      if (!$result) {
         common_log_db_error($block, 'INSERT', __FILE__);
         return false;
      }

      $self = $this->getProfile();
      if (Subscription::exists($other, $self)) {
         Subscription::cancel($other, $self);
      }
      if (Subscription::exists($self, $other)) {
         Subscription::cancel($self, $other);
      }
      $block->query('COMMIT');
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: unblock
   // Remove the block record this has for target profile $other.
   function unblock(Profile $other) {
      // Get the block record
      $block = Profile_block::exists($this->getProfile(), $other);
      if (!$block) {
         return false;
      }

      $result = $block->delete();
      if (!$result) {
         common_log_db_error($block, 'DELETE', __FILE__);
         return false;
      }
      return true;
   }


    function isMember(User_group $group)
    {
        return $this->getProfile()->isMember($group);
    }

    function isAdmin(User_group $group)
    {
        return $this->getProfile()->isAdmin($group);
    }

    function getGroups($offset=0, $limit=null)
    {
        return $this->getProfile()->getGroups($offset, $limit);
    }

    /**
     * Request to join the given group.
     * May throw exceptions on failure.
     *
     * @param User_group $group
     * @return Group_member
     */
    function joinGroup(User_group $group)
    {
        return $this->getProfile()->joinGroup($group);
    }

    /**
     * Leave a group that this user is a member of.
     *
     * @param User_group $group
     */
    function leaveGroup(User_group $group)
    {
        return $this->getProfile()->leaveGroup($group);
    }

    function getSubscribed($offset=0, $limit=null)
    {
        return $this->getProfile()->getSubscribed($offset, $limit);
    }

    function getSubscribers($offset=0, $limit=null)
    {
        return $this->getProfile()->getSubscribers($offset, $limit);
    }

    function getTaggedSubscribers($tag, $offset=0, $limit=null)
    {
        return $this->getProfile()->getTaggedSubscribers($tag, $offset, $limit);
    }

    function getTaggedSubscriptions($tag, $offset=0, $limit=null)
    {
        return $this->getProfile()->getTaggedSubscriptions($tag, $offset, $limit);
    }

    function hasRight($right)
    {
        return $this->getProfile()->hasRight($right);
    }

    function delete($useWhere=false)
    {
        if (empty($this->id)) {
            common_log(LOG_WARNING, "Ambiguous User->delete(); skipping related tables.");
            return parent::delete($useWhere);
        }

        try {
            if (!$this->hasRole(Profile_role::DELETED)) {
                $profile = $this->getProfile();
                $profile->delete();
            }
        } catch (UserNoProfileException $unp) {
            common_log(LOG_INFO, "User {$this->nickname} has no profile; continuing deletion.");
        }

        $related = array(
                         'Confirm_address',
                         'Remember_me',
                         'Foreign_link',
                         'Invitation',
                         );

        Event::handle('UserDeleteRelated', array($this, &$related));

        foreach ($related as $cls) {
            $inst = new $cls();
            $inst->user_id = $this->id;
            $inst->delete();
        }

        $this->_deleteTags();
        $this->_deleteBlocks();

        return parent::delete($useWhere);
    }

    function _deleteTags()
    {
        $tag = new Profile_tag();
        $tag->tagger = $this->id;
        $tag->delete();
    }

    function _deleteBlocks()
    {
        $block = new Profile_block();
        $block->blocker = $this->id;
        $block->delete();
        // XXX delete group block? Reset blocker?
    }

    function hasRole($name)
    {
        return $this->getProfile()->hasRole($name);
    }

    function grantRole($name)
    {
        return $this->getProfile()->grantRole($name);
    }

    function revokeRole($name)
    {
        return $this->getProfile()->revokeRole($name);
    }

    function isSandboxed()
    {
        return $this->getProfile()->isSandboxed();
    }

    function isSilenced()
    {
        return $this->getProfile()->isSilenced();
    }

    function receivesEmailNotifications()
    {
        // We could do this in one large if statement, but that's not as easy to read
        // Don't send notifications if we don't know the user's email address or it is
        // explicitly undesired by the user's own settings.
        if (empty($this->email) || !$this->emailnotifyattn) {
            return false;
        }
        // Don't send notifications to a user who is sandboxed or silenced
        if ($this->isSandboxed() || $this->isSilenced()) {
            return false;
        }
        return true;
    }

    function repeatedByMe($offset=0, $limit=20, $since_id=null, $max_id=null)
    {
        // FIXME: Use another way to get Profile::current() since we
        // want to avoid confusion between session user and queue processing.
        $stream = new RepeatedByMeNoticeStream($this->getProfile(), Profile::current());
        return $stream->getNotices($offset, $limit, $since_id, $max_id);
    }


   // -------------------------------------------------------------------------
   // Function: repeatsOfMe
   // Returns an array of notices of this user which have been repeated by 
   // others, that we are aware of.

   // FIXME:
   // Use another way to get Profile::current() since we want to avoid 
   // confusion between session user and queue processing.
   function repeatsOfMe($offset=0, $limit=20, $since_id=null, $max_id=null) {
      $stream = new RepeatsOfMeNoticeStream($this->getProfile(), Profile::current());
      return $stream->getNotices($offset, $limit, $since_id, $max_id);
   }


   // -------------------------------------------------------------------------
   // Functions: repeatedToMe
   // Returns an array of notices repeated to this user.
   public function repeatedToMe($offset=0, $limit=20, $since_id=null, $max_id=null) {
      return $this->getProfile()->repeatedToMe($offset, $limit, $since_id, $max_id);
   }


   // -------------------------------------------------------------------------
   // Function: siteOwner
   // Returns the User object of the account labelled as the site owner.  Note
   // that this will return the FIRST account found with such a role if
   // multiple are assigned.
   public static function siteOwner() {
      $owner = self::cacheGet('user:site_owner');

      if ($owner === false) { // cache miss
         $pr = new Profile_role();
         $pr->role = Profile_role::OWNER;
         $pr->orderBy('created');
         $pr->limit(1);
         if (!$pr->find(true)) {
            throw new NoResultException($pr);
         }

         $owner = User::getKV('id', $pr->profile_id);
         self::cacheSet('user:site_owner', $owner);
      }
      if ($owner instanceof User) {
         return $owner;
      }
      throw new ServerException(_('No site owner configured.'));
   }


   // -------------------------------------------------------------------------
   // Function: singleUser
   // Pull the primary site account to use in single-user mode.
   // If a valid user nickname is listed in 'singleuser':'nickname'
   // in the config, this will be used; otherwise the site owner
   // account is taken by default.
   //
   // Returns:
   // o object User
   //
   // Error States:
   // o throws ServerException if no valid single user account is present
   // o throws ServerException if called when not in single-user mode
   public static function singleUser() {
      if (!common_config('singleuser', 'enabled')) {
         // TRANS: Server exception.
         throw new ServerException(_('Single-user mode code called when not enabled.'));
      }
      if ($nickname = common_config('singleuser', 'nickname')) {
         $user = User::getKV('nickname', $nickname);
         if ($user instanceof User) {
            return $user;
         }
      }

      // If there was no nickname or no user by that nickname,
      // try the site owner. Throws exception if not configured.
      return User::siteOwner();
   }


   // -------------------------------------------------------------------------
   // Function: singleUserNickname
   // This is kind of a hack for using external setup code that's trying to
   // build single-user sites.
   //
   // Will still return a username if the config singleuser/nickname is set
   // even if the account doesn't exist, which normally indicates that the
   // site is horribly misconfigured.
   //
   // At the moment, we need to let it through so that router setup can
   // complete, otherwise we won't be able to create the account.
   //
   // This will be easier when we can more easily create the account and
   // *then* switch the site to 1user mode without jumping through hoops.
   //
   // Returns:
   // o string
   //
   // Error States:
   // o throws ServerException if no valid single user account is present
   // o throws ServerException if called when not in single-user mode
   static function singleUserNickname() {
      try {
         $user = User::singleUser();
         return $user->nickname;
      } catch (Exception $e) {
         if (common_config('singleuser', 'enabled') && common_config('singleuser', 'nickname')) {
            common_log(LOG_WARNING, "Warning: code attempting to pull single-user nickname when the account does not exist. If this is not setup time, this is probably a bug.");
            return common_config('singleuser', 'nickname');
         }
         throw $e;
      }
   }


   // -------------------------------------------------------------------------
   // Function: shortenLinks
   // Find and shorten links in the given text using this user's URL shortening
   // settings.
   //
   // By default, links will be left untouched if the text is shorter than the
   // configured maximum notice length. Pass true for the $always parameter
   // to force all links to be shortened regardless.
   //
   // Side effects: may save file and file_redirection records for referenced URLs.
   //
   // Parameters:
   // o string $text
   // o boolean $always
   //
   // Returns:
   // o string
   public function shortenLinks($text, $always=false) {
      return common_shorten_links($text, $always, $this);
   }


   // -------------------------------------------------------------------------
   // Function: getConnectedApps
   // Get a list of OAuth client applications that have access to this
   // user's account.
   function getConnectedApps($offset = 0, $limit = null) {
      $qry =
         'SELECT u.* ' .
         'FROM oauth_application_user u, oauth_application a ' .
         'WHERE u.profile_id = %d ' .
         'AND a.id = u.application_id ' .
         'AND u.access_type > 0 ' .
         'ORDER BY u.created DESC ';

      if ($offset > 0) {
         if (common_config('db','type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
         } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
         }
      }

      $apps = new Oauth_application_user();
      $cnt = $apps->query(sprintf($qry, $this->id));
      return $apps;
   }


   // -------------------------------------------------------------------------
   // Function: recoverPassword
   // Sends a password recovery email for this user if they have an email on
   // record.  If faking email recovery is enabled, it will look like one has
   // been sent regardless of this to the end user.  Warning: In the event of
   // an unconfirmed email address, it may actually be legit to have multiple
   // folks who have claimed, but not yet confirmed, the same address. We'll
   // only send to the first one that comes up.
   //
   // FIXME: Email body is constructed in hard code here, we should put it in
   // a template file at the very least that is then transcluded.
   static function recoverPassword($nore) {
      require_once(INSTALLDIR . '/lib/mail.php');

      // $confirm_email will be used as a fallback if our user doesn't have a confirmed email
      $confirm_email = null;

      if (common_is_email($nore)) {
         $user = User::getKV('email', common_canonical_email($nore));

         // See if it's an unconfirmed email address
         if (!$user instanceof User) {
            $confirm_email = new Confirm_address();
            $confirm_email->address = common_canonical_email($nore);
            $confirm_email->address_type = 'email';
            if ($confirm_email->find(true)) {
               $user = User::getKV('id', $confirm_email->user_id);
            }
         }

         // No luck finding anyone by that email address.
         if (!$user instanceof User) {
            if (common_config('site', 'fakeaddressrecovery')) {
               // Return without actually doing anything! We fake address recovery
               // to avoid revealing which email addresses are registered with the site.
               return;
            }
            // TRANS: Information on password recovery form if no known e-mail address was specified.
            throw new ClientException(_('No user with that email address exists here.'));
         }
      } else {
         // This might throw a NicknameException on bad nicknames
         $user = User::getKV('nickname', common_canonical_nickname($nore));
         if (!$user instanceof User) {
            // TRANS: Information on password recovery form if no known username was specified.
            throw new ClientException(_('No user with that nickname exists here.'));
         }
      }

      // Try to get an unconfirmed email address if they used a user name
      if (empty($user->email) && $confirm_email === null) {
         $confirm_email = new Confirm_address();
         $confirm_email->user_id = $user->id;
         $confirm_email->address_type = 'email';
         $confirm_email->find();
         if (!$confirm_email->fetch()) {
            // Nothing found, so let's reset it to null
            $confirm_email = null;
         }
      }

      if (empty($user->email) && !$confirm_email instanceof Confirm_address) {
         // TRANS: Client error displayed on password recovery form if a user does not have a registered e-mail address.
         throw new ClientException(_('No registered email address for that user.'));
      }

      // Success! We have a valid user and a confirmed or unconfirmed email address
      $confirm = new Confirm_address();
      $confirm->code = common_confirmation_code(128);
      $confirm->address_type = 'recover';
      $confirm->user_id = $user->id;
      $confirm->address = $user->email ?: $confirm_email->address;
      if (!$confirm->insert()) {
         common_log_db_error($confirm, 'INSERT', __FILE__);
         // TRANS: Server error displayed if e-mail address confirmation fails in the database on the password recovery form.
         throw new ServerException(_('Error saving address confirmation.'));
      }

      // @todo FIXME: needs i18n.
      $body = "Hey, $user->nickname.";
      $body .= "\n\n";
      $body .= 'Someone just asked for a new password ' .
               'for this account on ' . common_config('site', 'name') . '.';
      $body .= "\n\n";
      $body .= 'If it was you, and you want to confirm, use the URL below:';
      $body .= "\n\n";
      $body .= "\t".common_local_url('recoverpassword', array('code' => $confirm->code));
      $body .= "\n\n";
      $body .= 'If not, just ignore this message.';
      $body .= "\n\n";
      $body .= 'Thanks for your time, ';
      $body .= "\n";
      $body .= common_config('site', 'name');
      $body .= "\n";

      $headers = _mail_prepare_headers('recoverpassword', $user->nickname, $user->nickname);
      // TRANS: Subject for password recovery e-mail.
      mail_to_user($user, _('Password recovery requested'), $body, $headers, $confirm->address);
   }


   // -------------------------------------------------------------------------
   // Function: streamModeOnly
   // Returns whether to only display notice streams in the classic UI.  Legacy 
   // feature - kept for those who want to use "oldschool" config options.
   function streamModeOnly() {
      if (common_config('oldschool', 'enabled')) {
         $osp = Old_school_prefs::getKV('user_id', $this->id);
         if (!empty($osp)) {
            return $osp->stream_mode_only;
         }
      }
      return false;
   }


   // -------------------------------------------------------------------------
   // Function: streamNicknames
   // Returns whether to use nicknames as opposed to the full names in classic
   // UI streams.  Legacy feature - kept for those who want to use "oldschool"
   // config options.
   function streamNicknames() {
      if (common_config('oldschool', 'enabled')) {
         $osp = Old_school_prefs::getKV('user_id', $this->id);
         if (!empty($osp)) {
            return $osp->stream_nicknames;
         }
      }
      return false;
   }


   // -------------------------------------------------------------------------
   // Function: registrationActivity
   // Creates and returns an ActivityObject representing a new user registration.
   function registrationActivity() {
      $profile = $this->getProfile();

      $service = new ActivityObject();
      $service->type  = ActivityObject::SERVICE;
      $service->title = common_config('site', 'name');
      $service->link  = common_root_url();
      $service->id    = $service->link;

      $act = new Activity();
      $act->actor = $profile->asActivityObject();
      $act->verb = ActivityVerb::JOIN;
      $act->objects[] = $service;
      $act->id = TagURI::mint('user:register:%d',
                                $this->id);
      $act->time = strtotime($this->created);
      $act->title = _("Register");
      $act->content = sprintf(_('%1$s joined %2$s.'),
                                $profile->getBestName(),
                                $service->title);
      return $act;
   }


   // -------------------------------------------------------------------------
   // Function: isPrivateStream
   // Returns whether the user has their profile to private.
   public function isPrivateStream() {
      return $this->getProfile()->isPrivateStream();
   }


   // ------------------------------------------------------------------------
   // Function: hasPassword
   // Returns whether the user has a password set (false for remote users for
   // example.)
   public function hasPassword() {
      return !empty($this->password);
   }


   // -------------------------------------------------------------------------
   // Function: setPassword
   // Set the password associated with this user to the given $password.
   public function setPassword($password) {
      $orig = clone($this);
      $this->password = common_munge_password($password, $this->getProfile());

      if ($this->validate() !== true) {
         // TRANS: Form validation error on page where to change password.
         throw new ServerException(_('Error saving user; invalid.'));
      }

      if (!$this->update($orig)) {
         common_log_db_error($this, 'UPDATE', __FILE__);
         // TRANS: Server error displayed on page where to change password when password change
         // TRANS: could not be made because of a server error.
         throw new ServerException(_('Cannot save new password.'));
      }
   }


   // -------------------------------------------------------------------------
   // Function: delPref
   // Delete the given extended user preference of $namespace, $topic from the
   // profile of this user.
   public function delPref($namespace, $topic) {
      return $this->getProfile()->delPref($namespace, $topic);
   }


   // -------------------------------------------------------------------------
   // Function: getPref
   // Retrieves the given extended user preference of $namespace, $topic from the
   // profile of this user.
   public function getPref($namespace, $topic, $default=null) {
      return $this->getProfile()->getPref($namespace, $topic, $default);
   }


   // -------------------------------------------------------------------------
   // Function: getConfigPref
   // getPref, but we will fallback to the site configuration setting of the
   // same $namespace and $topic if the user hasn't specified a preference.
   public function getConfigPref($namespace, $topic) {
      return $this->getProfile()->getConfigPref($namespace, $topic);
   }


   // -------------------------------------------------------------------------
   // Function: setPref
   // Set the extended preference $namespace, $topic to contain the $data
   // specified in this user's profile.
   public function setPref($namespace, $topic, $data) {
      return $this->getProfile()->setPref($namespace, $topic, $data);
   }
}

// END OF FILE
// ============================================================================
?>
