<?php
/* ============================================================================
 * Title: Inbox Notice Stream
 * Stream of notices for a profile's "all" feed
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
 * ----------------------------------------------------------------------------
 * About:
 * A specific user's "all" feed.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou <evan@status.net>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Alexi Sorokin <sor.alexei@meowr.ru>
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

// ============================================================================
// Stream of notices for a profile's "all" feed
class InboxNoticeStream extends ScopingNoticeStream {
   // -------------------------------------------------------------------------
   // Function: __construct
   // Class object constructor
   //
   // Parameters:
   // o Profile $target - Profile to get a stream for
   // o Profile $scoped - Currently scoped profile (if null, it is fetched)
   //
   // FIXME:
   // o we don't use CachingNoticeStream - but maybe we should?
   function __construct(Profile $target, Profile $scoped=null) {
      parent::__construct(new CachingNoticeStream(new RawInboxNoticeStream($target), 'profileall'), $scoped);
   }
}

// ============================================================================
// Class: RawInboxNoticeStream
// Raw stream of notices for the target's inbox
class RawInboxNoticeStream extends FullNoticeStream {
   protected $target = null;
   protected $inbox  = null;

   // -------------------------------------------------------------------------
   // Function: __construct
   // Class object constructor
   //
   // Parameters:
   // o Profile $target Profile to get a stream for
   function __construct(Profile $target) {
      parent::__construct();
      $this->target  = $target;
   }

   // -------------------------------------------------------------------------
   // Function: getNoticeIds
   // Get IDs in a range
   //
   // Parameters:
   // o int $offset   - Offset from start
   // o int $limit    - Limit of number to get
   // o int $since_id - Since this notice
   // o int $max_id   - Before this notice
   //
   // Returns:
   // o Array IDs found
   function getNoticeIds($offset, $limit, $since_id, $max_id) {
      $notice_ids = array();

      // Subscription:: is a table of subscriptions (every user is subscribed to themselves)
      $subscription = new Subscription();
      $subscription->selectAdd();
      $subscription->selectAdd('subscribed');
      $subscription->whereAdd(sprintf('subscriber = %1$d', $this->target->id));
      $subscription_profile_ids = $subscription->fetchAll('subscribed');

      // Reply:: is a table of mentions
      $reply = new Reply();
      $reply->selectAdd();
      $reply->selectAdd('notice_id');
      $reply->whereAdd(sprintf('profile_id = %1$d', $this->target->id));
      $notice_ids += $reply->fetchAll('notice_id');

      $attention = new Attention();
      $attention->selectAdd();
      $attention->selectAdd('notice_id');
      $attention->whereAdd(sprintf('profile_id = %1$d', $this->target->id));
      $notice_ids += $attention->fetchAll('notice_id');

      $group_inbox = new Group_inbox();
      $group_inbox->selectAdd();
      $group_inbox->selectAdd('notice_id');
      $group_inbox->whereAdd(
         sprintf('group_id IN (SELECT group_id FROM group_member WHERE profile_id = %1$d)',
            $this->target->id));
      $notice_ids += $group_inbox->fetchAll('notice_id');

      $query_ids = '';
      
      if (!empty($notice_ids)) {
         $query_ids .= 'notice.id IN (' . implode(', ', $notice_ids) . ') OR ';
      }
      $query_ids .= 'notice.profile_id IN (' . implode(', ', $subscription_profile_ids) . ')';

      $notice = new Notice();
      $notice->selectAdd();
      $notice->selectAdd('id');
      $notice->whereAdd(sprintf('notice.created > "%s"', $notice->escape($this->target->created)));
      
      if (!is_null($query_ids)) {
         $notice->whereAdd($query_ids);
      }
      if (!empty($since_id)) {
         $notice->whereAdd(sprintf('notice.id > %d', $since_id));
      }
      if (!empty($max_id)) {
         $notice->whereAdd(sprintf('notice.id <= %d', $max_id));
      }

      self::filterVerbs($notice, $this->selectVerbs);

      $notice->limit($offset, $limit);
      // notice.id will give us even really old posts, which were
      // recently imported. For example if a remote instance had
      // problems and just managed to post here. Another solution
      // would be to have a 'notice.imported' field and order by it.
      $notice->orderBy('notice.id DESC');

      if (!$notice->find()) {
         return array();
      }

      $ids = $notice->fetchAll('id');

      return $ids;
   }
}

// END OF FILE
// ============================================================================
?>