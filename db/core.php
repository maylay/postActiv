<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Some notes...
 *
 * Drupal docs don't list a bool type, but it might be nice to use rather than 'tinyint'
 * Note however that we use bitfields and things as well in tinyints, and PG's
 * "bool" type isn't 100% compatible with 0/1 checks. Just keeping tinyints. :)
 *
 * decimal <-> numeric
 *
 * MySQL 'timestamp' columns were formerly used for 'modified' files for their
 * auto-updating properties. This didn't play well with changes to cache usage
 * in 0.9.x, as we don't know the timestamp value at INSERT time and never
 * have a chance to load it up again before caching. For now I'm leaving them
 * in, but we may want to clean them up later.
 *
 * Current code should be setting 'created' and 'modified' fields explicitly;
 * this also avoids mismatches between server and client timezone settings.
 *
 *
 * fulltext indexes?
 * got one or two things wanting a custom charset setting on a field?
 *
 * foreign keys are kinda funky...
 *     those specified in inline syntax (as all in the original .sql) are NEVER ENFORCED on mysql
 *     those made with an explicit 'foreign key' WITHIN INNODB and IF there's a proper index, do get enforced
 *     double-check what we've been doing on postgres?
 */

// Quick and dirty hack - lets make sure we load the classes we need - mb
$pa_path = dirname(dirname(__FILE__));
require_once(INSTALLDIR . "/modules/Bookmark/classes/Bookmark.php");
require_once(INSTALLDIR . "/modules/DirectMessage/classes/Message.php");
require_once(INSTALLDIR . "/modules/EmailReminder/classes/Email_reminder.php");
require_once(INSTALLDIR . "/modules/EmailSummary/classes/Email_summary_status.php");
require_once(INSTALLDIR . "/modules/Event/classes/Happening.php");
require_once(INSTALLDIR . "/modules/Event/classes/RSVP.php");
require_once(INSTALLDIR . "/modules/Favorite/classes/Fave.php");
require_once(INSTALLDIR . "/modules/Oembed/classes/File_oembed.php");
require_once(INSTALLDIR . "/modules/OpenID/classes/User_openid.php");
require_once(INSTALLDIR . "/modules/OpenID/classes/User_openid_prefs.php");
require_once(INSTALLDIR . "/modules/OpenID/classes/User_openid_trustroot.php");
require_once(INSTALLDIR . "/modules/OStatus/classes/FeedSub.php");
require_once(INSTALLDIR . "/modules/OStatus/classes/HubSub.php");
require_once(INSTALLDIR . "/modules/OStatus/classes/Magicsig.php");
require_once(INSTALLDIR . "/modules/OStatus/classes/Ostatus_profile.php");
require_once(INSTALLDIR . "/modules/Poll/classes/Poll.php");
require_once(INSTALLDIR . "/modules/Poll/classes/Poll_response.php");
require_once(INSTALLDIR . "/modules/Poll/classes/User_poll_prefs.php");
require_once(INSTALLDIR . "/modules/QnA/classes/QnA_Answer.php");
require_once(INSTALLDIR . "/modules/QnA/classes/QnA_Question.php");
require_once(INSTALLDIR . "/modules/QnA/classes/QnA_Vote.php");
require_once(INSTALLDIR . "/modules/SearchSub/classes/SearchSub.php");
require_once(INSTALLDIR . "/modules/TagSub/classes/TagSub.php");


$classes = array('Schema_version',
                 'Profile',
                 'Avatar',
                 'Sms_carrier',
                 'User',
                 'Subscription',
                 'Group_join_queue',
                 'Subscription_queue',
                 'Oauth_token_association',
                 'Notice',
                 'Notice_location',
                 'Notice_source',
                 'Notice_prefs',
                 'Reply',
                 'Consumer',
                 'Token',
                 'Nonce',
                 'Conversation',
                 'Oauth_application',
                 'Oauth_application_user',
                 'Confirm_address',
                 'Remember_me',
                 'Queue_item',
                 'Notice_tag',
                 'Foreign_service',
                 'Foreign_user',
                 'Foreign_link',
                 'Foreign_subscription',
                 'Invitation',
                 'Profile_prefs',
                 'Profile_tag',
                 'Profile_list',
                 'Profile_tag_subscription',
                 'Profile_block',
                 'User_group',
                 'Related_group',
                 'Group_alias',
                 'Group_block',
                 'Group_inbox',
                 'Group_member',
                 'File',
                 'File_redirection',
                 'File_thumbnail',
                 'File_to_post',
                 'File_oembed',
                 'Session',
                 'Config',
                 'Profile_role',
                 'Location_namespace',
                 'Login_token',
                 'User_location_prefs',
                 'User_im_prefs',
                 'Local_group',
                 'User_urlshortener_prefs',
                 'Old_school_prefs',
                 'User_username',
                 'Attention',
                 'Bookmark',
                 'Deleted_notice',
                 'Feedsub',
                 'Happening',
                 'Magicsig',
                 'Message',
                 'Poll',
                 'Poll_response',
                 'User_poll_prefs',
                 'QnA_Answer',
                 'QnA_Question',
                 'QnA_Vote',
                 'SearchSub',
                 'TagSub',
                 'Ostatus_profile',
                 'Fave',
                 'Email_reminder',
                 'User_openid',
                 'User_openid_prefs',
                 'User_openid_trustroot',
);

foreach ($classes as $cls) {
    $schema[strtolower($cls)] = call_user_func(array($cls, 'schemaDef'));
}
