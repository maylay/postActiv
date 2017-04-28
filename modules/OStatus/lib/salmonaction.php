<?php
/* ============================================================================
 * Title: Salmon Action
 * Superclass handler for receiving salmon messages
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
 * Superclass handler for receiving salmon messages
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Gina Haeussge <osd@foosel.net>
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Zach Copley
 * o Brion Vibber <brion@pobox.com>
 * o James Walker <walkah@walkah.net>
 * o Evan Prodromou
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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


class SalmonAction extends Action {
   protected $needPost = true;
   protected $oprofile = null; // Ostatus_profile of the actor
   protected $actor    = null; // Profile object of the actor

   var $format   = 'text'; // error messages will be printed in plaintext
   var $xml      = null;
   var $activity = null;
   var $target   = null;

   protected function prepare(array $args=array()) {
      postActiv::setApi(true); // Send smaller error pages
      parent::prepare($args);

      // Verify the content type checks out
      if (!isset($_SERVER['CONTENT_TYPE'])) {
         // TRANS: Client error. Do not translate "Content-type"
         throw new ClientException(_m('Salmon requires a Content-type header.'));
      }
      $envxml = null;
      switch ($_SERVER['CONTENT_TYPE']) {
      case 'application/magic-envelope+xml':
         $envxml = file_get_contents('php://input');
         break;
      case 'application/x-www-form-urlencoded':
         $envxml = Magicsig::base64_url_decode($this->trimmed('xml'));
         break;
      default:
         // TRANS: Client error. Do not translate the quoted "application/[type]" strings.
         throw new ClientException(_m('Salmon requires "application/magic-envelope+xml". For Diaspora we also accept "application/x-www-form-urlencoded" with an "xml" parameter.', 415));
      }

      // Verify the MagicEnvelope
      if (empty($envxml)) {
         throw new ClientException('No magic envelope supplied in POST.');
      }
      try {
         $magic_env = new MagicEnvelope($envxml);   // parse incoming XML as a MagicEnvelope
         $entry = $magic_env->getPayload();  // Not cryptographically verified yet!
         $this->activity = new Activity($entry->documentElement);

         if (empty($this->activity->actor->id)) {
            common_log(LOG_ERR, "Broken actor: " . var_export($this->activity->actor->id, true));
            common_log(LOG_ERR, "Activity with no actor: " . var_export($this->activity, true));
            // TRANS: Exception.
            throw new ClientException(_m('Activity in salmon slap has no actor id.'));
         }
         // ensureProfiles sets $this->actor and $this->oprofile
         $this->ensureProfiles();
      } catch (Exception $e) {
         common_debug('Salmon envelope parsing failed with: '.$e->getMessage());
         // convert exception to ClientException
         throw new ClientException($e->getMessage());
      }

      // Cryptographic verification test, throws exception on failure
      $magic_env->verify($this->actor);
      common_debug('Salmon slap is carrying activity URI=='._ve($this->activity->id));

      // If we got this far, it's checked out!
      return true;
   }

   // -------------------------------------------------------------------------
   // Function: handle
   // Check the posted activity type and break out to appropriate processing.
   protected function handle() {
        parent::handle();

        // Make sure the activity and target are valid
        assert($this->activity instanceof Activity);
        assert($this->target instanceof Profile);
        common_log(LOG_DEBUG, "Got a " . $this->activity->verb);

        // If they are, also make sure the originating instance isn't banned
        if ($this->isInstanceBlocked()) {
           common_log(LOG_INFO, "Salmon originating from a blocked instance, discarding.");
           return false;
        }

        try {
            $options = [ 'source' => 'ostatus' ];
            common_debug('Save salmon slap directly with Notice::saveActivity for actor=='.$this->actor->getID());
            $stored = Notice::saveActivity($this->activity, $this->actor, $options);
            common_debug('Save salmon slap finished, notice id=='.$stored->getID());
            return true;
        } catch (AlreadyFulfilledException $e) {
            // The action's results are already fulfilled. Maybe it was a
            // duplicate? Maybe someone's database is out of sync?
            // Let's just accept it and move on.
            common_log(LOG_INFO, 'Salmon slap carried an event which had already been fulfilled.');
            return true;
        } catch (NoticeSaveException $e) {
            common_debug('Notice::saveActivity did not save our '._ve($this->activity->verb).' activity, trying old-fashioned salmon saving.');
        }

        try {
            if (Event::handle('StartHandleSalmonTarget', array($this->activity, $this->target)) &&
                    Event::handle('StartHandleSalmon', array($this->activity))) {
                switch ($this->activity->verb) {
                case ActivityVerb::POST:
                    $this->handlePost();
                    break;
                case ActivityVerb::SHARE:
                    $this->handleShare();
                    break;
                case ActivityVerb::FOLLOW:
                case ActivityVerb::FRIEND:
                    $this->handleFollow();
                    break;
                case ActivityVerb::UNFOLLOW:
                    $this->handleUnfollow();
                    break;
                case ActivityVerb::JOIN:
                    $this->handleJoin();
                    break;
                case ActivityVerb::LEAVE:
                    $this->handleLeave();
                    break;
                case ActivityVerb::TAG:
                    $this->handleTag();
                    break;
                case ActivityVerb::UNTAG:
                    $this->handleUntag();
                    break;
                case ActivityVerb::UPDATE_PROFILE:
                    $this->handleUpdateProfile();
                    break;
                default:
                    // TRANS: Client exception.
                    throw new ClientException(_m('Unrecognized activity type.'));
                }
                Event::handle('EndHandleSalmon', array($this->activity));
                Event::handle('EndHandleSalmonTarget', array($this->activity, $this->target));
            }
        } catch (AlreadyFulfilledException $e) {
            // The action's results are already fulfilled. Maybe it was a
            // duplicate? Maybe someone's database is out of sync?
            // Let's just accept it and move on.
            common_log(LOG_INFO, 'Salmon slap carried an event which had already been fulfilled.');
        }
   }


   // -------------------------------------------------------------------------
   // Function: isInstanceBlocked
   // Returns true if the sending instance is blocked by this one
   protected function isInstanceBlocked() {
      // Get list of banned instances
		$configphpsettings = common_config('site','sanctions') ?: array();
		foreach($configphpsettings as $configphpsetting=>$value) {
			$settings[$configphpsetting] = $value;
		}
		$bans = $settings['banned_instances'];
      common_debug("Banned instances currently: " . json_encode($bans));

      // Get the instance of the sending user
      $originator = $this->actor->id;
      $originator = Profile::getKV('id', $originator);
      $originator = $originator->profileurl;
      common_log(LOG_INFO, "Received salmon from " . $originator);

		// If we have no banned instances we can bail
      if ($bans==null) {
         return false;
      }

      // Map bans to an array
      $bans = explode(',', $bans);
      
      // Return whether this feed from a banned instance
      $is_banned = false;
      foreach ($bans as $banned_instance) {
         if (strpos($originator, $banned_instance)) {
            $is_banned = true;
            break;
         }
      }
      common_log(LOG_INFO, "This salmon's originating instance ban status: " . (($is_banned) ? "true" : "false"));
      return $is_banned;

   }


   // -------------------------------------------------------------------------
   // Function: handlePost
   // Placeholder handler for a handler to inherit post handling
   function handlePost() {
      // TRANS: Client exception.
      throw new ClientException(_m('This target does not understand posts.'));
   }


   // -------------------------------------------------------------------------
   // Function: handleFollow
   // Placeholder handler for a handler to inherit follow handling
   function handleFollow() {
      // TRANS: Client exception.
      throw new ClientException(_m('This target does not understand follows.'));
   }


   // -------------------------------------------------------------------------
   // Function: handleUnfollow
   // Placeholder handler for a handler to inherit unfollow handling
   function handleUnfollow() {
      // TRANS: Client exception.
      throw new ClientException(_m('This target does not understand unfollows.'));
   }


   // -------------------------------------------------------------------------
   // Function: handleShare
   // Placeholder handler for a handler to inherit repost handling
   function handleShare() {
        // TRANS: Client exception.
        throw new ClientException(_m('This target does not understand share events.'));
   }


   // -------------------------------------------------------------------------
   // Function: handleJoin
   // Placeholder handler for a handler to inherit group joining handling
   function handleJoin() {
      // TRANS: Client exception.
      throw new ClientException(_m('This target does not understand joins.'));
   }


   // -------------------------------------------------------------------------
   // Function: handleLeave
   // Placeholder handler for a handler to inherit group leaving handling
   function handleLeave() {
        // TRANS: Client exception.
        throw new ClientException(_m('This target does not understand leave events.'));
   }

    function handleTag()
    {
        // TRANS: Client exception.
        throw new ClientException(_m('This target does not understand list events.'));
    }

    function handleUntag()
    {
        // TRANS: Client exception.
        throw new ClientException(_m('This target does not understand unlist events.'));
    }

    /**
     * Remote user sent us an update to their profile.
     * If we already know them, accept the updates.
     */
    function handleUpdateProfile()
    {
        $oprofile = Ostatus_profile::getActorProfile($this->activity);
        if ($oprofile instanceof Ostatus_profile) {
            common_log(LOG_INFO, "Got a profile-update ping from $oprofile->uri");
            $oprofile->updateFromActivityObject($this->activity->actor);
        } else {
            common_log(LOG_INFO, "Ignoring profile-update ping from unknown " . $this->activity->actor->id);
        }
    }

    function ensureProfiles()
    {
        try {
            $this->oprofile = Ostatus_profile::getActorProfile($this->activity);
            if (!$this->oprofile instanceof Ostatus_profile) {
                throw new UnknownUriException($this->activity->actor->id);
            }
        } catch (UnknownUriException $e) {
            // Apparently we didn't find the Profile object based on our URI,
            // so OStatus doesn't have it with this URI in ostatus_profile.
            // Try to look it up again, remote side may have changed from http to https
            // or maybe publish an acct: URI now instead of an http: URL.
            //
            // Steps:
            // 1. Check the newly received URI. Who does it say it is?
            // 2. Compare these alleged identities to our local database.
            // 3. If we found any locally stored identities, ask it about its aliases.
            // 4. Do any of the aliases from our known identity match the recently introduced one?
            //
            // Example: We have stored http://example.com/user/1 but this URI says https://example.com/user/1
            common_debug('No local Profile object found for a magicsigned activity author URI: '.$e->object_uri);
            $disco = new Discovery();
            $xrd = $disco->lookup($e->object_uri);
            // Step 1: We got a bunch of discovery data for https://example.com/user/1 which includes
            //         aliases https://example.com/user and hopefully our original http://example.com/user/1 too
            $all_ids = array_merge(array($xrd->subject), $xrd->aliases);

            if (!in_array($e->object_uri, $all_ids)) {
                common_debug('The activity author URI we got was not listed itself when doing discovery on it.');
                throw $e;
            }

            // Go through each reported alias from lookup to see if we know this already
            foreach ($all_ids as $aliased_uri) {
                $oprofile = Ostatus_profile::getKV('uri', $aliased_uri);
                if (!$oprofile instanceof Ostatus_profile) {
                    continue;   // unknown locally, check the next alias
                }
                // Step 2: We found the alleged http://example.com/user/1 URI in our local database,
                //         but this can't be trusted yet because anyone can publish any alias.
                common_debug('Found a local Ostatus_profile for "'.$e->object_uri.'" with this URI: '.$aliased_uri);

                // We found an existing OStatus profile, but is it really the same? Do a callback to the URI's origin
                // Step 3: lookup our previously known http://example.com/user/1 webfinger etc.
                $xrd = $disco->lookup($oprofile->getUri()); // getUri returns ->uri, which we filtered on earlier
                $doublecheck_aliases = array_merge(array($xrd->subject), $xrd->aliases);
                common_debug('Trying to match known "'.$aliased_uri.'" against its returned aliases: '.implode(' ', $doublecheck_aliases));
                // if we find our original URI here, it is a legitimate alias
                // Step 4: Is the newly introduced https://example.com/user/1 URI in the list of aliases
                //         presented by http://example.com/user/1 (i.e. do they both say they are the same identity?)
                if (in_array($e->object_uri, $doublecheck_aliases)) {
                    $oprofile->updateUriKeys($e->object_uri, DiscoveryHints::fromXRD($xrd));
                    $this->oprofile = $oprofile;
                    break;  // don't iterate through aliases anymore
                }
            }

            // We might end up here after $all_ids is iterated through without a $this->oprofile value,
            if (!$this->oprofile instanceof Ostatus_profile) {
                common_debug("We do not have a local profile to connect to this activity's author. Let's create one.");
                // ensureActivityObjectProfile throws exception on failure
                $this->oprofile = Ostatus_profile::ensureActivityObjectProfile($this->activity->actor);
            }
        }

        assert($this->oprofile instanceof Ostatus_profile);

        $this->actor = $this->oprofile->localProfile();
    }

    function saveNotice()
    {
        if (!$this->oprofile instanceof Ostatus_profile) {
            common_debug('Ostatus_profile missing in ' . get_class(). ' profile: '.var_export($this->profile, true));
        }
        return $this->oprofile->processPost($this->activity, 'salmon');
    }
}
