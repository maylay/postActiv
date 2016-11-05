<?php
/****
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 *
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
 * PHP version 5
 *
 * Shows social activities in the output feed
 *
 * @category  Activity
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Eric Helgeson <erichelgeson@gmail.com>
 * @author    Brenda Wallace <shiny@cpan.org>
 * @author    Sean Corbett <sean@gnu.org>
 * @author    James Walker <walkah@walkah.net>
 * @author    Jeroen De Dauw <jeroendedauw@gmail.com>
 * @author    Max Shinn <trombonechamp@gmail.com>
 * @author    Craig Andrews <candrews@integralblue.com>
 * @author    Ian Denhardt <ian@zenhack.net>
 * @author    flyingmana <flyingmana@googlemail.com>
 * @author    Sashi Gowda <connect2shashi@gmail.com>
 * @author    Jordan Conway <jordan@conway.name>
 * @author    Dan Scott <dan@coffeecode.net>
 * @author    Antonin Kral <a.kral@bobek.cz>
 * @author    Luke Fitzegerald <lw.fitzgerald@googlemail.com>
 * @author    Scott Sweeny <ssweeny@gmail.com>
 * @author    Samantha Doherty <samantha@doherty.name>
 * @author    Zach Copley <zach@copley.name>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Michele Azzolari <macno@macno.org>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Joshua Wise <jwise@nvidia.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Thomas Johnson <NTmatter@gmail.com>
 * @author    Emily O'leary <emily@oleary.name>
 * @author    Brian Tegtmeier <btegtmeier@gmail.com>
 * @author    Florian Schmaus <flo@geekplace.eu>
 * @author    Jean Baptiste Favre <github@jbfavre.org>
 * @author    Florian Hülsmann <fh@cbix.de>
 * @author    Vinilox <vinilox@vinilox.eu>
 * @author    Marcel van der Boom <marcel@hsdev.com>
 * @author    Rob Myers <rob@robmyers.org>
 * @author    Matt Lee <mattl@creativecommons.org>
 * @author    Mats Sjöberg <mats@sjoberg.fi>
 * @author    Aqeel Zafar <aqeel@aqeeliz.com>
 * @author    Jeremy Malcolm <jeremy@ciroap.org>
 * @author    Stanislav N. <pztrn@pztrn.name>
 * @author    Joshua Judson Rosen <rozzin@geekspace.com>
 * @author    Antonio Roquentin <antonio.roquentin@sfr.fr>
 * @author    Adam Moore <laemeur@sdf.org>
 * @author    Chris Buttle <chris@gatopaleo.org>
 * @author    abjectio <abjectio@kollektivet0x242.no>
 * @author    chimo <chimo@chromic.org>
 * @author    Marcus Moeller <marcus.moeller@gmx.ch>
 * @author    Bhuvan Krishna <bhuvan@swecha.net>
 * @author    Hannes Mannerheim <h@nnesmannerhe.im>
 * @author    digital-dreamer <digitaldreamer@email.>
 * @author    Stephen Paul Weber <singpolyma@singpolyma.net>
 * @author    Matthias Fritzsche <txt.file@txtfile.eu>
 * @author    Akio Nishimura <akio@akionux.net>
 * @author    Guillaume Hayot <postblue+git@postblue.info>
 * @author    Roland Haeder <roland@mxchange.net>
 * @author    Carlos Sanbu <carsanbu@entramado.net>
 * @author    Bob Mottram <bob@robotics.uk.to>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
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
