<?php

/* ============================================================================
 * Title: User
 * Superclass for conversations as they are stored in the DB
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
 * Superclass for conversations as they are stored in the DB, with associated
 * interfaces.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
- * o Zach Copley
- * o Brion Vibber <brion@pobox.com>
- * o Siebrand Mazeland <s.mazeland@xs4all.nl>
- * o Evan Prodromou
- * o Mikael Nordfeldth <mmn@hethane.se>
- * o Roland Haeder <roland@mxchange.org>
- * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
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
// Superclass representing a stored conversation which we use as the meta 
// method for organizing notice replies.
//
// Properties:
// o __table = 'conversation' - table name
// o id       - int(4)  primary_key not_null auto_increment
// o uri      - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o url      - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o created  - datetime   not_null
// o modified - timestamp   not_null default_CURRENT_TIMESTAMP
class Conversation extends Managed_DataObject {
   public $__table = 'conversation';
   public $id;
   public $uri;
   public $url;
   public $created;
   public $modified;


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the schema that describes the conversation
   // in the backend database.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'serial', 'not null' => true, 'description' => 'Unique identifier, (again) unrelated to notice id since 2016-01-06'),
            'uri' => array('type' => 'varchar', 'not null'=>true, 'length' => 191, 'description' => 'URI of the conversation'),
            'url' => array('type' => 'varchar', 'length' => 191, 'description' => 'Resolvable URL, preferrably remote (local can be generated on the fly)'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('id'),
         'unique keys' => array('conversation_uri_key' => array('uri'),),);
   }


   // -------------------------------------------------------------------------
   // Function: beforeSchemaUpdate
   // Integrity checks before we
   static public function beforeSchemaUpdate() {
      $table = strtolower(get_called_class());
      $schema = Schema::get();
      $schemadef = $schema->getTableDef($table);

      // 2016-01-06 We have to make sure there is no conversation with id==0 since it will screw up auto increment resequencing
      if ($schemadef['fields']['id']['auto_increment']) {
         // since we already have auto incrementing ('serial') we can continue
         return;
      }

      // The conversation will be recreated in upgrade.php, which will
      // generate a new URI, but that's collateral damage for you.
      $conv = new Conversation();
      $conv->id = 0;
      if ($conv->find()) {
         while ($conv->fetch()) {
            // Since we have filtered on 0 this only deletes such entries
            // which I have been afraid wouldn't work, but apparently does!
            // (I thought it would act as null or something and find _all_ conversation entries)
            $conv->delete();
         }
      }
    }


   // -------------------------------------------------------------------------
   // Function: create
   // Factory method for creating a new conversation.
   //
   // Use this for locally initiated conversations. Remote notices should
   // preferrably supply their own conversation URIs in the OStatus feed.
   //
   // Returns:
   // o the created Conversation object
   static function create(ActivityContext $ctx=null, $created=null) {
      // Be aware that the Notice does not have an id yet since it's not inserted!
      $conv = new Conversation();
      $conv->created = $created ?: common_sql_now();
      if ($ctx instanceof ActivityContext) {
            $conv->uri = $ctx->conversation;
            $conv->url = $ctx->conversation_url;
      } else {
            $conv->uri = sprintf('%s%s=%s:%s=%s',
                             TagURI::mint(),
                             'objectType', 'thread',
                             'nonce', common_random_hexstr(8));
            $conv->url = null;  // locally generated Conversation objects don't get static URLs stored
      }
      // This insert throws exceptions on failure
      $conv->insert();
      return $conv;
   }


   // -------------------------------------------------------------------------
   // Function: noticeCount
   // Returns the number of notices in this conversation.
   static function noticeCount($id) {
      $keypart = sprintf('conversation:notice_count:%d', $id);
      $cnt = self::cacheGet($keypart);
      if ($cnt !== false) {
         return $cnt;
      }

      $notice               = new Notice();
      $notice->conversation = $id;
      $notice->whereAddIn('verb', array(ActivityVerb::POST, ActivityUtils::resolveUri(ActivityVerb::POST, true)), $notice->columnType('verb'));
      $cnt                  = $notice->count();
      self::cacheSet($keypart, $cnt);
      return $cnt;
   }


   // -------------------------------------------------------------------------
   // Function: getUrlFromNotice
   // Retrieves a the conversation for a given notice, and then returns the
   // URL for the associated conversation.
   static public function getUrlFromNotice(Notice $notice, $anchor=true) {
      $conv = Conversation::getByID($notice->conversation);
      return $conv->getUrl($anchor ? $notice->getID() : null);
   }


   // -------------------------------------------------------------------------
   // Function: getUri
   // Returns the URI of the conversation.
   public function getUri() {
      return $this->uri;
   }

   // -------------------------------------------------------------------------
   // Function: getUrl
   // Returns the URL of the conversation.
   public function getUrl($noticeId=null) {
      // FIXME: the URL router should take notice-id as an argument...
      return common_local_url('conversation', array('id' => $this->getID())) .
         ($noticeId===null ? '' : "#notice-{$noticeId}");
   }


   // -------------------------------------------------------------------------
   // Function: getNotices
   // Returns a stream containing the notices associated with this
   // conversation.
   //
   // FIXME: ...will 500 ever be too low? Taken from ConversationAction::MAX_NOTICES
   public function getNotices(Profile $scoped=null, $offset=0, $limit=500) {
      $stream = new ConversationNoticeStream($this->getID(), $scoped);
      $notices = $stream->getNotices($offset, $limit);
      return $notices;
   }


   // -------------------------------------------------------------------------
   // Function: insert
   // Save the conversation entry into the database.
   public function insert() {
      $result = parent::insert();
      if ($result === false) {
         common_log_db_error($this, 'INSERT', __FILE__);
         throw new ServerException(_('Failed to insert Conversation into database'));
      }
      return $result;
   }
}

// END OF FILE
// ============================================================================
?>