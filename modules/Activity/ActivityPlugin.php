<?php
/* ============================================================================
 * Title: ActivityPlugin
 * Shows social activities in the output feed
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
 * ----------------------------------------------------------------------------
 * About:
 * Shows social activities in the output feed
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Eric Helgeson <erichelgeson@gmail.com>
 * o Brenda Wallace <shiny@cpan.org>
 * o Sean Corbett <sean@gnu.org>
 * o James Walker <walkah@walkah.net>
 * o Jeroen De Dauw <jeroendedauw@gmail.com>
 * o Max Shinn <trombonechamp@gmail.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Ian Denhardt <ian@zenhack.net>
 * o flyingmana <flyingmana@googlemail.com>
 * o Sashi Gowda <connect2shashi@gmail.com>
 * o Jordan Conway <jordan@conway.name>
 * o Dan Scott <dan@coffeecode.net>
 * o Antonin Kral <a.kral@bobek.cz>
 * o Luke Fitzegerald <lw.fitzgerald@googlemail.com>
 * o Scott Sweeny <ssweeny@gmail.com>
 * o Samantha Doherty <samantha@doherty.name>
 * o Zach Copley <zach@copley.name>
 * o Brion Vibber <brion@pobox.com>
 * o Michele Azzolari <macno@macno.org>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Joshua Wise <jwise@nvidia.com>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Thomas Johnson <NTmatter@gmail.com>
 * o Emily O'leary <emily@oleary.name>
 * o Brian Tegtmeier <btegtmeier@gmail.com>
 * o Florian Schmaus <flo@geekplace.eu>
 * o Jean Baptiste Favre <github@jbfavre.org>
 * o Florian H�lsmann <fh@cbix.de>
 * o Vinilox <vinilox@vinilox.eu>
 * o Marcel van der Boom <marcel@hsdev.com>
 * o Rob Myers <rob@robmyers.org>
 * o Matt Lee <mattl@creativecommons.org>
 * o Mats Sj�berg <mats@sjoberg.fi>
 * o Aqeel Zafar <aqeel@aqeeliz.com>
 * o Jeremy Malcolm <jeremy@ciroap.org>
 * o Stanislav N. <pztrn@pztrn.name>
 * o Joshua Judson Rosen <rozzin@geekspace.com>
 * o Antonio Roquentin <antonio.roquentin@sfr.fr>
 * o Adam Moore <laemeur@sdf.org>
 * o Chris Buttle <chris@gatopaleo.org>
 * o abjectio <abjectio@kollektivet0x242.no>
 * o chimo <chimo@chromic.org>
 * o Marcus Moeller <marcus.moeller@gmx.ch>
 * o Bhuvan Krishna <bhuvan@swecha.net>
 * o Hannes Mannerheim <h@nnesmannerhe.im>
 * o digital-dreamer <digitaldreamer@email.>
 * o Stephen Paul Weber <singpolyma@singpolyma.net>
 * o Matthias Fritzsche <txt.file@txtfile.eu>
 * o Akio Nishimura <akio@akionux.net>
 * o Guillaume Hayot <postblue+git@postblue.info>
 * o Roland Haeder <roland@mxchange.net>
 * o Carlos Sanbu <carsanbu@entramado.net>
 * o Bob Mottram <bob@robotics.uk.to>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class ActivityPlugin extends Plugin
{
    const VERSION = '0.1';
    const SOURCE  = 'activity';

    // Flags to switch off certain activity notices
    public $StartFollowUser = true;
    public $StopFollowUser  = false;
    public $JoinGroup = true;
    public $LeaveGroup = false;
    public $StartLike = false;
    public $StopLike = false;

    function onEndSubscribe(Profile $profile, Profile $other)
    {
        // Only do this if config is enabled
        if(!$this->StartFollowUser) return true;

        if (!$profile->isLocal()) {
            // can't do anything with remote user anyway
            return true;
        }

        $sub = Subscription::pkeyGet(array('subscriber' => $profile->id,
                                           'subscribed' => $other->id));
        // TRANS: Text for "started following" item in activity plugin.
        // TRANS: %1$s is a profile URL, %2$s is a profile name,
        // TRANS: %3$s is a profile URL, %4$s is a profile name.
        $rendered = html_sprintf(_m('<a href="%1$s">%2$s</a> started following <a href="%3$s">%4$s</a>.'),
                            $profile->getUrl(),
                            $profile->getBestName(),
                            $other->getUrl(),
                            $other->getBestName());
        // TRANS: Text for "started following" item in activity plugin.
        // TRANS: %1$s is a profile name, %2$s is a profile URL,
        // TRANS: %3$s is a profile name, %4$s is a profile URL.
        $content  = sprintf(_m('%1$s (%2$s) started following %3$s (%4$s).'),
                            $profile->getBestName(),
                            $profile->getUrl(),
                            $other->getBestName(),
                            $other->getUrl());

        $notice = Notice::saveNew($profile->id,
                                  $content,
                                  ActivityPlugin::SOURCE,
                                  array('rendered' => $rendered,
                                        'urls' => array(),
                                        'replies' => array($other->getUri()),
                                        'verb' => ActivityVerb::FOLLOW,
                                        'object_type' => ActivityObject::PERSON,
                                        'uri' => $sub->uri));
        return true;
    }

    function onEndUnsubscribe(Profile $profile, Profile $other)
    {
        // Only do this if config is enabled
        if(!$this->StopFollowUser) return true;

        if (!$profile->isLocal()) {
            return true;
        }

        // TRANS: Text for "stopped following" item in activity plugin.
        // TRANS: %1$s is a profile URL, %2$s is a profile name,
        // TRANS: %3$s is a profile URL, %4$s is a profile name.
        $rendered = html_sprintf(_m('<a href="%1$s">%2$s</a> stopped following <a href="%3$s">%4$s</a>.'),
                            $profile->getUrl(),
                            $profile->getBestName(),
                            $other->getUrl(),
                            $other->getBestName());
        // TRANS: Text for "stopped following" item in activity plugin.
        // TRANS: %1$s is a profile name, %2$s is a profile URL,
        // TRANS: %3$s is a profile name, %4$s is a profile URL.
        $content  = sprintf(_m('%1$s (%2$s) stopped following %3$s (%4$s).'),
                            $profile->getBestName(),
                            $profile->getUrl(),
                            $other->getBestName(),
                            $other->getUrl());

        $uri = TagURI::mint('stop-following:%d:%d:%s',
                            $profile->id,
                            $other->id,
                            common_date_iso8601(common_sql_now()));

        $notice = Notice::saveNew($profile->id,
                                  $content,
                                  ActivityPlugin::SOURCE,
                                  array('rendered' => $rendered,
                                        'urls' => array(),
                                        'replies' => array($other->getUri()),
                                        'uri' => $uri,
                                        'verb' => ActivityVerb::UNFOLLOW,
                                        'object_type' => ActivityObject::PERSON));

        return true;
    }

    function onEndDisfavorNotice($profile, $notice)
    {
        // Only do this if config is enabled
        if(!$this->StopLike) return true;

        if (!$profile->isLocal()) {
            return true;
        }

        $author = Profile::getKV('id', $notice->profile_id);
        // TRANS: Text for "stopped liking" item in activity plugin.
        // TRANS: %1$s is a profile URL, %2$s is a profile name,
        // TRANS: %3$s is a notice URL, %4$s is an author name.
        $rendered = html_sprintf(_m('<a href="%1$s">%2$s</a> stopped liking <a href="%3$s">%4$s\'s update</a>.'),
                            $profile->getUrl(),
                            $profile->getBestName(),
                            $notice->getUrl(),
                            $author->getBestName());
        // TRANS: Text for "stopped liking" item in activity plugin.
        // TRANS: %1$s is a profile name, %2$s is a profile URL,
        // TRANS: %3$s is an author name, %4$s is a notice URL.
        $content  = sprintf(_m('%1$s (%2$s) stopped liking %3$s\'s status (%4$s).'),
                            $profile->getBestName(),
                            $profile->getUrl(),
                            $author->getBestName(),
                            $notice->getUrl());

        $uri = TagURI::mint('unlike:%d:%d:%s',
                            $profile->id,
                            $notice->id,
                            common_date_iso8601(common_sql_now()));

        $notice = Notice::saveNew($profile->id,
                                  $content,
                                  ActivityPlugin::SOURCE,
                                  array('rendered' => $rendered,
                                        'urls' => array(),
                                        'replies' => array($author->getUri()),
                                        'uri' => $uri,
                                        'verb' => ActivityVerb::UNFAVORITE,
                                        'object_type' => (($notice->verb == ActivityVerb::POST) ?
                                                         $notice->object_type : null)));

        return true;
    }

    function onEndJoinGroup($group, $profile)
    {
        // Only do this if config is enabled
        if(!$this->JoinGroup) return true;

        if (!$profile->isLocal()) {
            return true;
        }

        // TRANS: Text for "joined group" item in activity plugin.
        // TRANS: %1$s is a profile URL, %2$s is a profile name,
        // TRANS: %3$s is a group URL, %4$s is a group name.
        $rendered = html_sprintf(_m('<a href="%1$s">%2$s</a> joined the group <a href="%3$s">%4$s</a>.'),
                            $profile->getUrl(),
                            $profile->getBestName(),
                            $group->homeUrl(),
                            $group->getBestName());
        // TRANS: Text for "joined group" item in activity plugin.
        // TRANS: %1$s is a profile name, %2$s is a profile URL,
        // TRANS: %3$s is a group name, %4$s is a group URL.
        $content  = sprintf(_m('%1$s (%2$s) joined the group %3$s (%4$s).'),
                            $profile->getBestName(),
                            $profile->getUrl(),
                            $group->getBestName(),
                            $group->homeUrl());

        $mem = Group_member::pkeyGet(array('group_id' => $group->id,
                                           'profile_id' => $profile->id));

        $notice = Notice::saveNew($profile->id,
                                  $content,
                                  ActivityPlugin::SOURCE,
                                  array('rendered' => $rendered,
                                        'urls' => array(),
                                        'groups' => array($group->id),
                                        'uri' => $mem->getURI(),
                                        'verb' => ActivityVerb::JOIN,
                                        'object_type' => ActivityObject::GROUP));
        return true;
    }

    function onEndLeaveGroup($group, $profile)
    {
        // Only do this if config is enabled
        if(!$this->LeaveGroup) return true;

        if (!$profile->isLocal()) {
            return true;
        }

        // TRANS: Text for "left group" item in activity plugin.
        // TRANS: %1$s is a profile URL, %2$s is a profile name,
        // TRANS: %3$s is a group URL, %4$s is a group name.
        $rendered = html_sprintf(_m('<a href="%1$s">%2$s</a> left the group <a href="%3$s">%4$s</a>.'),
                            $profile->getUrl(),
                            $profile->getBestName(),
                            $group->homeUrl(),
                            $group->getBestName());
        // TRANS: Text for "left group" item in activity plugin.
        // TRANS: %1$s is a profile name, %2$s is a profile URL,
        // TRANS: %3$s is a group name, %4$s is a group URL.
        $content  = sprintf(_m('%1$s (%2$s) left the group %3$s (%4$s).'),
                            $profile->getBestName(),
                            $profile->getUrl(),
                            $group->getBestName(),
                            $group->homeUrl());

        $uri = TagURI::mint('leave:%d:%d:%s',
                            $profile->id,
                            $group->id,
                            common_date_iso8601(common_sql_now()));

        $notice = Notice::saveNew($profile->id,
                                  $content,
                                  ActivityPlugin::SOURCE,
                                  array('rendered' => $rendered,
                                        'urls' => array(),
                                        'groups' => array($group->id),
                                        'uri' => $uri,
                                        'verb' => ActivityVerb::LEAVE,
                                        'object_type' => ActivityObject::GROUP));
        return true;
    }

    function onStartShowNoticeItem($nli)
    {
        $notice = $nli->notice;

        $adapter = null;

        switch ($notice->verb) {
        case ActivityVerb::JOIN:
            $adapter = new JoinListItem($nli);
            break;
        case ActivityVerb::LEAVE:
            $adapter = new LeaveListItem($nli);
            break;
        case ActivityVerb::FOLLOW:
            $adapter = new FollowListItem($nli);
            break;
        case ActivityVerb::UNFOLLOW:
            $adapter = new UnfollowListItem($nli);
            break;
        }

        if (!empty($adapter)) {
            $adapter->showNotice();
            $adapter->showNoticeAttachments();
            $adapter->showNoticeInfo();
            $adapter->showNoticeOptions();
            return false;
        }

        return true;
    }

    public function onEndNoticeAsActivity(Notice $stored, Activity $act, Profile $scoped=null)
    {
        switch ($stored->verb) {
        case ActivityVerb::UNFAVORITE:
            // FIXME: do something here
            break;
        case ActivityVerb::JOIN:
            $mem = Group_member::getKV('uri', $stored->getUri());
            if ($mem instanceof Group_member) {
                $group = $mem->getGroup();
                $act->title = $stored->getTitle();
                $act->objects = array(ActivityObject::fromGroup($group));
            }
            break;
        case ActivityVerb::LEAVE:
            // FIXME: ????
            break;
        case ActivityVerb::FOLLOW:
            $sub = Subscription::getKV('uri', $stored->uri);
            if ($sub instanceof Subscription) {
                $profile = Profile::getKV('id', $sub->subscribed);
                if ($profile instanceof Profile) {
                    $act->title = $stored->getTitle();
                    $act->objects = array($profile->asActivityObject());
                }
            }
            break;
        case ActivityVerb::UNFOLLOW:
            // FIXME: ????
            break;
        }

        return true;
    }

    function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'Activity',
                            'version' => self::VERSION,
                            'author' => 'Evan Prodromou',
                            'homepage' => 'https://git.gnu.io/gnu/gnu-social/tree/master/plugins/Activity',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Emits notices when social activities happen.'));
        return true;
    }
}
?>