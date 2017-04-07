<?php
/* ============================================================================
 * Title: DelUserQueueHandler
 * Background job to delete prolific users without disrupting front-end too much.
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
 * ----------------------------------------------------------------------------
 * About:
 * Background job to delete prolific users without disrupting front-end too much.
 *
 * Up to 50 messages are deleted on each run through; when all messages are gone,
 * the actual account is deleted.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Evan Prodromou
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
// Class: DelUserQueueHandler
// Class abstraction for the queue job of deleting a prolific user
//
// Defines:
// o DELETION_WINDOW - 50
class DelUserQueueHandler extends QueueHandler
{
    const DELETION_WINDOW = 50;

    // ------------------------------------------------------------------------
    // Function: transport
    public function transport()
    {
        return 'deluser';
    }

    // ------------------------------------------------------------------------
    // Function: handle
    //
    // Parameters:
    // o User $user
    public function handle($user)
    {
        if (!($user instanceof User)) {
            common_log(LOG_ERR, "Got a bogus user, not deleting");
            return true;
        }

        $user = User::getKV('id', $user->id);
        if (!$user) {
            common_log(LOG_INFO, "User {$user->nickname} was deleted before we got here.");
            return true;
        }

        try {
            if (!$user->hasRole(Profile_role::DELETED)) {
                common_log(LOG_INFO, "User {$user->nickname} is not pending deletion; aborting.");
                return true;
            }
        } catch (UserNoProfileException $unp) {
            common_log(LOG_INFO, "Deleting user {$user->nickname} with no profile... probably a good idea!");
        }

        $notice = $this->getNextBatch($user);
        if ($notice->N) {
            common_log(LOG_INFO, "Deleting next {$notice->N} notices by {$user->nickname}");
            while ($notice->fetch()) {
                $del = clone($notice);
                $del->delete();
            }

            // @todo improve reliability in case we died during the above deletions
            // with a fatal error. If the job is lost, we should perform some kind
            // of garbage collection later.

            // Queue up the next batch.
            $qm = QueueManager::get();
            $qm->enqueue($user, 'deluser');
        } else {
            // Out of notices? Let's finish deleting this profile!
            try {
                $user->getProfile()->delete();
            } catch (UserNoProfileException $e) {
                // in case a profile didn't exist for some reason, just delete the User directly
                $user->delete();
            }
            common_log(LOG_INFO, "User $user->id $user->nickname deleted.");
            return true;
        }

        return true;
    }

    // ------------------------------------------------------------------------
    // Function: getNextBatch
    // Fetch the next self::DELETION_WINDOW messages for this user.
    //
    // Parameters:
    // o User $user
    //
    // Returns:
    // o Notice
    protected function getNextBatch(User $user)
    {
        $notice = new Notice();
        $notice->profile_id = $user->id;
        $notice->limit(self::DELETION_WINDOW);
        $notice->find();
        return $notice;
    }

}
?>