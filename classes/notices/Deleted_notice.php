<?php
/* ============================================================================
 * Title: Deleted_notice
 * Class which holds information for a deleted notice
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016, Maiyannah Bishop
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
 * Class which holds information for a deleted notice
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
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


// ----------------------------------------------------------------------------
// Class: Deleted_notice
// Table Definition for deleted_notice
//
// Variables:
// o $__table     - 'deleted_notice'
// o $id          - int(4)  primary_key not_null
// o $profile_id  - int(4)   not_null
// o $uri         - varchar(191)  unique_key   not 255 because utf8mb4 takes more space
// o $act_created - datetime()   not_null
// o $created     - datetime()   not_null
class Deleted_notice extends Managed_DataObject
{
   public $__table = 'deleted_notice';      // table name
   public $id;                              // int(4)  primary_key not_null
   public $profile_id;                      // int(4)   not_null
   public $uri;                             // varchar(191)  unique_key   not 255 because utf8mb4 takes more space
   public $act_created;                     // datetime()   not_null
   public $created;                         // datetime()   not_null


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns the schema definition for the table, for upgrade/checkschema
   // integrity checks
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'int', 'not null' => true, 'description' => 'notice ID'),
            'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'profile that deleted the notice'),
            'uri' => array('type' => 'varchar', 'length' => 191, 'description' => 'URI of the deleted notice'),
            'act_created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date the notice record was created'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date the notice record was deleted')),
         'primary key' => array('id'),
         'unique keys' => array(
            'deleted_notice_uri_key' => array('uri')),
         'indexes' => array(
            'deleted_notice_profile_id_idx' => array('profile_id')));
   }


   // -------------------------------------------------------------------------
   // Function: addNew
   // Add a new deleted notice to the database
   //
   // Parameters:
   // o Notice $notice
   // o Profile $actor (default NULL)
   public static function addNew(Notice $notice, Profile $actor=null)
   {
      if (is_null($actor)) {
            $actor = $notice->getProfile();
      }

      if ($notice->getProfile()->hasRole(Profile_role::DELETED)) {
            // Don't emit notices if the notice author is (being) deleted
            return false;
      }

      $act = new Activity();
      $act->verb = ActivityVerb::DELETE;
      $act->time = time();
      $act->id   = $notice->getUri();
      $act->content = sprintf(_m('<a href="%1$s">%2$s</a> deleted notice <a href="%3$s">{{%4$s}}</a>.'),
         htmlspecialchars($actor->getUrl()),
         htmlspecialchars($actor->getBestName()),
         htmlspecialchars($notice->getUrl()),
         htmlspecialchars($notice->getUri()));
      $act->actor = $actor->asActivityObject();
      $act->target = new ActivityObject();    // We don't save the notice object, as it's supposed to be removed!
      $act->target->id = $notice->getUri();
      
      try {
            $act->target->type = $notice->getObjectType();
      } catch (NoObjectTypeException $e) {
            // This could be for example an RSVP which is a verb and carries no object-type
            $act->target->type = null;
      }
      $act->objects = array(clone($act->target));
      $url = $notice->getUrl();
      $act->selfLink = $url;
      $act->editLink = $url;

      // This will make ActivityModeration run saveObjectFromActivity which adds
      // a new Deleted_notice entry in the database as well as deletes the notice
      // if the actor has permission to do so.
      $stored = Notice::saveActivity($act, $actor);
      return $stored;
   }


   // -------------------------------------------------------------------------
   // Function: fromStored
   // Return a deletion entry that was stored
   //
   // Parameters:
   // o Notice $stored
   static public function fromStored(Notice $stored) {
      $class = get_called_class();
      return self::getByKeys( ['uri' => $stored->getUri()] );
   }


   // -------------------------------------------------------------------------
   // Function: getActor
   // Return the user ID of the user whom deleted the notice in question.
   public function getActor() {
      return Profile::getByID($this->profile_id);
   }

   // -------------------------------------------------------------------------
   // Function: getActorObject
   // Return the ActivityObject of the user whom deleted the notice in question.
   public function getActorObject() {
      return $this->getActor()->asActivityObject();
   }

   // -------------------------------------------------------------------------
   // Function: getObjectType
   // Tell the caller we're an ActivityObject
   static public function getObjectType() {
      return 'activity';
   }

   protected $_stored = array();
   
   
   // -------------------------------------------------------------------------
   // Function: getStored
   // Return the stored deletion notice
   public function getStored() {
      $uri = $this->getUri();
      if (!isset($this->_stored[$uri])) {
         $this->_stored[$uri] = Notice::getByPK(array('uri' => $uri));
      }
      return $this->_stored[$uri];
   }


   // -------------------------------------------------------------------------
   // Function: getUri
   // Returns the URI of the deletion notice
   public function getUri() {
      return $this->uri;
   }


   // -------------------------------------------------------------------------
   // Function: asActivityObject
   // Returns the deletion notice as an ActivityObject, much like the name implies.
   //
   // Parameters:
   // o Profile $scoped (default NULL)
   //
   // Returns:
   // o ActivityObject $actobj
   public function asActivityObject(Profile $scoped=null)
   {
      $actobj = new ActivityObject();
      $actobj->id = $this->getUri();
      $actobj->type = ActivityObject::ACTIVITY;
      $actobj->actor = $this->getActorObject();
      $actobj->target = new ActivityObject();
      $actobj->target->id = $this->getUri();
      // FIXME: actobj->target->type? as in extendActivity, and actobj->target->actor maybe?
      $actobj->objects = array(clone($actobj->target));
      $actobj->verb = ActivityVerb::DELETE;
      $actobj->title = ActivityUtils::verbToTitle($actobj->verb);
      $actor = $this->getActor();
      // TRANS: Notice HTML content of a deleted notice. %1$s is the
      // TRANS: actor's URL, %2$s its "fancy name" and %3$s the notice URI.
      $actobj->content = sprintf(_m('<a href="%1$s">%2$s</a> deleted notice {{%3$s}}.'),
         htmlspecialchars($actor->getUrl()),
         htmlspecialchars($actor->getFancyName()),
         htmlspecialchars($this->getUri()));
      return $actobj;
   }


   // -------------------------------------------------------------------------
   // Function: extendActivity
   // The original notice id and type is still stored in the Notice table,
   // so we use that information to describe the delete activity
   //
   // Parameters:
   // o Notice $stored
   // o Activity $act
   // o Profile $scoped (default NULL)
   static public function extendActivity(Notice $stored, Activity $act, Profile $scoped=null)
   {
      $act->target = new ActivityObject();
      $act->target->id = $stored->getUri();
      $act->target->type = $stored->getObjectType();
      $act->objects = array(clone($act->target));
      $act->title = ActivityUtils::verbToTitle($act->verb);
   }


   // -------------------------------------------------------------------------
   // Function: beforeSchemaUpdate
   // A variety of cleaning tasks to do before database integrity checks.
   static public function beforeSchemaUpdate()
   {
      $table = strtolower(get_called_class());
      $schema = Schema::get();
      $schemadef = $schema->getTableDef($table);

      // 2015-12-31 If we have the act_uri field we want to remove it
      // since there's no difference in delete verbs and the original URI
      // but the act_created field stays.
      if (!isset($schemadef['fields']['act_uri']) && isset($schemadef['fields']['act_created'])) {
         // We don't have an act_uri field, and we also have act_created, so no need to migrate.
         return;
      } elseif (isset($schemadef['fields']['act_uri']) && !isset($schemadef['fields']['act_created'])) {
         throw new ServerException('Something is wrong with your database, you have the act_uri field but NOT act_created in deleted_notice!');
      }

      if (!isset($schemadef['fields']['act_created'])) {
         // this is a "normal" upgrade from StatusNet for example
         echo "\nFound old $table table, upgrading it to add 'act_created' field...";
         $schemadef['fields']['act_created'] = array('type' => 'datetime', 'not null' => true, 'description' => 'datetime the notice record was created');
         $schemadef['fields']['uri']['length'] = 191;    // we likely don't have to discover too long keys here
         $schema->ensureTable($table, $schemadef);
         $deleted = new Deleted_notice();
         // we don't actually know when the notice was created for the old ones
         $deleted->query('UPDATE deleted_notice SET act_created=created;');
      } else {
         // 2015-10-03 For a while we had act_uri and act_created fields which
         // apparently wasn't necessary.
         echo "\nFound old $table table, upgrading it to remove 'act_uri' field...";
         // we stored what should be in 'uri' in the 'act_uri' field for some night-coding reason.
          $deleted = new Deleted_notice();
          $deleted->query('UPDATE deleted_notice SET uri=act_uri;');
      }
      print "DONE.\n";
      print "Resuming core schema upgrade...";
   }


   // -------------------------------------------------------------------------
   // Function: insert
   function insert() {
      $result = parent::insert();
      if ($result === false) {
         common_log_db_error($this, 'INSERT', __FILE__);
         // TRANS: Server exception thrown when a stored object entry cannot be saved.
         throw new ServerException('Could not save Deleted_notice');
      }
   }
}

// END OF FILE
// ============================================================================
?>