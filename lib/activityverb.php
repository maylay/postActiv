<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * PHP version 5
 *
 * An activity
 *
 * @category  ActivityStreams
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Zach Copley
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Utility class to hold a bunch of constant defining default verb types
 */
class ActivityVerb
{
    const POST     = 'http://activitystrea.ms/schema/1.0/post';
    const SHARE    = 'http://activitystrea.ms/schema/1.0/share';
    const SAVE     = 'http://activitystrea.ms/schema/1.0/save';
    const FAVORITE = 'http://activitystrea.ms/schema/1.0/favorite';
    const LIKE     = 'http://activitystrea.ms/schema/1.0/like'; // This is a synonym of favorite
    const PLAY     = 'http://activitystrea.ms/schema/1.0/play';
    const FOLLOW   = 'http://activitystrea.ms/schema/1.0/follow';
    const FRIEND   = 'http://activitystrea.ms/schema/1.0/make-friend';
    const JOIN     = 'http://activitystrea.ms/schema/1.0/join';
    const TAG      = 'http://activitystrea.ms/schema/1.0/tag';
    const DELETE   = 'http://activitystrea.ms/schema/1.0/delete';
    const UPDATE   = 'http://activitystrea.ms/schema/1.0/update';

    // Custom OStatus verbs for the flipside until they're standardized
    const UNFAVORITE = 'http://activitystrea.ms/schema/1.0/unfavorite';
    const UNLIKE     = 'http://activitystrea.ms/schema/1.0/unlike'; // This is a synonym of unfavorite
    const UNFOLLOW   = 'http://ostatus.org/schema/1.0/unfollow';
    const LEAVE      = 'http://ostatus.org/schema/1.0/leave';
    const UNTAG      = 'http://ostatus.org/schema/1.0/untag';

    // For simple profile-update pings; no content to share.
    const UPDATE_PROFILE = 'http://ostatus.org/schema/1.0/update-profile';

    static function canonical($verb) {
        $ns = 'http://activitystrea.ms/schema/1.0/';
        if (substr($verb, 0, mb_strlen($ns)) == $ns) {
            return substr($verb, mb_strlen($ns));
        } else {
            return $verb;
        }
    }
}
?>