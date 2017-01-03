<?php
/* ============================================================================
 * Title: APIAtomService
 * An AtomPub service document for a user
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
 * An AtomPub service document for a user
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
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
 * Shows an AtomPub service document for a user
 */
class ApiAtomServiceAction extends ApiBareAuthAction
{
    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     *
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);
        $this->user = $this->getTargetUser($this->arg('id'));

        if (empty($this->user)) {
            // TRANS: Client error displayed when making an Atom API request for an unknown user.
            $this->clientError(_('No such user.'), 404);
        }

        return true;
    }

    /**
     * Handle the arguments. In our case, show a service document.
     *
     * @param Array $args unused.
     *
     * @return void
     */
    function handle()
    {
        parent::handle();

        header('Content-Type: application/atomsvc+xml');

        $this->startXML();
        $this->elementStart('service', array('xmlns' => 'http://www.w3.org/2007/app',
                                             'xmlns:atom' => 'http://www.w3.org/2005/Atom',
                                             'xmlns:activity' => 'http://activitystrea.ms/spec/1.0/'));
        $this->elementStart('workspace');
        // TRANS: Title for Atom feed.
        $this->element('atom:title', null, _m('ATOM','Main'));
        $this->elementStart('collection',
                            array('href' => common_local_url('ApiTimelineUser',
                                                             array('id' => $this->user->id,
                                                                   'format' => 'atom'))));
        $this->element('atom:title',
                       null,
                       // TRANS: Title for Atom feed. %s is a user nickname.
                       sprintf(_("%s timeline"),
                               $this->user->nickname));
        $this->element('accept', null, 'application/atom+xml;type=entry');
        $this->element('activity:verb', null, ActivityVerb::POST);
        $this->elementEnd('collection');
        $this->elementStart('collection',
                            array('href' => common_local_url('AtomPubSubscriptionFeed',
                                                             array('subscriber' => $this->user->id))));
        $this->element('atom:title',
                       null,
                       // TRANS: Title for Atom feed with a user's subscriptions. %s is a user nickname.
                       sprintf(_("%s subscriptions"),
                               $this->user->nickname));
        $this->element('accept', null, 'application/atom+xml;type=entry');
        $this->element('activity:verb', null, ActivityVerb::FOLLOW);
        $this->elementEnd('collection');
        $this->elementStart('collection',
                            array('href' => common_local_url('AtomPubFavoriteFeed',
                                                             array('profile' => $this->user->id))));
        $this->element('atom:title',
                       null,
                       // TRANS: Title for Atom feed with a user's favorite notices. %s is a user nickname.
                       sprintf(_("%s favorites"),
                               $this->user->nickname));
        $this->element('accept', null, 'application/atom+xml;type=entry');
        $this->element('activity:verb', null, ActivityVerb::FAVORITE);
        $this->elementEnd('collection');
        $this->elementStart('collection',
                            array('href' => common_local_url('AtomPubMembershipFeed',
                                                             array('profile' => $this->user->id))));
        $this->element('atom:title',
                       null,
                       // TRANS: Title for Atom feed with a user's memberships. %s is a user nickname.
                       sprintf(_("%s memberships"),
                               $this->user->nickname));
        $this->element('accept', null, 'application/atom+xml;type=entry');
        $this->element('activity:verb', null, ActivityVerb::JOIN);
        $this->elementEnd('collection');
        $this->elementEnd('workspace');
        $this->elementEnd('service');
        $this->endXML();
    }
}

// END OF FILE
// ============================================================================
?>