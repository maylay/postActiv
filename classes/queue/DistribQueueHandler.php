<?php
/* ============================================================================
 * Title: DistribQueueHandler
 * Transport that handles federation of notices.
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
 * As extensions of the Daemon class, each queue handler has the ability
 * to launch itself in the background, at which point it'll pass control
 * to the configured QueueManager class to poll for updates.
 *
 * Subclasses must override at least the following methods:
 * - transport
 * - handle_notice
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Brion Vibber <brion@pobox.com>
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

class DistribQueueHandler
{
    // ------------------------------------------------------------------------
    // Function: transport
    // Return transport keyword which identifies items this queue handler
    // services; must be defined for all subclasses.
    //
    // Must be 8 characters or less to fit in the queue_item database.
    // ex "email", "jabber", "sms", "irc", ...
    //
    // @return string
    //
    public function transport()
    {
        return 'distrib';
    }

    // ------------------------------------------------------------------------
    // Function: handle
    //
    // Handle distribution of a notice after we've saved it:
    // o add to local recipient inboxes
    // o send email notifications to local @-reply targets
    // o run final EndNoticeSave plugin events
    // o put any remaining post-processing into the queues
    //
    // If this function indicates failure, a warning will be logged
    // and the item is placed back in the queue to be re-run.
    //
    // Parameters:
    // o Notice $notice
    //
    // Returns:
    // o boolean - true on success, false on failure
    public function handle(Notice $notice)
    {
        // We have to manually add attentions to non-profile subs and non-mentions
       $ptAtts = $notice->getAttentionsFromProfileTags();
        foreach (array_keys($ptAtts) as $profile_id) {
            $profile = Profile::getKV('id', $profile_id);
            if ($profile instanceof Profile) {
                try {
                    common_debug('Adding Attention for '.$notice->getID().' profile '.$profile->getID());
                    Attention::saveNew($notice, $profile);
                } catch (Exception $e) {
                    $this->logit($notice, $e);
                }
            }
        }

        try {
            $notice->sendReplyNotifications();
        } catch (Exception $e) {
            $this->logit($notice, $e);
        }

        try {
            Event::handle('EndNoticeDistribute', array($notice));
        } catch (Exception $e) {
            $this->logit($notice, $e);
        }

        try {
            Event::handle('EndNoticeSave', array($notice));
        } catch (Exception $e) {
            $this->logit($notice, $e);
        }

        try {
            // Enqueue for other handlers
            common_enqueue_notice($notice);
        } catch (Exception $e) {
            $this->logit($notice, $e);
        }

        return true;
    }

    // ------------------------------------------------------------------------
    // Function: logit
    // Log an exception we come across in handling federation
    protected function logit($notice, $e)
    {
        common_log(LOG_ERR, "Distrib queue exception saving notice $notice->id: " .
            $e->getMessage() . ' ' .
            str_replace("\n", " ", $e->getTraceAsString()));

        // We'll still return true so we don't get stuck in a loop
        // trying to run a bad insert over and over...
    }
}
?>