#!/usr/bin/env php
<?php
/*
 * StatusNet - a distributed open-source microblogging tool
 * Copyright (C) 2008-2011 StatusNet, Inc.
 *
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
 */

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'x::';
$longoptions = array('extensions=');

$helptext = <<<END_OF_UPGRADE_HELP
php upgrade.php [options]
Upgrade database schema and data to latest software

END_OF_UPGRADE_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

function main()
{
    if (Event::handle('StartUpgrade')) {
        updateSchemaCore();
        updateSchemaPlugins();

        // These replace old "fixup_*" scripts

        fixupNoticeRendered();
        fixupNoticeConversation();
        initConversation();
        initInbox();
        fixupGroupURI();

        initLocalGroup();
        initNoticeReshare();
    
        initFaveURI();
        initSubscriptionURI();
        initGroupMemberURI();

        initProfileLists();

        Event::handle('EndUpgrade');
    }
}

function tableDefs()
{
	$schema = array();
	require INSTALLDIR.'/db/core.php';
	return $schema;
}

function updateSchemaCore()
{
    printfnq("Upgrading core schema...");

    $schema = Schema::get();
    $schemaUpdater = new SchemaUpdater($schema);
    foreach (tableDefs() as $table => $def) {
        $schemaUpdater->register($table, $def);
    }
    $schemaUpdater->checkSchema();

    printfnq("DONE.\n");
}

function updateSchemaPlugins()
{
    printfnq("Upgrading plugin schema...");

    Event::handle('CheckSchema');

    printfnq("DONE.\n");
}

function fixupNoticeRendered()
{
    printfnq("Ensuring all notices have rendered HTML...");

    $notice = new Notice();

    $notice->whereAdd('rendered IS NULL');
    $notice->find();

    while ($notice->fetch()) {
        $original = clone($notice);
        $notice->rendered = common_render_content($notice->content, $notice);
        $notice->update($original);
    }

    printfnq("DONE.\n");
}

function fixupNoticeConversation()
{
    printfnq("Ensuring all notices have a conversation ID...");

    $notice = new Notice();
    $notice->whereAdd('conversation is null');
    $notice->orderBy('id'); // try to get originals before replies
    $notice->find();

    while ($notice->fetch()) {
        try {
            $cid = null;
    
            $orig = clone($notice);
    
            if (empty($notice->reply_to)) {
                $notice->conversation = $notice->id;
            } else {
                $reply = Notice::staticGet('id', $notice->reply_to);

                if (empty($reply)) {
                    $notice->conversation = $notice->id;
                } else if (empty($reply->conversation)) {
                    $notice->conversation = $notice->id;
                } else {
                    $notice->conversation = $reply->conversation;
                }
	
                unset($reply);
                $reply = null;
            }

            $result = $notice->update($orig);

            $orig = null;
            unset($orig);
        } catch (Exception $e) {
            printv("Error setting conversation: " . $e->getMessage());
        }
    }

    printfnq("DONE.\n");
}

function fixupGroupURI()
{
    printfnq("Ensuring all groups have an URI...");

    $group = new User_group();
    $group->whereAdd('uri IS NULL');

    if ($group->find()) {
        while ($group->fetch()) {
            $orig = User_group::staticGet('id', $group->id);
            $group->uri = $group->getUri();
            $group->update($orig);
        }
    }

    printfnq("DONE.\n");
}

function initConversation()
{
    printfnq("Ensuring all conversations have a row in conversation table...");

    $notice = new Notice();
    $notice->query('select distinct notice.conversation from notice '.
                   'where notice.conversation is not null '.
                   'and not exists (select conversation.id from conversation where id = notice.conversation)');

    while ($notice->fetch()) {

        $id = $notice->conversation;

        $uri = common_local_url('conversation', array('id' => $id));

        // @fixme db_dataobject won't save our value for an autoincrement
        // so we're bypassing the insert wrappers
        $conv = new Conversation();
        $sql = "insert into conversation (id,uri,created) values(%d,'%s','%s')";
        $sql = sprintf($sql,
                       $id,
                       $conv->escape($uri),
                       $conv->escape(common_sql_now()));
        $conv->query($sql);
    }

    printfnq("DONE.\n");
}

function initInbox()
{
    printfnq("Ensuring all users have an inbox...");

    $user = new User();
    $user->whereAdd('not exists (select user_id from inbox where user_id = user.id)');
    $user->orderBy('id');

    if ($user->find()) {

        while ($user->fetch()) {

            try {
                $notice = new Notice();

                $notice->selectAdd();
                $notice->selectAdd('id');
                $notice->joinAdd(array('profile_id', 'subscription:subscribed'));
                $notice->whereAdd('subscription.subscriber = ' . $user->id);
                $notice->whereAdd('notice.created >= subscription.created');

                $ids = array();

                if ($notice->find()) {
                    while ($notice->fetch()) {
                        $ids[] = $notice->id;
                    }
                }

                $notice = null;

                $inbox = new Inbox();
                $inbox->user_id = $user->id;
                $inbox->pack($ids);
                $inbox->insert();
            } catch (Exception $e) {
                printv("Error initializing inbox: " . $e->getMessage());
            }
        }
    }

    printfnq("DONE.\n");
}

