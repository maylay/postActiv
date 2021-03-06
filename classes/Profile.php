<?php
/* ============================================================================
 * Title: Profile
 * Table Definition for profile
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
 * Superclass containing the database representation of user profiles and the
 * associated interfaces - with is a great many interfaces since user profiles
 * are our largest single data store.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Robin Millette <robin@millette.info>
 * o Zach Copley
 * o Sean Murphy <sgmurphy@gmail.com>
 * o Brenda Wallace <shiny@cpan.org>
 * o Craig Andrews <candrews@integralblue.com>
 * o Trever Fischer <wm161@wm161.net>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Dan Scott <dan@coffeecode.net>
 * o Joshua Wise <jwise@nvidia.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Hannes Mannerheim <h@nnesmannerhe.im>
 * o Chimo <chimo@chromic.org>
 * o Bob Mottram <bob@freedombone.net>
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
// Class: Profile
// Superclass representing the table definition for user Profiles and
// containing interfaces to interact with them.
//
// Properties:
// o __table = 'profile' - table name
// o id          - int(4)  primary_key not_null
// o nickname    - varchar(64)  multiple_key not_null
// o fullname    - text()
// o profileurl  - text()
// o homepage    - text()
// o bio         - text()  multiple_key
// o matrix      - text()
// o donateurl   - text()
// o toxid       - text()
// o xmpp        - text()
// o gpgpubkey   - text()
// o location    - text()
// o lat         - decimal(10,7)
// o lon         - decimal(10,7)
// o location_id - int(4)
// o location_ns - int(4)
// o created     - datetime()   not_null
// o modified    - timestamp()   not_null default_CURRENT_TIMESTAMP
class Profile extends Managed_DataObject {
   public $__table = 'profile';                         // table name
   public $id;
   public $nickname;
   public $fullname;
   public $profileurl;
   public $homepage;
   public $bio;
   public $location;
   public $lat;
   public $lon;
   public $location_id;
   public $location_ns;
   public $created;
   public $modified;
   public $matrix;
   public $donateurl;
   public $toxid;
   public $xmpp;
   public $gpgpubkey;
   
   protected $_user = array();
   protected $_group = array();


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns the table schema definition that represents how the table is set
   // up in the database.
   public static function schemaDef() {
      $def = array(
         'description' => 'local and remote users have profiles',
         'fields' => array(
            'id' => array('type' => 'serial', 'not null' => true, 'description' => 'unique identifier'),
            'nickname' => array('type' => 'varchar', 'length' => 64, 'not null' => true, 'description' => 'nickname or username', 'collate' => 'utf8mb4_general_ci'),
            'fullname' => array('type' => 'text', 'description' => 'display name', 'collate' => 'utf8mb4_general_ci'),
            'profileurl' => array('type' => 'text', 'description' => 'URL, cached so we dont regenerate'),
            'homepage' => array('type' => 'text', 'description' => 'identifying URL', 'collate' => 'utf8mb4_general_ci'),
            'bio' => array('type' => 'text', 'description' => 'descriptive biography', 'collate' => 'utf8mb4_general_ci'),
            'location' => array('type' => 'text', 'description' => 'physical location', 'collate' => 'utf8mb4_general_ci'),
            'lat' => array('type' => 'numeric', 'precision' => 10, 'scale' => 7, 'description' => 'latitude'),
            'lon' => array('type' => 'numeric', 'precision' => 10, 'scale' => 7, 'description' => 'longitude'),
            'location_id' => array('type' => 'int', 'description' => 'location id if possible'),
            'location_ns' => array('type' => 'int', 'description' => 'namespace for location'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            'gpgpubkey' => array('type' => 'text', 'description' => 'gpg public key', 'collate' => 'utf8mb4_general_ci'),
            'xmpp' => array('type' => 'text', 'description' => 'xmpp address', 'collate' => 'utf8mb4_general_ci'),
            'toxid' => array('type' => 'text', 'description' => 'tox id', 'collate' => 'utf8mb4_general_ci'),
            'matrix' => array('type' => 'text', 'description' => 'matrix address', 'collate' => 'utf8mb4_general_ci'),
            'donateurl' => array('type' => 'text', 'description' => 'donations link', 'collate' => 'utf8mb4_general_ci'),),
         'primary key' => array('id'),
         'indexes' => array(
            'profile_nickname_idx' => array('nickname'),));

      // Add a fulltext index
      if (common_config('search', 'type') == 'fulltext') {
         $def['fulltext indexes'] = array('nickname' => array('nickname', 'fullname', 'location', 'bio', 'homepage'));
      }
      return $def;
   }


   // -------------------------------------------------------------------------
   // Function: __sleep
   // Magic function called at serialize() time.
   //
   // We use this to drop a couple process-specific references
   // from DB_DataObject which can cause trouble in future
   // processes.
   //
   // @return array of variable names to include in serialization.
   function __sleep() {
      $vars = parent::__sleep();
      $skip = array('_user', '_group');
      return array_diff($vars, $skip);
   }


   // -------------------------------------------------------------------------
   // Function: getByEmail
   // Looks up a profile by the email associated with it.
   public static function getByEmail($email) {
      // in the future, profiles should have emails stored...
      $user = User::getKV('email', $email);
      if (!($user instanceof User)) {
         throw new NoSuchUserException(array('email'=>$email));
      }
      return $user->getProfile();
   }


   // -------------------------------------------------------------------------
   // Function: getUser
   // Returns the user associated with this profile.
   public function getUser() {
      if (!isset($this->_user[$this->id])) {
         $user = User::getKV('id', $this->id);
         if (!$user instanceof User) {
            throw new NoSuchUserException(array('id'=>$this->id));
         }
         $this->_user[$this->id] = $user;
      }
      return $this->_user[$this->id];
   }


   // ------------------------------------------------------------------------
   // Function: getGroup
   // Returns the group associated with this profile.
   public function getGroup() {
      if (!isset($this->_group[$this->id])) {
         $group = User_group::getKV('profile_id', $this->id);
         if (!$group instanceof User_group) {
            throw new NoSuchGroupException(array('profile_id'=>$this->id));
         }
         $this->_group[$this->id] = $group;
      }
      return $this->_group[$this->id];
   }


   // -------------------------------------------------------------------------
   // Function: isGroup
   // Returns true/false whether this profile is associated with a group.
   public function isGroup() {
      try {
         $this->getGroup();
         return true;
      } catch (NoSuchGroupException $e) {
         return false;
      }
   }


   // -------------------------------------------------------------------------
   // Function: isPerson
   // Returns true/false whether this profile is associated with an
   // individual user (as opposed to a group).
   public function isPerson() {
      return !$this->isGroup();
   }


   // -------------------------------------------------------------------------
   // Function: isLocal
   // Returns true/false whether this profile represents a local user.
   public function isLocal() {
      try {
         $this->getUser();
      } catch (NoSuchUserException $e) {
         return false;
      }
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: hasPassword
   // Returns false if the user has no password (which will always be the case
   // for remote users). This can be the case for OpenID logins or other
   // mechanisms which don't store a password hash.
   public function hasPassword() {
      try {
         return $this->getUser()->hasPassword();
      } catch (NoSuchUserException $e) {
         return false;
      }
   }


   // -------------------------------------------------------------------------
   // Function: getObjectType
   // Returns which ActivityObject represents the user represented by this
   // profile.
   public function getObjectType() {
      if ($this->isGroup()) {
         return ActivityObject::GROUP;
      } else {
         return ActivityObject::PERSON;
      }
   }


   // -------------------------------------------------------------------------
   // Function: getAvatar
   // Returns the avatar associated with this profile.
   public function getAvatar($width, $height=null) {
      return Avatar::byProfile($this, $width, $height);
   }


   // -------------------------------------------------------------------------
   // Function: setOriginal
   // A poorly-named function which sets the avatar associated with this
   // profile.
   public function setOriginal($filename) {
      if ($this->isGroup()) {
            // Until Group avatars are handled just like profile avatars.
            return $this->getGroup()->setOriginal($filename);
      }

      $imagefile = new ImageFile(null, Avatar::path($filename));

      $avatar = new Avatar();
      $avatar->profile_id = $this->id;
      $avatar->width = $imagefile->width;
      $avatar->height = $imagefile->height;
      $avatar->mediatype = image_type_to_mime_type($imagefile->type);
      $avatar->filename = $filename;
      $avatar->original = true;
      $avatar->created = common_sql_now();

      // XXX: start a transaction here
      if (!Avatar::deleteFromProfile($this, true) || !$avatar->insert()) {
         // If we can't delete the old avatars, let's abort right here.
         @unlink(Avatar::path($filename));
         return null;
      }
      return $avatar;
   }


   // -------------------------------------------------------------------------
   // Function: getBestName
   // Gets either the full name (if filled) or the nickname.
   //
   // Returns:
   // o string
   function getBestName() {
      return ($this->fullname) ? $this->fullname : $this->nickname;
   }


   // -------------------------------------------------------------------------
   // Function: getStreamName
   // Takes the currently scoped profile into account to give a name to list
   // in notice streams. Preferences may differ between profiles.
   function getStreamName() {
      $user = common_current_user();
      if ($user instanceof User && $user->streamNicknames()) {
            return $this->nickname;
      }
      return $this->getBestName();
   }


   // -------------------------------------------------------------------------
   // Function: getFancyName
   // Gets the full name (if filled) with acct URI, URL, or URI as a 
   // parenthetical (in that order, for each not found). If no full name is 
   // found only the second part is returned, without ()s.
   //
   // Returns:
   // o string
   function getFancyName() {
      $uri = null;
      try {
         $uri = $this->getAcctUri(false);
      } catch (ProfileNoAcctUriException $e) {
         try {
            $uri = $this->getUrl();
         } catch (InvalidUrlException $e) {
            $uri = $this->getUri();
         }
      }

        if (mb_strlen($this->getFullname()) > 0) {
            // TRANS: The "fancy name": Full name of a profile or group (%1$s) followed by some URI (%2$s) in parentheses.
            return sprintf(_m('FANCYNAME','%1$s (%2$s)'), $this->getFullname(), $uri);
        } else {
            return $uri;
        }
    }


   // -------------------------------------------------------------------------
   // Function: getCurrentNotice
   // Get the most recent notice posted by this user, if any.
   //
   // Returns:
   // o mixed Notice or null
   function getCurrentNotice(Profile $scoped=null) {
      try {
         $notice = $this->getNotices(0, 1, 0, 0, $scoped);
         if ($notice->fetch()) {
            if ($notice instanceof ArrayWrapper) {
               // hack for things trying to work with single notices
               // ...but this shouldn't happen anymore I think. Keeping it for safety...
               return $notice->_items[0];
            }
            return $notice;
         }
      } catch (PrivateStreamException $e) {
         // Maybe we should let this through if it's handled well upstream
         return null;
      }
      return null;
    }

    function getReplies($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0)
    {
        return Reply::stream($this->getID(), $offset, $limit, $since_id, $before_id);
    }

    function getTaggedNotices($tag, $offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $max_id=0)
    {
        //FIXME: Get Profile::current() some other way to avoid possible
        // confusion between current session profile and background processing.
        $stream = new TaggedProfileNoticeStream($this, $tag, Profile::current());

        return $stream->getNotices($offset, $limit, $since_id, $max_id);
    }

    function getNotices($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $max_id=0, Profile $scoped=null)
    {
        $stream = new ProfileNoticeStream($this, $scoped);

        return $stream->getNotices($offset, $limit, $since_id, $max_id);
    }

    function isMember(User_group $group)
    {
    	$groups = $this->getGroups(0, null);
        while ($groups instanceof User_group && $groups->fetch()) {
    	    if ($groups->id == $group->id) {
    	        return true;
    	    }
    	}
    	return false;
    }

    function isAdmin(User_group $group)
    {
        $gm = Group_member::pkeyGet(array('profile_id' => $this->id,
                                          'group_id' => $group->id));
        return (!empty($gm) && $gm->is_admin);
    }

    function isPendingMember($group)
    {
        $request = Group_join_queue::pkeyGet(array('profile_id' => $this->id,
                                                   'group_id' => $group->id));
        return !empty($request);
    }

    function getGroups($offset=0, $limit=PROFILES_PER_PAGE)
    {
        $ids = array();

        $keypart = sprintf('profile:groups:%d', $this->id);

        $idstring = self::cacheGet($keypart);

        if ($idstring !== false) {
            $ids = explode(',', $idstring);
        } else {
            $gm = new Group_member();

            $gm->profile_id = $this->id;

            if ($gm->find()) {
                while ($gm->fetch()) {
                    $ids[] = $gm->group_id;
                }
            }

            self::cacheSet($keypart, implode(',', $ids));
        }

        if (!is_null($offset) && !is_null($limit)) {
            $ids = array_slice($ids, $offset, $limit);
        }

        try {
            return User_group::multiGet('id', $ids);
        } catch (NoResultException $e) {
            return null;    // throw exception when we handle it everywhere
        }
    }

    function getGroupCount() {
        $groups = $this->getGroups(0, null);
        return $groups instanceof User_group
                ? $groups->N
                : 0;
    }

    function isTagged($peopletag)
    {
        $tag = Profile_tag::pkeyGet(array('tagger' => $peopletag->tagger,
                                          'tagged' => $this->id,
                                          'tag'    => $peopletag->tag));
        return !empty($tag);
    }

    function canTag($tagged)
    {
        if (empty($tagged)) {
            return false;
        }

        if ($tagged->id == $this->id) {
            return true;
        }

        $all = common_config('peopletag', 'allow_tagging', 'all');
        $local = common_config('peopletag', 'allow_tagging', 'local');
        $remote = common_config('peopletag', 'allow_tagging', 'remote');
        $subs = common_config('peopletag', 'allow_tagging', 'subs');

        if ($all) {
            return true;
        }

        $tagged_user = $tagged->getUser();
        if (!empty($tagged_user)) {
            if ($local) {
                return true;
            }
        } else if ($subs) {
            return (Subscription::exists($this, $tagged) ||
                    Subscription::exists($tagged, $this));
        } else if ($remote) {
            return true;
        }
        return false;
    }

    function getLists(Profile $scoped=null, $offset=0, $limit=null, $since_id=0, $max_id=0)
    {
        $ids = array();

        $keypart = sprintf('profile:lists:%d', $this->id);

        $idstr = self::cacheGet($keypart);

        if ($idstr !== false) {
            $ids = explode(',', $idstr);
        } else {
            $list = new Profile_list();
            $list->selectAdd();
            $list->selectAdd('id');
            $list->tagger = $this->id;
            $list->selectAdd('id as "cursor"');

            if ($since_id>0) {
               $list->whereAdd('id > '.$since_id);
            }

            if ($max_id>0) {
                $list->whereAdd('id <= '.$max_id);
            }

            if($offset>=0 && !is_null($limit)) {
                $list->limit($offset, $limit);
            }

            $list->orderBy('id DESC');

            if ($list->find()) {
                while ($list->fetch()) {
                    $ids[] = $list->id;
                }
            }

            self::cacheSet($keypart, implode(',', $ids));
        }

        $showPrivate = $this->sameAs($scoped);

        $lists = array();

        foreach ($ids as $id) {
            $list = Profile_list::getKV('id', $id);
            if (!empty($list) &&
                ($showPrivate || !$list->private)) {

                if (!isset($list->cursor)) {
                    $list->cursor = $list->id;
                }

                $lists[] = $list;
            }
        }

        return new ArrayWrapper($lists);
    }

    /**
     * Get tags that other people put on this profile, in reverse-chron order
     *
     * @param Profile        $scoped     User we are requesting as
     * @param int            $offset     Offset from latest
     * @param int            $limit      Max number to get
     * @param datetime       $since_id   max date
     * @param datetime       $max_id     min date
     *
     * @return Profile_list resulting lists
     */

    function getOtherTags(Profile $scoped=null, $offset=0, $limit=null, $since_id=0, $max_id=0)
    {
        $list = new Profile_list();

        $qry = sprintf('select profile_list.*, unix_timestamp(profile_tag.modified) as "cursor" ' .
                       'from profile_tag join profile_list '.
                       'on (profile_tag.tagger = profile_list.tagger ' .
                       '    and profile_tag.tag = profile_list.tag) ' .
                       'where profile_tag.tagged = %d ',
                       $this->id);


        if (!is_null($scoped)) {
            $qry .= sprintf('AND ( ( profile_list.private = false ) ' .
                            'OR ( profile_list.tagger = %d AND ' .
                            'profile_list.private = true ) )',
                            $scoped->getID());
        } else {
            $qry .= 'AND profile_list.private = 0 ';
        }

        if ($since_id > 0) {
            $qry .= sprintf('AND (cursor > %d) ', $since_id);
        }

        if ($max_id > 0) {
            $qry .= sprintf('AND (cursor < %d) ', $max_id);
        }

        $qry .= 'ORDER BY profile_tag.modified DESC ';

        if ($offset >= 0 && !is_null($limit)) {
            $qry .= sprintf('LIMIT %d OFFSET %d ', $limit, $offset);
        }

        $list->query($qry);
        return $list;
    }

    function getPrivateTags($offset=0, $limit=null, $since_id=0, $max_id=0)
    {
        $tags = new Profile_list();
        $tags->private = true;
        $tags->tagger = $this->id;

        if ($since_id>0) {
           $tags->whereAdd('id > '.$since_id);
        }

        if ($max_id>0) {
            $tags->whereAdd('id <= '.$max_id);
        }

        if($offset>=0 && !is_null($limit)) {
            $tags->limit($offset, $limit);
        }

        $tags->orderBy('id DESC');
        $tags->find();

        return $tags;
    }

    function hasLocalTags()
    {
        $tags = new Profile_tag();

        $tags->joinAdd(array('tagger', 'user:id'));
        $tags->whereAdd('tagged  = '.$this->id);
        $tags->whereAdd('tagger != '.$this->id);

        $tags->limit(0, 1);
        $tags->fetch();

        return ($tags->N == 0) ? false : true;
    }


   // -------------------------------------------------------------------------
   // Function: getTagSubscriptions
   // Returns a Profile_list object containing all the tags this profile has
   // subscribed to.
   function getTagSubscriptions($offset=0, $limit=null, $since_id=0, $max_id=0) {
      $lists = new Profile_list();
      $subs = new Profile_tag_subscription();
      $lists->joinAdd(array('id', 'profile_tag_subscription:profile_tag_id'));
      #@fixme: postgres (round(date_part('epoch', my_date)))
      $lists->selectAdd('unix_timestamp(profile_tag_subscription.created) as "cursor"');
      $lists->whereAdd('profile_tag_subscription.profile_id = '.$this->id);
      if ($since_id>0) {
         $lists->whereAdd('cursor > '.$since_id);
      }
      if ($max_id>0) {
         $lists->whereAdd('cursor <= '.$max_id);
      }
      if($offset>=0 && !is_null($limit)) {
         $lists->limit($offset, $limit);
      }
      $lists->orderBy('"cursor" DESC');
      $lists->find();
      return $lists;
   }


   // -------------------------------------------------------------------------
   // Function: joinGroup
   // Adds this profile to a user group $group, or, if it has an approval
   // requirement, add it to the approvals queue for the group.
   //
   // Returns:
   // o Group_member on success
   // o Group_join_queue if pending approval
   // o null on some cancels?
   function joinGroup(User_group $group) {
      $join = null;
      if ($group->join_policy == User_group::JOIN_POLICY_MODERATE) {
         $join = Group_join_queue::saveNew($this, $group);
      } else {
         if (Event::handle('StartJoinGroup', array($group, $this))) {
            $join = Group_member::join($group->id, $this->id);
            self::blow('profile:groups:%d', $this->id);
            self::blow('group:member_ids:%d', $group->id);
            self::blow('group:member_count:%d', $group->id);
            Event::handle('EndJoinGroup', array($group, $this));
         }
      }
      if ($join) {
         // Send any applicable notifications...
         $join->notify();
      }
      return $join;
    }


   // -------------------------------------------------------------------------
   // Function: leaveGroup
   // Leave a user group $group that this profile is a member of.
   function leaveGroup(User_group $group) {
      if (Event::handle('StartLeaveGroup', array($group, $this))) {
         Group_member::leave($group->id, $this->id);
         self::blow('profile:groups:%d', $this->id);
         self::blow('group:member_ids:%d', $group->id);
         self::blow('group:member_count:%d', $group->id);
         Event::handle('EndLeaveGroup', array($group, $this));
      }
   }


   // -------------------------------------------------------------------------
   // Function: avatarUrl
   // Returns the URL of the avatar associated with this profile, or the URL of
   // of the default avatar if one is not set.
   function avatarUrl($size=AVATAR_PROFILE_SIZE) {
      return Avatar::urlByProfile($this, $size);
   }


   // -------------------------------------------------------------------------
   // Function: getSubscribed
   // Returns an array of profile objects containing the profiles that this
   // profile subscribes to.
   function getSubscribed($offset=0, $limit=null) {
      $subs = Subscription::getSubscribedIDs($this->id, $offset, $limit);
      try {
         $profiles = Profile::multiGet('id', $subs);
      } catch (NoResultException $e) {
         return $e->obj;
      }
      return $profiles;
   }


   // -------------------------------------------------------------------------
   // Function: getSubscribers
   // Returns an array of profile objects containing the subscribers to 
   // this profile.
   function getSubscribers($offset=0, $limit=null) {
      $subs = Subscription::getSubscriberIDs($this->id, $offset, $limit);
      try {
         $profiles = Profile::multiGet('id', $subs);
      } catch (NoResultException $e) {
         return $e->obj;
      }
      return $profiles;
   }


   // -------------------------------------------------------------------------
   // Function: getTaggedSubscribers
   // Returns a profile object containing subscribers associated with this
   // profile containing a certain tag $tag.
   function getTaggedSubscribers($tag, $offset=0, $limit=null) {
      $qry =
          'SELECT profile.* ' .
          'FROM profile JOIN subscription ' .
          'ON profile.id = subscription.subscriber ' .
          'JOIN profile_tag ON (profile_tag.tagged = subscription.subscriber ' .
          'AND profile_tag.tagger = subscription.subscribed) ' .
          'WHERE subscription.subscribed = %d ' .
          "AND profile_tag.tag = '%s' " .
          'AND subscription.subscribed != subscription.subscriber ' .
          'ORDER BY subscription.created DESC ';

      if ($offset) {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
      }

      $profile = new Profile();
      $cnt = $profile->query(sprintf($qry, $this->id, $profile->escape($tag)));
      return $profile;
   }

   // -------------------------------------------------------------------------
   // Function: getTaggedSubscriptions
   // Returns a profile object containing subscriptions associated with this
   // profile containing a certain tag $tag.
   function getTaggedSubscriptions($tag, $offset=0, $limit=null) {
      $qry =
          'SELECT profile.* ' .
          'FROM profile JOIN subscription ' .
          'ON profile.id = subscription.subscribed ' .
          'JOIN profile_tag on (profile_tag.tagged = subscription.subscribed ' .
          'AND profile_tag.tagger = subscription.subscriber) ' .
          'WHERE subscription.subscriber = %d ' .
          "AND profile_tag.tag = '%s' " .
          'AND subscription.subscribed != subscription.subscriber ' .
          'ORDER BY subscription.created DESC ';

      $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;

      $profile = new Profile();
      $profile->query(sprintf($qry, $this->id, $profile->escape($tag)));
      return $profile;
   }


   // -------------------------------------------------------------------------
   // Function: getRequests
   // Returns an profile containing pending subscribers, who have not yet 
   // been approved.
   //
   // FIXME: mysql only
   //
   // Parameters:
   // o int $offset
   // o int $limit
   function getRequests($offset=0, $limit=null) {
      $subqueue = new Profile();
      $subqueue->joinAdd(array('id', 'subscription_queue:subscriber'));
      $subqueue->whereAdd(sprintf('subscription_queue.subscribed = %d', $this->getID()));
      $subqueue->limit($offset, $limit);
      $subqueue->orderBy('subscription_queue.created', 'DESC');
      if (!$subqueue->find()) {
         throw new NoResultException($subqueue);
      }
      return $subqueue;
   }


   // ------------------------------------------------------------------------
   // Function: subscriptionCount
   // Returns the number of subscriptions that are associated with this
   // profile.  This will hit the cache if it exists.
   function subscriptionCount() {
      $c = Cache::instance();
      if (!empty($c)) {
         $cnt = $c->get(Cache::key('profile:subscription_count:'.$this->id));
         if (is_integer($cnt)) {
            return (int) $cnt;
         }
      }

      $sub = new Subscription();
      $sub->subscriber = $this->id;
      $cnt = (int) $sub->count('distinct subscribed');
      $cnt = ($cnt > 0) ? $cnt - 1 : $cnt;

      if (!empty($c)) {
         $c->set(Cache::key('profile:subscription_count:'.$this->id), $cnt);
      }

      return $cnt;
    }


   // -------------------------------------------------------------------------
   // Function: subscriberCount
   // Returns the number of subscribers that are associated with this profile.  
   // This will hit the cache if it exists.
   function subscriberCount() {
      $c = Cache::instance();
      if (!empty($c)) {
         $cnt = $c->get(Cache::key('profile:subscriber_count:'.$this->id));
         if (is_integer($cnt)) {
            return (int) $cnt;
         }
      }
      $sub = new Subscription();
      $sub->subscribed = $this->id;
      $sub->whereAdd('subscriber != subscribed');
      $cnt = (int) $sub->count('distinct subscriber');
      if (!empty($c)) {
         $c->set(Cache::key('profile:subscriber_count:'.$this->id), $cnt);
      }
      return $cnt;
   }


   // -------------------------------------------------------------------------
   // Function: isSubscribed
   // Returns true/false whether this profile is subscribed to profile $other.
   function isSubscribed(Profile $other) {
      return Subscription::exists($this, $other);
   }


   // -------------------------------------------------------------------------
   // Function: readableBy
   // Returns true/false whether this profile's stream is public.  If you
   // specify a profile $other, it will return true/false whether the user
   // represented by that profile can view the profile, if it is private.
   function readableBy(Profile $other=null) {
      // If it's not a private stream, it's readable by anyone
      if (!$this->isPrivateStream()) {
         return true;
      }
      // If it's a private stream, $other must be a subscriber to $this
      return is_null($other) ? false : $other->isSubscribed($this);
   }


   // -------------------------------------------------------------------------
   // Function: requiresSubscriptionApproval
   // Returns whether the profile $other requires approval to subscribe to this
   // user
   function requiresSubscriptionApproval(Profile $other=null) {
      if (!$this->isLocal()) {
         // We don't know for remote users, and we'll always be able to send
         // the request. Whether it'll work immediately or require moderation
         // can be determined in another function.
         return false;
      }

      // Assume that profiles _we_ subscribe to are permitted. Could be made configurable.
      if (!is_null($other) && $this->isSubscribed($other)) {
         return false;
      }

      // If the local user either has a private stream (implies the following)
      // or  user has a moderation policy for new subscriptions, return true.
      return $this->getUser()->private_stream || 
         $this->getUser()->subscribe_policy === User::SUBSCRIBE_POLICY_MODERATE;
   }


   // -------------------------------------------------------------------------
   // Function: hasPendingSubscription
   // Returns true/false if a pending subscription request is outstanding for
   // profile $other.
   function hasPendingSubscription(Profile $other) {
      return Subscription_queue::exists($this, $other);
   }


   // -------------------------------------------------------------------------
   // Function: mutuallySubscribed
   // Returns true/false whether the current profile and the profile $other
   // both subscribe to one another.
   function mutuallySubscribed(Profile $other) {
      return $this->isSubscribed($other) && $other->isSubscribed($this);
   }


   // -------------------------------------------------------------------------
   // Function: noticeCount
   // Returns the number of notices associated with being posted by this
   // profile.  It will not include anything other than posts (ie not shares/
   // events/etc)
   function noticeCount() {
        $c = Cache::instance();
        if (!empty($c)) {
            $cnt = $c->get(Cache::key('profile:notice_count:'.$this->getID()));
            if (is_integer($cnt)) {
                return (int) $cnt;
            }
        }

        $notices = new Notice();
        $notices->profile_id = $this->getID();
        $notices->verb = ActivityVerb::POST;
        $cnt = (int) $notices->count('id'); // Not sure if I imagine this, but 'id' was faster than the defaulting 'uri'?
        if (!empty($c)) {
            $c->set(Cache::key('profile:notice_count:'.$this->getID()), $cnt);
        }

        return $cnt;
    }

   // -------------------------------------------------------------------------
   // Function: blowSubscriberCount
   // Refresh the subscribers count in the cache for this profile.
   function blowSubscriberCount() {
      $c = Cache::instance();
      if (!empty($c)) {
         $c->delete(Cache::key('profile:subscriber_count:'.$this->id));
      }
   }


   // -------------------------------------------------------------------------
   // Function: blowSucriptionCount
   // Refresh the subscriptions count in the cache for this profile.
   function blowSubscriptionCount() {
      $c = Cache::instance();
      if (!empty($c)) {
         $c->delete(Cache::key('profile:subscription_count:'.$this->id));
      }
   }


   // -------------------------------------------------------------------------
   // Function: blowNoticeCount
   // Refresh the notice count in the cache for this profile.
   function blowNoticeCount() {
      $c = Cache::instance();
      if (!empty($c)) {
         $c->delete(Cache::key('profile:notice_count:'.$this->id));
      }
   }


   // -------------------------------------------------------------------------
   // Function: maxBio
   // Returns the current maximum bio length according to the site settings.
   static function maxBio() {
      $biolimit = common_config('profile', 'biolimit');
      // null => use global limit (distinct from 0!)
      if (is_null($biolimit)) {
         $biolimit = common_config('site', 'textlimit');
      }
      return $biolimit;
   }


   // -------------------------------------------------------------------------
   // Function: bioTooLong
   // Returns whether a proposed profile bio $bio is too long according to
   // site settings
   static function bioTooLong($bio) {
      $biolimit = self::maxBio();
      return ($biolimit > 0 && !empty($bio) && (mb_strlen($bio) > $biolimit));
   }


   // -------------------------------------------------------------------------
   // Function: update
   // A slightly misleadingly-named function that changes the profile's nickname
   // in the database when it's been changed in the backend (ie, user settings)
   function update($dataObject=false) {
      if (is_object($dataObject) && $this->nickname != $dataObject->nickname) {
         try {
            $local = $this->getUser();
            common_debug("Updating User ({$this->id}) nickname from {$dataObject->nickname} to {$this->nickname}");
            $origuser = clone($local);
            $local->nickname = $this->nickname;
            // updateWithKeys throws exception on failure.
            $local->updateWithKeys($origuser);

            // Clear the site owner, in case nickname changed
            if ($local->hasRole(Profile_role::OWNER)) {
               User::blow('user:site_owner');
            }
         } catch (NoSuchUserException $e) {
            // Nevermind...
         }
      }
      return parent::update($dataObject);
   }


   // ------------------------------------------------------------------------
   // Function: getRelSelf
   // Returns an associative array with simple profile hints
   public function getRelSelf() {
      return ['href' => $this->getUrl(),
              'text' => common_config('site', 'name'),
              'image' => Avatar::urlByProfile($this)];
   }


   // -------------------------------------------------------------------------
   // Function: getRelMes
   // Returns all the known rel="me", used for the IndieWeb audience
   public function getRelMes() {
      $relMes = array();
      try {
         $relMes[] = $this->getRelSelf();
      } catch (InvalidUrlException $e) {
         // no valid profile URL available
      }
      if (common_valid_http_url($this->getHomepage())) {
         $relMes[] = ['href' => $this->getHomepage(),
                      'text' => _('Homepage'),
                      'image' => null];
      }
      Event::handle('OtherAccountProfiles', array($this, &$relMes));
      return $relMes;
   }


   // ------------------------------------------------------------------------
   // Function: delete
   // Deletes a profile and all of the associated notices, subs, tages, etc.
   function delete($useWhere=false) {
      $this->_deleteNotices();
      $this->_deleteSubscriptions();
      $this->_deleteTags();
      $this->_deleteBlocks();
      $this->_deleteAttentions();
      Avatar::deleteFromProfile($this, true);

      // Warning: delete() will run on the batch objects,
      // not on individual objects.
      $related = array('Reply', 'Group_member', 'Profile_role');
      Event::handle('ProfileDeleteRelated', array($this, &$related));
      foreach ($related as $cls) {
         $inst = new $cls();
         $inst->profile_id = $this->id;
         $inst->delete();
      }

      $this->grantRole(Profile_role::DELETED);
      $localuser = User::getKV('id', $this->id);
      if ($localuser instanceof User) {
         $localuser->delete();
      }
      return parent::delete($useWhere);
   }


   // ------------------------------------------------------------------------
   // Function: _deleteNotices
   // Helper function to delete all of the notices associated with this profile.
   function _deleteNotices() {
      $notice = new Notice();
      $notice->profile_id = $this->id;
      if ($notice->find()) {
         while ($notice->fetch()) {
                $other = clone($notice);
                $other->delete();
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: _deleteSubscriptions
   // Helper function to delete all of the subscriptions associated with this
   // profile.
   function _deleteSubscriptions() {
      $sub = new Subscription();
      $sub->subscriber = $this->getID();
      $sub->find();
      while ($sub->fetch()) {
         try {
            $other = $sub->getSubscribed();
            if (!$other->sameAs($this)) {
               Subscription::cancel($this, $other);
            }
         } catch (NoResultException $e) {
            // Profile not found
            common_log(LOG_INFO, 'Subscribed profile id=='.$sub->subscribed.' not found when deleting profile id=='.$this->getID().', ignoring...');
         } catch (ServerException $e) {
            // Subscription cancel failed
            common_log(LOG_INFO, 'Subscribed profile id=='.$other->getID().' could not be reached for unsubscription notice when deleting profile id=='.$this->getID().', ignoring...');
         }
      }

      $sub = new Subscription();
      $sub->subscribed = $this->getID();
      $sub->find();
      while ($sub->fetch()) {
         try {
            $other = $sub->getSubscriber();
            common_log(LOG_INFO, 'Subscriber profile id=='.$sub->subscribed.' not found when deleting profile id=='.$this->getID().', ignoring...');
            if (!$other->sameAs($this)) {
               Subscription::cancel($other, $this);
            }
         } catch (NoResultException $e) {
            // Profile not found
            common_log(LOG_INFO, 'Subscribed profile id=='.$sub->subscribed.' not found when deleting profile id=='.$this->getID().', ignoring...');
         } catch (ServerException $e) {
            // Subscription cancel failed
            common_log(LOG_INFO, 'Subscriber profile id=='.$other->getID().' could not be reached for unsubscription notice when deleting profile id=='.$this->getID().', ignoring...');
         }
      }

      // Finally delete self-subscription
      $self = new Subscription();
      $self->subscriber = $this->getID();
      $self->subscribed = $this->getID();
      $self->delete();
   }


   // -------------------------------------------------------------------------
   // Function: _deleteTags
   // Helper function to delete all the tags associated with a profile.
   function _deleteTags() {
      $tag = new Profile_tag();
      $tag->tagged = $this->id;
      $tag->delete();
   }


   // -------------------------------------------------------------------------
   // Function: _deleteBlocks
   // Helper function to delete all the blocks associated with a profile.
   function _deleteBlocks() {
      $block = new Profile_block();
      $block->blocked = $this->id;
      $block->delete();
      $block = new Group_block();
      $block->blocked = $this->id;
      $block->delete();
   }


   // -------------------------------------------------------------------------
   // Function: _deleteAttentions
   // Helper function to delete all of the notifications associated with a 
   // profile.
   function _deleteAttentions() {
      $att = new Attention();
      $att->profile_id = $this->getID();
      if ($att->find()) {
         while ($att->fetch()) {
            // Can't do delete() on the object directly since it won't remove all of it
            $other = clone($att);
            $other->delete();
         }
      }
   }


   // -------------------------------------------------------------------------
   // Function: getLocation
   // Returns the location associated with a profile.
   public function getLocation() {
      $location = null;
      if (!empty($this->location_id) && !empty($this->location_ns)) {
         $location = Location::fromId($this->location_id, $this->location_ns);
      }
      if (is_null($location)) { // no ID, or Location::fromId() failed
         if (!empty($this->lat) && !empty($this->lon)) {
            $location = Location::fromLatLon($this->lat, $this->lon);
         }
      }
      if (is_null($location)) { // still haven't found it!
         if (!empty($this->location)) {
            $location = Location::fromName($this->location);
         }
      }
      return $location;
   }


   // -------------------------------------------------------------------------
   // Function: shareLocation
   // Returns whether the location should be shared according to user
   // and site settings, in the current context.
   public function shareLocation() {
      $cfg = common_config('location', 'share');
      if ($cfg == 'always') {
         return true;
      } else if ($cfg == 'never') {
         return false;
      } else { // user
         $share = common_config('location', 'sharedefault');
         // Check if user has a personal setting for this
         $prefs = User_location_prefs::getKV('user_id', $this->id);
         if (!empty($prefs)) {
            $share = $prefs->share_location;
            $prefs->free();
         }
      return $share;
      }
   }


   // ------------------------------------------------------------------------
   // Function: hasRole
   // Returns true/false whether the user represented by this profile has the
   // role $name
   function hasRole($name) {
      $has_role = false;
      if (Event::handle('StartHasRole', array($this, $name, &$has_role))) {
         $role = Profile_role::pkeyGet(array('profile_id' => $this->id,
                                             'role' => $name));
         $has_role = !empty($role);
         Event::handle('EndHasRole', array($this, $name, $has_role));
      }
      return $has_role;
   }


   // -------------------------------------------------------------------------
   // Fuunction: grantRole
   // Assign the role $name to the user represented by this profile.
   function grantRole($name) {
      if (Event::handle('StartGrantRole', array($this, $name))) {
         $role = new Profile_role();
         $role->profile_id = $this->id;
         $role->role       = $name;
         $role->created    = common_sql_now();
         $result = $role->insert();
         if (!$result) {
            throw new Exception("Can't save role '$name' for profile '{$this->id}'");
         }
         if ($name == 'owner') {
            User::blow('user:site_owner');
         }
         Event::handle('EndGrantRole', array($this, $name));
      }
      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: revokeRole
   // Remove the user right $name from the user associated with this profile.
   function revokeRole($name) {
      if (Event::handle('StartRevokeRole', array($this, $name))) {
         $role = Profile_role::pkeyGet(array('profile_id' => $this->id, 'role' => $name));
         if (empty($role)) {
            // TRANS: Exception thrown when trying to revoke an existing role for a user that does not exist.
            // TRANS: %1$s is the role name, %2$s is the user ID (number).
            throw new Exception(sprintf(_('Cannot revoke role "%1$s" for user #%2$d; does not exist.'),$name, $this->id));
         }
         $result = $role->delete();
         if (!$result) {
            common_log_db_error($role, 'DELETE', __FILE__);
            // TRANS: Exception thrown when trying to revoke a role for a user with a failing database query.
            // TRANS: %1$s is the role name, %2$s is the user ID (number).
            throw new Exception(sprintf(_('Cannot revoke role "%1$s" for user #%2$d; database error.'),$name, $this->id));
         }
         if ($name == 'owner') {
            User::blow('user:site_owner');
         }
         Event::handle('EndRevokeRole', array($this, $name));
         return true;
      }
   }


   // -------------------------------------------------------------------------
   // Function: isSandboxed
   // Returns true/false whether this user is Sandboxed.
   function isSandboxed() {
      return $this->hasRole(Profile_role::SANDBOXED);
   }


   // -------------------------------------------------------------------------
   // Function: isSilenced
   // Returns true/false whether this user is Silenced (banned).
   function isSilenced() {
       return $this->hasRole(Profile_role::SILENCED);
   }


   // -------------------------------------------------------------------------
   // Function: sandbox
   // Sandbox the user represented by this profile.
   function sandbox() {
      $this->grantRole(Profile_role::SANDBOXED);
   }


   // ------------------------------------------------------------------------
   // Function: unsandbox
   // Remove sandboxing from the user represented by this profile.
   function unsandbox() {
      $this->revokeRole(Profile_role::SANDBOXED);
   }


   // -------------------------------------------------------------------------
   // Function: silence
   // Ban the user represented by this profile.
   function silence() {
      $this->grantRole(Profile_role::SILENCED);
      if (common_config('notice', 'hidespam')) {
         $this->flushVisibility();
      }
   }


   // -------------------------------------------------------------------------
   // Function: silenceAs
   // An interface to silence() that does rights check first
   //
   // Parameters:
   // o actor - profile of the user we're checking rights for.
   function silenceAs(Profile $actor) {
      if (!$actor->hasRight(Right::SILENCEUSER)) {
         throw new AuthorizationException(_('You cannot silence users on this site.'));
      }
      // Only administrators can silence other privileged users (such as others who have the right to silence).
      if ($this->isPrivileged() && !$actor->hasRole(Profile_role::ADMINISTRATOR)) {
         throw new AuthorizationException(_('You cannot silence other privileged users.'));
      }
      if ($this->isSilenced()) {
         // TRANS: Client error displayed trying to silence an already silenced user.
         throw new AlreadyFulfilledException(_('User is already silenced.'));
      }
      return $this->silence();
   }


   // -------------------------------------------------------------------------
   // Function: unsilence
   // Remove a ban from the user represented by this profile.
   function unsilence() {
      $this->revokeRole(Profile_role::SILENCED);
      if (common_config('notice', 'hidespam')) {
         $this->flushVisibility();
      }
   }


   // -------------------------------------------------------------------------
   // Function: unsilenceAs
   // An interface to unsilence() that does rights check first.
   //
   // Parameters:
   // o actor - profile of the user we're checking rights for.
   function unsilenceAs(Profile $actor) {
      if (!$actor->hasRight(Right::SILENCEUSER)) {
         // TRANS: Client error displayed trying to unsilence a user when the user does not have the right.
         throw new AuthorizationException(_('You cannot unsilence users on this site.'));
      }
      if (!$this->isSilenced()) {
         // TRANS: Client error displayed trying to unsilence a user when the target user has not been silenced.
         throw new AlreadyFulfilledException(_('User is not silenced.'));
      }
      return $this->unsilence();
   }


    function flushVisibility()
    {
        // Get all notices
        $stream = new ProfileNoticeStream($this, $this);
        $ids = $stream->getNoticeIds(0, CachingNoticeStream::CACHE_WINDOW);
        foreach ($ids as $id) {
            self::blow('notice:in-scope-for:%d:null', $id);
        }
    }

    public function isPrivileged()
    {
        // TODO: An Event::handle so plugins can report if users are privileged.
        // The ModHelper is the only one I care about when coding this, and that
        // can be tested with Right::SILENCEUSER which I do below:
        switch (true) {
        case $this->hasRight(Right::SILENCEUSER):
        case $this->hasRole(Profile_role::MODERATOR):
        case $this->hasRole(Profile_role::ADMINISTRATOR):
        case $this->hasRole(Profile_role::OWNER):
            return true;
        }

        return false;
    }

    /**
     * Does this user have the right to do X?
     *
     * With our role-based authorization, this is merely a lookup for whether the user
     * has a particular role. The implementation currently uses a switch statement
     * to determine if the user has the pre-defined role to exercise the right. Future
     * implementations may allow per-site roles, and different mappings of roles to rights.
     *
     * @param $right string Name of the right, usually a constant in class Right
     * @return boolean whether the user has the right in question
     */
    public function hasRight($right)
    {
        $result = false;

        if ($this->hasRole(Profile_role::DELETED)) {
            return false;
        }

        if (Event::handle('UserRightsCheck', array($this, $right, &$result))) {
            switch ($right)
            {
            case Right::DELETEOTHERSNOTICE:
            case Right::MAKEGROUPADMIN:
            case Right::SANDBOXUSER:
            case Right::SILENCEUSER:
            case Right::DELETEUSER:
            case Right::DELETEGROUP:
            case Right::TRAINSPAM:
            case Right::REVIEWSPAM:
                $result = $this->hasRole(Profile_role::MODERATOR);
                break;
            case Right::CONFIGURESITE:
                $result = $this->hasRole(Profile_role::ADMINISTRATOR);
                break;
            case Right::GRANTROLE:
            case Right::REVOKEROLE:
                $result = $this->hasRole(Profile_role::OWNER);
                break;
            case Right::NEWNOTICE:
            case Right::NEWMESSAGE:
            case Right::SUBSCRIBE:
            case Right::CREATEGROUP:
                $result = !$this->isSilenced();
                break;
            case Right::PUBLICNOTICE:
            case Right::EMAILONREPLY:
            case Right::EMAILONSUBSCRIBE:
            case Right::EMAILONFAVE:
                $result = !$this->isSandboxed() && !$this->isSilenced();
                break;
            case Right::WEBLOGIN:
                $result = !$this->isSilenced();
                break;
            case Right::API:
                $result = !$this->isSilenced();
                break;
            case Right::BACKUPACCOUNT:
                $result = common_config('profile', 'backup');
                break;
            case Right::RESTOREACCOUNT:
                $result = common_config('profile', 'restore');
                break;
            case Right::DELETEACCOUNT:
                $result = common_config('profile', 'delete');
                break;
            case Right::MOVEACCOUNT:
                $result = common_config('profile', 'move');
                break;
            default:
                $result = false;
                break;
            }
        }
        return $result;
    }

    // FIXME: Can't put Notice typing here due to ArrayWrapper
    public function hasRepeated($notice)
    {
        // XXX: not really a pkey, but should work

        $notice = Notice::pkeyGet(array('profile_id' => $this->getID(),
                                        'repeat_of' => $notice->getID(),
                                        'verb' => ActivityVerb::SHARE));

        return !empty($notice);
    }

    /**
     * Returns an XML string fragment with limited profile information
     * as an Atom <author> element.
     *
     * Assumes that Atom has been previously set up as the base namespace.
     *
     * @param Profile $cur the current authenticated user
     *
     * @return string
     */
    function asAtomAuthor($cur = null)
    {
        $xs = new XMLStringer(true);

        $xs->elementStart('author');
        $xs->element('name', null, $this->nickname);
        $xs->element('uri', null, $this->getUri());
        if ($cur != null) {
            $attrs = Array();
            $attrs['following'] = $cur->isSubscribed($this) ? 'true' : 'false';
            $attrs['blocking']  = $cur->hasBlocked($this) ? 'true' : 'false';
            $xs->element('statusnet:profile_info', $attrs, null);
        }
        $xs->elementEnd('author');

        return $xs->getString();
    }

    /**
     * Extra profile info for atom entries
     *
     * Clients use some extra profile info in the atom stream.
     * This gives it to them.
     *
     * @param Profile $scoped The currently logged in/scoped profile
     *
     * @return array representation of <statusnet:profile_info> element or null
     */

    function profileInfo(Profile $scoped=null)
    {
        $profileInfoAttr = array('local_id' => $this->id);

        if ($scoped instanceof Profile) {
            // Whether the current user is a subscribed to this profile
            $profileInfoAttr['following'] = $scoped->isSubscribed($this) ? 'true' : 'false';
            // Whether the current user is has blocked this profile
            $profileInfoAttr['blocking']  = $scoped->hasBlocked($this) ? 'true' : 'false';
        }

        return array('statusnet:profile_info', $profileInfoAttr, null);
    }

    /**
     * Returns an XML string fragment with profile information as an
     * Activity Streams <activity:actor> element.
     *
     * Assumes that 'activity' namespace has been previously defined.
     *
     * @return string
     */
    function asActivityActor()
    {
        return $this->asActivityNoun('actor');
    }

    /**
     * Returns an XML string fragment with profile information as an
     * Activity Streams noun object with the given element type.
     *
     * Assumes that 'activity', 'georss', and 'poco' namespace has been
     * previously defined.
     *
     * @param string $element one of 'actor', 'subject', 'object', 'target'
     *
     * @return string
     */
    function asActivityNoun($element)
    {
        $noun = $this->asActivityObject();
        return $noun->asString('activity:' . $element);
    }

    public function asActivityObject()
    {
        $object = new ActivityObject();

        if (Event::handle('StartActivityObjectFromProfile', array($this, &$object))) {
            $object->type   = $this->getObjectType();
            $object->id     = $this->getUri();
            $object->title  = $this->getBestName();
            $object->link   = $this->getUrl();
            $object->summary = $this->getDescription();

            try {
                $avatar = Avatar::getUploaded($this);
                $object->avatarLinks[] = AvatarLink::fromAvatar($avatar);
            } catch (NoAvatarException $e) {
                // Could not find an original avatar to link
            }

            $sizes = array(
                AVATAR_PROFILE_SIZE,
                AVATAR_STREAM_SIZE,
                AVATAR_MINI_SIZE
            );

            foreach ($sizes as $size) {
                $alink  = null;
                try {
                    $avatar = Avatar::byProfile($this, $size);
                    $alink = AvatarLink::fromAvatar($avatar);
                } catch (NoAvatarException $e) {
                    $alink = new AvatarLink();
                    $alink->type   = 'image/png';
                    $alink->height = $size;
                    $alink->width  = $size;
                    $alink->url    = Avatar::defaultImage($size);
                }

                $object->avatarLinks[] = $alink;
            }

            if (isset($this->lat) && isset($this->lon)) {
                $object->geopoint = (float)$this->lat
                    . ' ' . (float)$this->lon;
            }

            $object->poco = PoCo::fromProfile($this);

            if ($this->isLocal()) {
                $object->extra[] = array('followers', array('url' => common_local_url('subscribers', array('nickname' => $this->getNickname()))));
            }

            Event::handle('EndActivityObjectFromProfile', array($this, &$object));
        }

        return $object;
    }

    /**
     * Returns the profile's canonical url, not necessarily a uri/unique id
     *
     * @return string $profileurl
     */
    public function getUrl()
    {
        $url = null;
        if ($this->isGroup()) {
            // FIXME: Get rid of this event, it fills no real purpose, data should be in Profile->profileurl (replaces User_group->mainpage)
            if (Event::handle('StartUserGroupHomeUrl', array($this->getGroup(), &$url))) {
                $url = $this->getGroup()->isLocal()
                        ? common_local_url('showgroup', array('nickname' => $this->getNickname()))
                        : $this->profileurl;
            }
            Event::handle('EndUserGroupHomeUrl', array($this->getGroup(), $url));
        } elseif ($this->isLocal()) {
            $url = common_local_url('showstream', array('nickname' => $this->getNickname()));
        } else {
            $url = $this->profileurl;
        }
        if (empty($url) ||
                !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException($url);
        }
        return $url;
    }
    public function getHtmlTitle()
    {
        try {
            return $this->getAcctUri(false);
        } catch (ProfileNoAcctUriException $e) {
            return $this->getNickname();
        }
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function getFullname()
    {
        return $this->fullname;
    }

    public function getHomepage()
    {
        return $this->homepage;
    }

    public function getDescription()
    {
        return $this->bio;
    }

    /**
     * Returns the best URI for a profile. Plugins may override.
     *
     * @return string $uri
     */
    public function getUri()
    {
        $uri = null;

        // give plugins a chance to set the URI
        if (Event::handle('StartGetProfileUri', array($this, &$uri))) {

            // check for a local user first
            $user = User::getKV('id', $this->id);
            if ($user instanceof User) {
                $uri = $user->getUri();
            } else {
                $group = User_group::getKV('profile_id', $this->id);
                if ($group instanceof User_group) {
                    $uri = $group->getUri();
                }
            }

            Event::handle('EndGetProfileUri', array($this, &$uri));
        }

        return $uri;
    }

    /**
     * Returns an assumed acct: URI for a profile. Plugins are required.
     *
     * @return string $uri
     */
    public function getAcctUri($scheme=true)
    {
        $acct = null;

        if (Event::handle('StartGetProfileAcctUri', array($this, &$acct))) {
            Event::handle('EndGetProfileAcctUri', array($this, &$acct));
        }

        if ($acct === null) {
            throw new ProfileNoAcctUriException($this);
        }
        if (parse_url($acct, PHP_URL_SCHEME) !== 'acct') {
            throw new ServerException('Acct URI does not have acct: scheme');
        }

        // if we don't return the scheme, just remove the 'acct:' in the beginning
        return $scheme ? $acct : mb_substr($acct, 5);
    }

    function hasBlocked(Profile $other)
    {
        $block = Profile_block::exists($this, $other);
        return !empty($block);
    }

    public function getAtomFeed()
    {
        $feed = null;

        if (Event::handle('StartProfileGetAtomFeed', array($this, &$feed))) {
            if ($this->isLocal()) {
                $feed = common_local_url('ApiTimelineUser', array('id' => $this->getID(),
                                                                  'format' => 'atom'));
            }
            Event::handle('EndProfileGetAtomFeed', array($this, $feed));
        }

        return $feed;
    }

    public function repeatedToMe($offset=0, $limit=20, $since_id=null, $max_id=null)
    {
        // TRANS: Exception thrown when trying view "repeated to me".
        throw new Exception(_('Not implemented since inbox change.'));
    }

    /*
     * Get a Profile object by URI. Will call external plugins for help
     * using the event StartGetProfileFromURI.
     *
     * @param string $uri A unique identifier for a resource (profile/group/whatever)
     */
    static function fromUri($uri)
    {
        $profile = null;

        if (Event::handle('StartGetProfileFromURI', array($uri, &$profile))) {
            // Get a local user when plugin lookup (like OStatus) fails
            $user = User::getKV('uri', $uri);
            if ($user instanceof User) {
                $profile = $user->getProfile();
            } else {
                $group = User_group::getKV('uri', $uri);
                if ($group instanceof User_group) {
                    $profile = $group->getProfile();
                }
            }
            Event::handle('EndGetProfileFromURI', array($uri, $profile));
        }

        if (!$profile instanceof Profile) {
            throw new UnknownUriException($uri);
        }

        return $profile;
    }

    function canRead(Notice $notice)
    {
        if ($notice->scope & Notice::SITE_SCOPE) {
            $user = $this->getUser();
            if (empty($user)) {
                return false;
            }
        }

        if ($notice->scope & Notice::ADDRESSEE_SCOPE) {
            $replies = $notice->getReplies();

            if (!in_array($this->id, $replies)) {
                $groups = $notice->getGroups();

                $foundOne = false;

                foreach ($groups as $group) {
                    if ($this->isMember($group)) {
                        $foundOne = true;
                        break;
                    }
                }

                if (!$foundOne) {
                    return false;
                }
            }
        }

        if ($notice->scope & Notice::FOLLOWER_SCOPE) {
            $author = $notice->getProfile();
            if (!Subscription::exists($this, $author)) {
                return false;
            }
        }

        return true;
    }


   // -------------------------------------------------------------------------
   // Function: current
   // Returns the current profile as an object, because we have a fetish for
   // returning mixed types.
   static function current() {
      $user = common_current_user();
      if (empty($user)) {
         $profile = null;
      } else {
         $profile = $user->getProfile();
      }
      return $profile;
   }


    static function ensureCurrent()
    {
        $profile = self::current();
        if (!$profile instanceof Profile) {
            throw new AuthorizationException('A currently scoped profile is required.');
        }
        return $profile;
    }


   // -------------------------------------------------------------------------
   // Function: getProfile
   // Returns this profile object.  Why something would want to call something 
   // like this, who knows.  Stop returning mixed types!
   public function getProfile() {
      return $this;
   }


   // -------------------------------------------------------------------------
   // Function: sameAs
   // Test whether the given profile is the same as the current class,
   // for testing identities.
   //
   // Parameters:
   // o Profile $other    The other profile, usually from Action's $this->scoped
   //
   // Returns:
   // o boolean
   public function sameAs(Profile $other=null) {
      if (is_null($other)) {
         // In case $this->scoped is null or something, i.e. not a current/legitimate profile.
         return false;
      }
      return $this->getID() === $other->getID();
   }


   // -------------------------------------------------------------------------
   // Function: shortenLinks
   // This will perform shortenLinks with the connected User object.
   //
   // Won't work on remote profiles or groups, so expect a NoSuchUserException
   // if you don't know it's a local User.
   //
   // Parameters:
   // o string $text    - String to shorten
   // o boolean $always - Disrespect minimum length etc.
   //
   // Returns:
   // o string link-shortened $text
   public function shortenLinks($text, $always=false) {
      return $this->getUser()->shortenLinks($text, $always);
   }


   // -------------------------------------------------------------------------
   // Function: isPrivateStream
   // Returns true/false whether the user's stream is set to private.
   public function isPrivateStream() {
      // We only know of public remote users as of yet...
      if (!$this->isLocal()) {
         return false;
      }
      return $this->getUser()->private_stream ? true : false;
   }


   // -------------------------------------------------------------------------
   // Function: delPref
   // Delete an extended preference of the profile.
   public function delPref($namespace, $topic) {
      return Profile_prefs::setData($this, $namespace, $topic, null);
   }


   // -------------------------------------------------------------------------
   // Function: getPref
   // Retrieve an extended preference of the profile.
   //
   // If you want an exception to be thrown on an error, call 
   // Profile_prefs::getData directly.
   public function getPref($namespace, $topic, $default=null) {
      try {
         return Profile_prefs::getData($this, $namespace, $topic, $default);
      } catch (NoResultException $e) {
         return null;
      }
   }

   // -------------------------------------------------------------------------
   // Function: getConfigPref
   // The same as getPref but will fall back to common_config value for the 
   // same namespace/topic.
   public function getConfigPref($namespace, $topic) {
      return Profile_prefs::getConfigData($this, $namespace, $topic);
   }


   // -------------------------------------------------------------------------
   // Function: setPref
   // Set an extended preference of the user.
   public function setPref($namespace, $topic, $data) {
      return Profile_prefs::setData($this, $namespace, $topic, $data);
   }


   // -------------------------------------------------------------------------
   // Function: getConnectedApps
   // Returns an array containing representations of the OAuth apps associated
   // with the user represented by this profile.
   public function getConnectedApps($offset=0, $limit=null) {
      return $this->getUser()->getConnectedApps($offset, $limit);
   }


   // -------------------------------------------------------------------------
   // Function: getGpgPubKey
   // Returns the GPG public key associated with the profile, if one exists.
   public function getGpgPubKey() {
      return $this->gpgpubkey;
   }


   // -------------------------------------------------------------------------
   // Function: getXmpp
   // Returns the XMPP account associated with the profile, if one exists.
   public function getXmpp() {
      return $this->xmpp;
   }


   // -------------------------------------------------------------------------
   // Function: getToxId
   // Returns the TOX ID set for the profile, if one exists.
   public function getToxId() {
      return $this->toxid;
   }


   // -------------------------------------------------------------------------
   // Function: getMatrix
   // Returns the Matrix ID set for the profile, if one exists.
   public function getMatrix() {
      return $this->matrix;
   }


   // -------------------------------------------------------------------------
   // Function: getDonateUrl
   // Returns the donation URL set for the profile, if one exists
   public function getDonateUrl() {
      return $this->donateurl;
   }
}

// END OF FILE
// ============================================================================
?>