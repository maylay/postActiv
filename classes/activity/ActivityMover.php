<?php
/* ============================================================================
 * Title: ActivityMover
 * Queue handler for exporting (federating) Activities
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
 * Tested with PHP 5.6
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
// Class: ActivityMover
// Queue handler for exporting (federating) Activities
class ActivityMover extends QueueHandler
{
   // -------------------------------------------------------------------------
   // Function: transport
   function transport() {
      return 'actmove';
   }


   // ------------------------------------------------------------------------
   // Function: handle
   //
   // Parameters:
   // o data
   function handle($data) {
      list ($act, $sink, $userURI, $remoteURI) = $data;
      $user   = User::getKV('uri', $userURI);
      try {
         $remote = Profile::fromUri($remoteURI);
      } catch (UnknownUriException $e) {
         // Don't retry. It's hard to tell whether it's because of
         // lookup failures or because the URI is permanently gone.
         // If we knew it was temporary, we'd return false here.
         return true;
      }

      try {
         $this->moveActivity($act, $sink, $user, $remote);
      } catch (ClientException $cex) {
         $this->log(LOG_WARNING,
                    $cex->getMessage());
         // "don't retry me"
         return true;
      } catch (ServerException $sex) {
         $this->log(LOG_WARNING,
                    $sex->getMessage());
         // "retry me" (because we think the server might handle it next time)
         return false;
      } catch (Exception $ex) {
         $this->log(LOG_WARNING,
                    $ex->getMessage());
         // "don't retry me"
         return true;
      }
   }


   // -------------------------------------------------------------------------
   // Function: moveActivity
   //
   // Parameters:
   // o act
   // o sink
   // o user
   // o remote
   function moveActivity($act, $sink, $user, $remote) {
      if (empty($user)) {
         // TRANS: Exception thrown if a non-existing user is provided. %s is a user ID.
         throw new Exception(sprintf(_('No such user "%s".'),$act->actor->id));
      }

      switch ($act->verb) {
/*        case ActivityVerb::FAVORITE:
            $this->log(LOG_INFO,
                       "Moving favorite of {$act->objects[0]->id} by ".
                       "{$act->actor->id} to {$remote->nickname}.");
            // push it, then delete local
            $sink->postActivity($act);
            $notice = Notice::getKV('uri', $act->objects[0]->id);
            if (!empty($notice)) {
                $fave = Fave::pkeyGet(array('user_id' => $user->id,
                                            'notice_id' => $notice->id));
                $fave->delete();
            }
            break;*/
      case ActivityVerb::POST:
         $this->log(LOG_INFO,
                    "Moving notice {$act->objects[0]->id} by ".
                    "{$act->actor->id} to {$remote->nickname}.");
         // XXX: send a reshare, not a post
         $sink->postActivity($act);
         $notice = Notice::getKV('uri', $act->objects[0]->id);
         if (!empty($notice)) {
            $notice->deleteAs($user->getProfile(), false);
         }
         break;
      case ActivityVerb::JOIN:
         $this->log(LOG_INFO,
                    "Moving group join of {$act->objects[0]->id} by ".
                    "{$act->actor->id} to {$remote->nickname}.");
         $sink->postActivity($act);
         $group = User_group::getKV('uri', $act->objects[0]->id);
         if (!empty($group)) {
            $user->leaveGroup($group);
         }
         break;
      case ActivityVerb::FOLLOW:
         if ($act->actor->id === $user->getUri()) {
            $this->log(LOG_INFO,
                       "Moving subscription to {$act->objects[0]->id} by ".
                       "{$act->actor->id} to {$remote->nickname}.");
            $sink->postActivity($act);
            try {
               $other = Profile::fromUri($act->objects[0]->id);
               Subscription::cancel($user->getProfile(), $other);
            } catch (UnknownUriException $e) {
                // Can't cancel subscription if we don't know who to alert
            }
         } else {
            $otherUser = User::getKV('uri', $act->actor->id);
            if (!empty($otherUser)) {
               $this->log(LOG_INFO,
                          "Changing sub to {$act->objects[0]->id}".
                           "by {$act->actor->id} to {$remote->nickname}.");
               $otherProfile = $otherUser->getProfile();
               Subscription::ensureStart($otherProfile, $remote);
               Subscription::cancel($otherProfile, $user->getProfile());
            } else {
               $this->log(LOG_NOTICE,
                          "Not changing sub to {$act->objects[0]->id}".
                          "by remote {$act->actor->id} ".
                          "to {$remote->nickname}.");
            }
         }
         break;
      }
   }


   // -------------------------------------------------------------------------
   // Function: log
   // Log some data
   //
   // Add a header for our class so we know who did it.
   //
   // Parameters:
   // o int $level      - Log level, like LOG_ERR or LOG_INFO
   // o string $message - Message to log
   //
   // Returns:
   // o void
   protected function log($level, $message) {
      common_log($level, "ActivityMover: " . $message);
   }
}

?>