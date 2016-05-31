<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Personal tag cloud section
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  Widget
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Personal tag cloud section
 *
 * @category Widget
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class SubscriptionsPeopleTagCloudSection extends SubPeopleTagCloudSection
{
    function title()
    {
        // TRANS: Title of personal tag cloud section.
        return _('People Tagcloud as tagged');
    }

    function tagUrl($tag) {
        $nickname = $this->out->profile->nickname;
        return common_local_url('subscriptions', array('nickname' => $nickname, 'tag' => $tag));
    }

    function query() {
//        return 'select tag, count(tag) as weight from subscription left join profile_tag on subscriber=tagger and subscribed=tagged where subscriber=%d and subscriber != subscribed group by tag order by weight desc';
        return 'select profile_tag.tag, count(profile_tag.tag) as weight from subscription left join (profile_tag, profile_list) on subscriber=profile_tag.tagger and subscribed=tagged and profile_tag.tag = profile_list.tag and profile_tag.tagger = profile_list.tagger where subscriber=%d and subscriber != subscribed and profile_list.private = false and profile_tag.tag is not null group by profile_tag.tag order by weight desc';
    }
}
?>