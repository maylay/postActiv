<?php
/* ============================================================================
 * Title: AtomPubMembershipFeed
 * Feed of group memberships for a user, in ActivityStreams format
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
 * Feed of group memberships for a user, in ActivityStreams format
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * @author    Evan Prodromou
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
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
 * Feed of group memberships for a user, in ActivityStreams format
 */
class AtompubmembershipfeedAction extends AtompubAction
{
    private $_profile     = null;
    private $_memberships = null;

    protected function atompubPrepare()
    {
        $this->_profile = Profile::getKV('id', $this->trimmed('profile'));

        if (!$this->_profile instanceof Profile) {
            // TRANS: Client exception.
            throw new ClientException(_('No such profile.'), 404);
        }

        $this->_memberships = Group_member::byMember($this->_profile->id,
                                                     $this->offset,
                                                     $this->limit);
        return true;
    }

    protected function handleGet()
    {
        return $this->showFeed();
    }

    protected function handlePost()
    {
        return $this->addMembership();
    }

    /**
     * Show a feed of favorite activity streams objects
     *
     * @return void
     */
    function showFeed()
    {
        header('Content-Type: application/atom+xml; charset=utf-8');

        $url = common_local_url('AtomPubMembershipFeed',
                                array('profile' => $this->_profile->id));

        $feed = new Atom10Feed(true);

        $feed->addNamespace('activity',
                            'http://activitystrea.ms/spec/1.0/');

        $feed->addNamespace('poco',
                            'http://portablecontacts.net/spec/1.0');

        $feed->addNamespace('media',
                            'http://purl.org/syndication/atommedia');

        $feed->id = $url;

        $feed->setUpdated('now');

        $feed->addAuthor($this->_profile->getBestName(),
                         $this->_profile->getURI());

        // TRANS: Title for group membership feed.
        // TRANS: %s is a username.
        $feed->setTitle(sprintf(_('Group memberships of %s'),
                                $this->_profile->getBestName()));

        // TRANS: Subtitle for group membership feed.
        // TRANS: %1$s is a username, %2$s is the StatusNet sitename.
        $feed->setSubtitle(sprintf(_('Groups %1$s is a member of on %2$s'),
                                   $this->_profile->getBestName(),
                                   common_config('site', 'name')));

        $feed->addLink(common_local_url('usergroups',
                                        array('nickname' =>
                                              $this->_profile->nickname)));

        $feed->addLink($url,
                       array('rel' => 'self',
                             'type' => 'application/atom+xml'));

        // If there's more...

        if ($this->page > 1) {
            $feed->addLink($url,
                           array('rel' => 'first',
                                 'type' => 'application/atom+xml'));

            $feed->addLink(common_local_url('AtomPubMembershipFeed',
                                            array('profile' =>
                                                  $this->_profile->id),
                                            array('page' =>
                                                  $this->page - 1)),
                           array('rel' => 'prev',
                                 'type' => 'application/atom+xml'));
        }

        if ($this->_memberships->N > $this->count) {

            $feed->addLink(common_local_url('AtomPubMembershipFeed',
                                            array('profile' =>
                                                  $this->_profile->id),
                                            array('page' =>
                                                  $this->page + 1)),
                           array('rel' => 'next',
                                 'type' => 'application/atom+xml'));
        }

        $i = 0;

        while ($this->_memberships->fetch()) {

            // We get one more than needed; skip that one

            $i++;

            if ($i > $this->count) {
                break;
            }

            $act = $this->_memberships->asActivity();
            $feed->addEntryRaw($act->asString(false, false, false));
        }

        $this->raw($feed->getString());
    }

    /**
     * add a new favorite
     *
     * @return void
     */
    function addMembership()
    {
        // XXX: Refactor this; all the same for atompub

        if (empty($this->auth_user) ||
            $this->auth_user->id != $this->_profile->id) {
            // TRANS: Client exception thrown when trying subscribe someone else to a group.
            throw new ClientException(_("Cannot add someone else's".
                                        " membership."), 403);
        }

        $xml = file_get_contents('php://input');

        $dom = DOMDocument::loadXML($xml);

        if ($dom->documentElement->namespaceURI != Activity::ATOM ||
            $dom->documentElement->localName != 'entry') {
            // TRANS: Client error displayed when not using an Atom entry.
            throw new ClientException(_('Atom post must be an Atom entry.'));
            return;
        }

        $activity = new Activity($dom->documentElement);

        $membership = null;

        if (Event::handle('StartAtomPubNewActivity', array(&$activity))) {
            if ($activity->verb != ActivityVerb::JOIN) {
                // TRANS: Client error displayed when not using the join verb.
                throw new ClientException(_('Can only handle join activities.'));
            }

            $groupObj = $activity->objects[0];

            if ($groupObj->type != ActivityObject::GROUP) {
                // TRANS: Client exception thrown when trying to join something which is not a group
                throw new ClientException(_('Can only join groups.'));
            }

            $group = User_group::getKV('uri', $groupObj->id);

            if (empty($group)) {
                // XXX: import from listed URL or something
                // TRANS: Client exception thrown when trying to subscribe to a non-existing group.
                throw new ClientException(_('Unknown group.'));
            }

            $old = Group_member::pkeyGet(array('profile_id' => $this->auth_user->id,
                                               'group_id' => $group->id));

            if (!empty($old)) {
                // TRANS: Client exception thrown when trying to subscribe to an already subscribed group.
                throw new ClientException(_('Already a member.'));
            }

            $profile = $this->auth_user->getProfile();

            if (Group_block::isBlocked($group, $profile)) {
                // XXX: import from listed URL or something
                // TRANS: Client exception thrown when trying to subscribe to group while blocked from that group.
                throw new ClientException(_('Blocked by admin.'));
            }

            $this->auth_user->joinGroup($group);

            Event::handle('EndAtomPubNewActivity', array($activity, $membership));
        }

        if (!empty($membership)) {
            $act = $membership->asActivity();

            header('Content-Type: application/atom+xml; charset=utf-8');
            header('Content-Location: ' . $act->selfLink);

            $this->startXML();
            $this->raw($act->asString(true, true, true));
            $this->endXML();
        }
    }

    /**
     * Return last modified, if applicable.
     *
     * MAY override
     *
     * @return string last modified http header
     */
    function lastModified()
    {
        // For comparison with If-Last-Modified
        // If not applicable, return null
        return null;
    }

    /**
     * Return etag, if applicable.
     *
     * MAY override
     *
     * @return string etag http header
     */
    function etag()
    {
        return null;
    }
}

// END OF FILE
// ============================================================================
?>