function initLocalGroup()
{
    printfnq("Ensuring all local user groups have a local_group...");

    $group = new User_group();
    $group->whereAdd('NOT EXISTS (select group_id from local_group where group_id = user_group.id)');
    $group->find();

    while ($group->fetch()) {
        try {
            // Hack to check for local groups
            if ($group->getUri() == common_local_url('groupbyid', array('id' => $group->id))) {
                $lg = new Local_group();

                $lg->group_id = $group->id;
                $lg->nickname = $group->nickname;
                $lg->created  = $group->created; // XXX: common_sql_now() ?
                $lg->modified = $group->modified;

                $lg->insert();
            }
        } catch (Exception $e) {
            printfv("Error initializing local group for {$group->nickname}:" . $e->getMessage());
        }
    }

    printfnq("DONE.\n");
}

function initNoticeReshare()
{
    printfnq("Ensuring all reshares have the correct verb and object-type...");
    
    $notice = new Notice();
    $notice->whereAdd('repeat_of is not null');
    $notice->whereAdd('(verb != "'.ActivityVerb::SHARE.'" OR object_type != "'.ActivityObject::ACTIVITY.'")');

    if ($notice->find()) {
        while ($notice->fetch()) {
            try {
                $orig = Notice::staticGet('id', $notice->id);
                $notice->verb = ActivityVerb::SHARE;
                $notice->object_type = ActivityObject::ACTIVITY;
                $notice->update($orig);
            } catch (Exception $e) {
                printfv("Error updating verb and object_type for {$notice->id}:" . $e->getMessage());
            }
        }
    }

    printfnq("DONE.\n");
}

function initFaveURI() 
{
    printfnq("Ensuring all faves have a URI...");

    $fave = new Fave();
    $fave->whereAdd('uri IS NULL');

    if ($fave->find()) {
        while ($fave->fetch()) {
            try {
                $fave->decache();
                $fave->query(sprintf('update fave '.
                                     'set uri = "%s", '.
                                     '    modified = "%s" '.
                                     'where user_id = %d '.
                                     'and notice_id = %d',
                                     Fave::newURI($fave->user_id, $fave->notice_id, $fave->modified),
                                     common_sql_date(strtotime($fave->modified)),
                                     $fave->user_id,
                                     $fave->notice_id));
            } catch (Exception $e) {
                common_log(LOG_ERR, "Error updated fave URI: " . $e->getMessage());
            }
        }
    }

    printfnq("DONE.\n");
}

function initSubscriptionURI()
{
    printfnq("Ensuring all subscriptions have a URI...");

    $sub = new Subscription();
    $sub->whereAdd('uri IS NULL');

    if ($sub->find()) {
        while ($sub->fetch()) {
            try {
                $sub->decache();
                $sub->query(sprintf('update subscription '.
                                    'set uri = "%s" '.
                                    'where subscriber = %d '.
                                    'and subscribed = %d',
                                    Subscription::newURI($sub->subscriber, $sub->subscribed, $sub->created),
                                    $sub->subscriber,
                                    $sub->subscribed));
            } catch (Exception $e) {
                common_log(LOG_ERR, "Error updated subscription URI: " . $e->getMessage());
            }
        }
    }

    printfnq("DONE.\n");
}

function initGroupMemberURI()
{
    printfnq("Ensuring all group memberships have a URI...");

    $mem = new Group_member();
    $mem->whereAdd('uri IS NULL');

    if ($mem->find()) {
        while ($mem->fetch()) {
            try {
                $mem->decache();
                $mem->query(sprintf('update group_member set uri = "%s" '.
                                    'where profile_id = %d ' . 
                                    'and group_id = %d ',
                                    Group_member::newURI($mem->profile_id, $mem->group_id, $mem->created),
                                    $mem->profile_id,
                                    $mem->group_id));
            } catch (Exception $e) {
                common_log(LOG_ERR, "Error updated membership URI: " . $e->getMessage());  
          }
        }
    }

    printfnq("DONE.\n");
}

function initProfileLists()
{
    printfnq("Ensuring all profile tags have a corresponding list...");

    $ptag = new Profile_tag();
    $ptag->selectAdd();
    $ptag->selectAdd('tagger, tag, count(*) as tagged_count');
    $ptag->whereAdd('NOT EXISTS (SELECT tagger, tagged from profile_list '.
                    'where profile_tag.tagger = profile_list.tagger '.
                    'and profile_tag.tag = profile_list.tag)');
    $ptag->groupBy('tagger, tag');
    $ptag->orderBy('tagger, tag');

    if ($ptag->find()) {
        while ($ptag->fetch()) {
            $plist = new Profile_list();

            $plist->tagger   = $ptag->tagger;
            $plist->tag      = $ptag->tag;
            $plist->private  = 0;
            $plist->created  = common_sql_now();
            $plist->modified = $plist->created;
            $plist->mainpage = common_local_url('showprofiletag',
                                                array('tagger' => $plist->getTagger()->nickname,
                                                      'tag'    => $plist->tag));;

            $plist->tagged_count     = $ptag->tagged_count;
            $plist->subscriber_count = 0;

            $plist->insert();

            $orig = clone($plist);
            // After insert since it uses auto-generated ID
            $plist->uri      = common_local_url('profiletagbyid',
                                        array('id' => $plist->id, 'tagger_id' => $plist->tagger));

            $plist->update($orig);
        }
    }

    printfnq("DONE.\n");
}

main();
