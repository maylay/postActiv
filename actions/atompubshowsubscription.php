<?php
/* ============================================================================
 * Title: AtomPubShowSubscription
 * Single subscription
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 * Single subscription
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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


/**
 * Show a single subscription
 */
class AtompubshowsubscriptionAction extends AtompubAction
{
    private $_subscriber   = null;
    private $_subscribed   = null;
    private $_subscription = null;

    protected function atompubPrepare()
    {
        $subscriberId = $this->trimmed('subscriber');

        $this->_subscriber = Profile::getKV('id', $subscriberId);

        if (!$this->_subscriber instanceof Profile) {
            // TRANS: Client exception thrown when trying to display a subscription for a non-existing profile ID.
            // TRANS: %d is the non-existing profile ID number.
            throw new ClientException(sprintf(_('No such profile id: %d.'),
                                              $subscriberId), 404);
        }

        $subscribedId = $this->trimmed('subscribed');

        $this->_subscribed = Profile::getKV('id', $subscribedId);

        if (!$this->_subscribed instanceof Profile) {
            // TRANS: Client exception thrown when trying to display a subscription for a non-existing profile ID.
            // TRANS: %d is the non-existing profile ID number.
            throw new ClientException(sprintf(_('No such profile id: %d.'),
                                              $subscribedId), 404);
        }

        $this->_subscription = Subscription::pkeyGet(array('subscriber' => $subscriberId,
                                                           'subscribed' => $subscribedId));
        if (!$this->_subscription instanceof Subscription) {
            // TRANS: Client exception thrown when trying to display a subscription for a non-subscribed profile ID.
            // TRANS: %1$d is the non-existing subscriber ID number, $2$d is the ID of the profile that was not subscribed to.
            $msg = sprintf(_('Profile %1$d not subscribed to profile %2$d.'),
                           $subscriberId, $subscribedId);
            throw new ClientException($msg, 404);
        }

        return true;
    }

    protected function handleGet()
    {
        $this->showSubscription();
    }

    protected function handleDelete()
    {
        $this->deleteSubscription();
    }

    /**
     * Show the subscription in ActivityStreams Atom format.
     *
     * @return void
     */
    function showSubscription()
    {
        $activity = $this->_subscription->asActivity();

        header('Content-Type: application/atom+xml; charset=utf-8');

        $this->startXML();
        $this->raw($activity->asString(true, true, true));
        $this->endXML();
    }

    /**
     * Delete the subscription
     *
     * @return void
     */
    function deleteSubscription()
    {
        if (!$this->scoped instanceof Profile ||
                $this->scoped->id != $this->_subscriber->id) {
            // TRANS: Client exception thrown when trying to delete a subscription of another user.
            throw new ClientException(_("Cannot delete someone else's subscription."), 403);
        }

        Subscription::cancel($this->_subscriber, $this->_subscribed);
    }

    /**
     * Is this action read only?
     *
     * @param array $args other arguments
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            return false;
        }

        return true;
    }

    /**
     * Return last modified, if applicable.
     *
     * @return string last modified http header
     */
    function lastModified()
    {
        return max(strtotime($this->_subscriber->modified),
                   strtotime($this->_subscribed->modified),
                   strtotime($this->_subscription->modified));
    }

    /**
     * Etag for this object
     *
     * @return string etag http header
     */
    function etag()
    {
        $mtime = strtotime($this->_subscription->modified);

        return 'W/"' . implode(':', array('AtomPubShowSubscription',
                                          $this->_subscriber->id,
                                          $this->_subscribed->id,
                                          $mtime)) . '"';
    }

    /**
     * Does this require authentication?
     *
     * @return boolean true if delete, else false
     */
    function requiresAuth()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            return true;
        } else {
            return false;
        }
    }
}

// END OF FILE
// ============================================================================
?>