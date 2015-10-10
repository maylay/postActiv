<?php
/*
 * Laconica - a distributed open-source microblogging tool
 * Copyright (C) 2008, 2009, Control Yourself, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.     If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Table Definition for deleted_notice
 */

class Deleted_notice extends Managed_DataObject
{
    public $__table = 'deleted_notice';      // table name
    public $id;                              // int(4)  primary_key not_null
    public $profile_id;                      // int(4)   not_null
    public $uri;                             // varchar(191)  unique_key   not 255 because utf8mb4 takes more space
    public $act_uri;                         // varchar(191)  unique_key   not 255 because utf8mb4 takes more space
    public $act_created;                     // datetime()   not_null
    public $created;                         // datetime()   not_null

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'id' => array('type' => 'int', 'not null' => true, 'description' => 'notice ID'),
                'profile_id' => array('type' => 'int', 'not null' => true, 'description' => 'profile that deleted the notice'),
                'uri' => array('type' => 'varchar', 'length' => 191, 'description' => 'URI of the deleted notice'),
                'act_uri' => array('type' => 'varchar', 'length' => 191, 'description' => 'URI of the delete activity, may exist in notice table'),
                'act_created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date the notice record was created'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date the notice record was deleted'),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'deleted_notice_uri_key' => array('uri'),
                'deleted_notice_act_uri_key' => array('act_uri'),
            ),
            'indexes' => array(
                'deleted_notice_profile_id_idx' => array('profile_id'),
            ),
        );
    }

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
                            htmlspecialchars($notice->getUri())
                           );

        $act->actor = $actor->asActivityObject();
        $act->target = new ActivityObject();    // We don't save the notice object, as it's supposed to be removed!
        $act->target->id = $notice->getUri();
        $act->target->type = $notice->getObjectType();
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

    static public function fromStored(Notice $stored)
    {
        $class = get_called_class();
        return self::getByPK(array('uri', $stored->getUri()));
    }

    // The one who deleted the notice, not the notice's author
    public function getActor()
    {
        return Profile::getByID($this->profile_id);
    }

    // As above: The one who deleted the notice, not the notice's author
    public function getActorObject()
    {
        return $this->getActor()->asActivityObject();
    }

    static public function getObjectType()
    {
        return 'activity';
    }

    protected $_stored = array();

    public function getStored()
    {
        $uri = $this->getUri();
        if (!isset($this->_stored[$uri])) {
            $this->_stored[$uri] = Notice::getByPK('uri', $uri);
        }
        return $this->_stored[$uri];
    }

    public function getUri()
    {
        return $this->uri;
    }

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
        $actobj->content = sprintf(_m('<a href="%1$s">%2$s</a> deleted notice {{%3$s}}.'),
                            htmlspecialchars($actor->getUrl()),
                            htmlspecialchars($actor->getBestName()),
                            htmlspecialchars($this->getUri())
                           );

        return $actobj;
    }

    static public function extendActivity(Notice $stored, Activity $act, Profile $scoped=null)
    {
        // the original notice id and type is still stored in the Notice table
        // so we use that information to describe the delete activity
        $act->target = new ActivityObject();
        $act->target->id = $stored->getUri();
        $act->target->type = $stored->getObjectType();
        $act->objects = array(clone($act->target));

        $act->title = ActivityUtils::verbToTitle($act->verb);
    }

    static public function beforeSchemaUpdate()
    {
        $table = strtolower(get_called_class());
        $schema = Schema::get();
        $schemadef = $schema->getTableDef($table);

        // 2015-10-03 We change the meaning of the 'uri' field and move its 
        // content to the 'act_uri' for the deleted activity. act_created is
        // added too.
        if (isset($schemadef['fields']['act_uri'])) {
            // We already have the act_uri field, so no need to migrate to it.
            return;
        }
        echo "\nFound old $table table, upgrading it to contain 'act_uri' and 'act_created' field...";

        $schemadef['fields']['act_uri'] = array('type' => 'varchar', 'not null' => true, 'length' => 191, 'description' => 'URI of the deleted notice');
        $schemadef['fields']['act_created'] = array('type' => 'datetime', 'not null' => true, 'description' => 'datetime the notice record was created');
        unset($schemadef['unique keys']);
        $schema->ensureTable($table, $schemadef);

        $deleted = new Deleted_notice();
        $result = $deleted->find();
        if ($result === false) {
            print "\nFound no deleted_notice entries, continuing...";
            return true;
        }
        print "\nFound $result deleted_notice entries, aligning with new database layout: ";
        while($deleted->fetch()) {
            $orig = clone($deleted);
            $deleted->act_uri = $deleted->uri;
            // this is a fake URI just to have something to put there to avoid NULL. crc32 of uri is to avoid collisions
            $deleted->uri = TagURI::mint(strtolower(get_called_class()).':%d:%s:%s:%s:crc32=%x',
                                $deleted->profile_id,
                                ActivityUtils::resolveUri(self::getObjectType(), true),
                                'unknown',
                                common_date_iso8601($deleted->created),
                                crc32($deleted->act_uri)
                            );
            $deleted->act_created = $deleted->created;  // we don't actually know when the notice was created
            $deleted->updateWithKeys($orig, 'id');
            print ".";
        }
        print "DONE.\n";
        print "Resuming core schema upgrade...";
    }

}
