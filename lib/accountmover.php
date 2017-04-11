<?php
/* ============================================================================
 * Title: AccountMover
 * A class for moving an account to a new server
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
 * A class for moving an account to a new server
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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

/**
 * Moves an account from this server to another
 */
class AccountMover extends QueueHandler
{
    function transport()
    {
        return 'acctmove';
    }

    function handle($object)
    {
        list($user, $remote, $password) = $object;

        $remote = Discovery::normalize($remote);

        $oprofile = Ostatus_profile::ensureProfileURI($remote);

        if (empty($oprofile)) {
            // TRANS: Exception thrown when an account could not be located when it should be moved.
            // TRANS: %s is the remote site.
            throw new Exception(sprintf(_("Cannot locate account %s."),$remote));
        }

        list($svcDocUrl, $username) = self::getServiceDocument($remote);

        $sink = new ActivitySink($svcDocUrl, $username, $password);

        $this->log(LOG_INFO,
                   "Moving user {$user->nickname} ".
                   "to {$remote}.");

        $stream = new UserActivityStream($user);

        // Reverse activities to run in correct chron order

        $acts = array_reverse($stream->activities);

        $this->log(LOG_INFO,
                   "Got ".count($acts)." activities ".
                   "for {$user->nickname}.");

        $qm = QueueManager::get();

        foreach ($acts as $act) {
            $qm->enqueue(array($act, $sink, $user->getUri(), $remote), 'actmove');
        }

        $this->log(LOG_INFO,
                   "Finished moving user {$user->nickname} ".
                   "to {$remote}.");
    }

    static function getServiceDocument($remote)
    {
        $discovery = new Discovery();

        $xrd = $discovery->lookup($remote);

        if (empty($xrd)) {
            // TRANS: Exception thrown when a service document could not be located account move.
            // TRANS: %s is the remote site.
            throw new Exception(sprintf(_("Cannot find XRD for %s."),$remote));
        }

        $svcDocUrl = null;
        $username  = null;

        $link = $xrd->links->get('http://apinamespace.org/atom', 'application/atomsvc+xml');
        if (!is_null($link)) {
            $svcDocUrl = $link->href;
            if (isset($link['http://apinamespace.org/atom/username'])) {
                $username = $link['http://apinamespace.org/atom/username'];
            }
        }

        if (empty($svcDocUrl)) {
            // TRANS: Exception thrown when an account could not be located when it should be moved.
            // TRANS: %s is the remote site.
            throw new Exception(sprintf(_("No AtomPub API service for %s."),$remote));
        }

        return array($svcDocUrl, $username);
    }

    /**
     * Log some data
     *
     * Add a header for our class so we know who did it.
     *
     * @param int    $level   Log level, like LOG_ERR or LOG_INFO
     * @param string $message Message to log
     *
     * @return void
     */
    protected function log($level, $message)
    {
        common_log($level, "AccountMover: " . $message);
    }
}

// END OF FILE
// ============================================================================
?>