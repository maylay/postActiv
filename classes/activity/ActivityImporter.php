<?php
/* ============================================================================
 * Title: ActivityImporter
 * Queue handler for importing Activities
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
 * An activity verb in class form, and the related scaffolding.
 *
 * This file also now consolidates the ActivityContext, ActivityImporter,
 * ActivityMover, ActivitySink, and ActivitySource classes, formerly at
 * /lib/<class>.php
 *
 * o Activity abstracts the class for an activity verb.
 * o ActivityContext contains information of the context of the activity verb.
 * o ActivityImporter abstracts a means that is importing activity verbs
 *   into the system as part of a user's timeline.
 * o ActivityMover abstracts the means to transport activity verbs.
 * o ActivitySink abstracts a class to receive activity verbs.
 * o ActivitySource abstracts a class to represent the source of a received
 *    activity verb.
 *
 * ActivityObject is a noun in the activity universe basically, from
 * the original file:
 *     A noun-ish thing in the activity universe
 *
 *     The activity streams spec talks about activity objects, while also
 *     having a tag activity:object, which is in fact an activity object.
 *     Aaaaaah!
 *
 *     This is just a thing in the activity universe. Can be the subject,
 *     object, or indirect object (target!) of an activity verb. Rotten
 *     name, and I'm propagating it. *sigh*
 * It's large enough that I've left it seperate in activityobject.php
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Brion Vibber <brion@pobox.com>
 * o James Walker <walkah@walkah.net>
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chimo <chimo@chromic.org>
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
// Class: ActivityImporter
// Queue handler for importing activities, as the name suggests.
//
// Variables:
// o trusted - whether the source is trusted, defaults to false
class ActivityImporter extends QueueHandler
{
   private $trusted = false;


   // -------------------------------------------------------------------------
   // Function: handle
   // Main processing handler that delegates out to
   function handle($data)
   {
      list($user, $author, $activity, $trusted) = $data;
      $this->trusted = $trusted;
      $done = null;
      try {
         if (Event::handle('StartImportActivity',
                           array($user, $author, $activity, $trusted, &$done))) {
            switch ($activity->verb) {
               case ActivityVerb::FOLLOW:
                  $this->subscribeProfile($user, $author, $activity);
                  break;
               case ActivityVerb::JOIN:
                  $this->joinGroup($user, $activity);
                  break;
               case ActivityVerb::POST:
                  $this->postNote($user, $author, $activity);
                  break;
               default:
                  // TRANS: Client exception thrown when using an unknown verb for the activity importer.
                  throw new ClientException(sprintf(_("Unknown verb: \"%s\"."),$activity->verb));
            }
            Event::handle('EndImportActivity',
                          array($user, $author, $activity, $trusted));
            $done = true;
         }
      } catch (Exception $e) {
         common_log(LOG_ERR, $e->getMessage());
         $done = true;
      }
      return $done;
   }


   // -------------------------------------------------------------------------
   // Function: subscribeProfile
   // Process an activity verb saying that a user has subscribed to a profile
   //
   // Parameters:
   // o user     - object of the user being subscribed to
   // o author   - object of the person subscribing to the user
   // o activity - the activity object
   //
   // Error States:
   // o user isn't trusted
   // o user subscribing is a remote user
   // o cannot find profile we're subscribing to
   // o activity isn't related to referenced user
   function subscribeProfile($user, $author, $activity) {
      $profile = $user->getProfile();
      if ($activity->objects[0]->id == $author->id) {
         if (!$this->trusted) {
            // TRANS: Client exception thrown when trying to force a subscription for an untrusted user.
            throw new ClientException(_('Cannot force subscription for untrusted user.'));
         }
         $other = $activity->actor;
         $otherUser = User::getKV('uri', $other->id);
         if (!$otherUser instanceof User) {
            // TRANS: Client exception thrown when trying to force a remote user to subscribe.
            throw new Exception(_('Cannot force remote user to subscribe.'));
         }
         $otherProfile = $otherUser->getProfile();
         // XXX: don't do this for untrusted input!
         Subscription::ensureStart($otherProfile, $profile);
      } else if (empty($activity->actor) || $activity->actor->id == $author->id) {
         $other = $activity->objects[0];
         try {
            $otherProfile = Profile::fromUri($other->id);
         } catch (UnknownUriException $e) {
            // Let's convert it to a client exception instead of server.
            // TRANS: Client exception thrown when trying to subscribe to an unknown profile.
            throw new ClientException(_('Unknown profile.'));
         }

         Subscription::ensureStart($profile, $otherProfile);
      } else {
         // TRANS: Client exception thrown when trying to import an event not related to the importing user.
         throw new Exception(_('This activity seems unrelated to our user.'));
      }
   }


   // -------------------------------------------------------------------------
   // Function: joinGroup
   // Process an activity stating the user joined a group
   //
   // Parameters:
   // o user     - object of the user joining the group
   // o activity - the activity object
   //
   // Error states:
   // o user is already a member of the group
   // o profile referenced as a group, isn't actually a group
   function joinGroup($user, $activity) {
      // XXX: check that actor == subject
      $uri = $activity->objects[0]->id;
      $group = User_group::getKV('uri', $uri);
      if (!$group instanceof User_group) {
         $oprofile = Ostatus_profile::ensureActivityObjectProfile($activity->objects[0]);
         if (!$oprofile->isGroup()) {
            // TRANS: Client exception thrown when trying to join a remote group that is not a group.
            throw new ClientException(_('Remote profile is not a group!'));
         }
         $group = $oprofile->localGroup();
      }
      assert(!empty($group));
      if ($user->isMember($group)) {
         // TRANS: Client exception thrown when trying to join a group the importing user is already a member of.
         throw new ClientException(_("User is already a member of this group."));
      }
      $user->joinGroup($group);
   }


   // -------------------------------------------------------------------------
   // Function: postNote
   // XXX: largely cadged from Ostatus_profile::processNote()
   //
   // Parameters:
   // o user
   // o author
   // o activity
   //
   // Error states:
   // o attempting to overwrite a notice when author isn't trusted
   // o importing a notice by another user
   function postNote($user, $author, $activity) {
      $note = $activity->objects[0];
      $sourceUri = $note->id;
      $notice = Notice::getKV('uri', $sourceUri);
      if ($notice instanceof Notice) {
         common_log(LOG_INFO, "Notice {$sourceUri} already exists.");
         if ($this->trusted) {
            $profile = $notice->getProfile();
            $uri = $profile->getUri();
            if ($uri === $author->id) {
               common_log(LOG_INFO, sprintf('Updating notice author from %s to %s', $author->id, $user->getUri()));
               $orig = clone($notice);
               $notice->profile_id = $user->id;
               $notice->update($orig);
               return;
            } else {
               // TRANS: Client exception thrown when trying to import a notice by another user.
               // TRANS: %1$s is the source URI of the notice, %2$s is the URI of the author.
               throw new ClientException(sprintf(_('Already know about notice %1$s and '.
                                                   ' it has a different author %2$s.'),
                                                   $sourceUri, $uri));
            }
         } else {
                // TRANS: Client exception thrown when trying to overwrite the author information for a non-trusted user during import.
                throw new ClientException(_('Not overwriting author info for non-trusted user.'));
         }
      }
      // Use summary as fallback for content
      if (!empty($note->content)) {
         $sourceContent = $note->content;
      } else if (!empty($note->summary)) {
         $sourceContent = $note->summary;
      } else if (!empty($note->title)) {
         $sourceContent = $note->title;
      } else {
         // @fixme fetch from $sourceUrl?
         // TRANS: Client exception thrown when trying to import a notice without content.
         // TRANS: %s is the notice URI.
         throw new ClientException(sprintf(_('No content for notice %s.'),$sourceUri));
      }

      // Get (safe!) HTML and text versions of the content
      $rendered = common_purify($sourceContent);
      $content = common_strip_html($rendered);
      $shortened = $user->shortenLinks($content);
      $options = array('is_local' => Notice::LOCAL_PUBLIC,
                       'uri' => $sourceUri,
                       'rendered' => $rendered,
                       'replies' => array(),
                       'groups' => array(),
                       'tags' => array(),
                       'urls' => array(),
                       'distribute' => false);

      // Check for optional attributes...
      if (!empty($activity->time)) {
         $options['created'] = common_sql_date($activity->time);
      }
      if ($activity->context) {
         // Any individual or group attn: targets?
         list($options['groups'], $options['replies']) = $this->filterAttention($activity->context->attention);
         // Maintain direct reply associations
         // @fixme what about conversation ID?
         if (!empty($activity->context->replyToID)) {
            $orig = Notice::getKV('uri', $activity->context->replyToID);
            if ($orig instanceof Notice) {
                $options['reply_to'] = $orig->id;
            }
         }
         $location = $activity->context->location;
         if ($location) {
            $options['lat'] = $location->lat;
            $options['lon'] = $location->lon;
            if ($location->location_id) {
               $options['location_ns'] = $location->location_ns;
               $options['location_id'] = $location->location_id;
            }
         }
      }

      // Atom categories <-> hashtags
      foreach ($activity->categories as $cat) {
         if ($cat->term) {
            $term = common_canonical_tag($cat->term);
            if ($term) {
               $options['tags'][] = $term;
            }
         }
      }

      // Atom enclosures -> attachment URLs
      foreach ($activity->enclosures as $href) {
         // @fixme save these locally or....?
         $options['urls'][] = $href;
      }
      common_log(LOG_INFO, "Saving notice {$options['uri']}");
      $saved = Notice::saveNew($user->id, $content, 'restore', $options);
      return $saved;
   }


   // -------------------------------------------------------------------------
   // Function: filterAttention
   // Make sure an attention goes to where it needs to go.
   //
   // Parameters:
   // o attn
   protected function filterAttention(array $attn) {
      $groups = array();  // TODO: context->attention
      $replies = array(); // TODO: context->attention
      foreach ($attn as $recipient=>$type) {
         // Is the recipient a local user?
         $user = User::getKV('uri', $recipient);
         if ($user instanceof User) {
            // TODO: @fixme sender verification, spam etc?
            $replies[] = $recipient;
            continue;
         }

         // Is the recipient a remote group?
         $oprofile = Ostatus_profile::ensureProfileURI($recipient);
         if ($oprofile) {
            if (!$oprofile->isGroup()) {
               // may be canonicalized or something
               $replies[] = $oprofile->uri;
            }
            continue;
         }

         // Is the recipient a local group?
         // TODO: @fixme uri on user_group isn't reliable yet
         // $group = User_group::getKV('uri', $recipient);
         $id = OStatusPlugin::localGroupFromUrl($recipient);
         if ($id) {
            $group = User_group::getKV('id', $id);
            if ($group) {
               // Deliver to all members of this local group if allowed.
               $profile = $sender->localProfile();
               if ($profile->isMember($group)) {
                        $groups[] = $group->id;
               } else {
                        common_log(LOG_INFO, "Skipping reply to local group {$group->nickname} as sender {$profile->id} is not a member");
               }
                    continue;
               } else {
                  common_log(LOG_INFO, "Skipping reply to bogus group $recipient");
               }
         }
      }
      return array($groups, $replies);
   }
}

// END OF FILE
// ============================================================================
?>