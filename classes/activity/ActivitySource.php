<?php
/* ============================================================================
 * Title: ActivitySource
 * Class abstraction for Activity verb source
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
 * An activity verb in class form, and the related scaffolding.
 *
 * This file also now consolidates the ActivityContext, ActivityImporter,
 * ActivityMover, ActivitySink, and ActivitySource classes, formerly at
 * /lib/<class>.php
 *
 * o Activity abstracts the class for an activity verb.
 * o ActivityContext contains information of the context of the activity verb.
 * o ActivityImporter abstracts a means that is importing activity verbs
 *   into the system as part of a user's timeline.
 * o ActivityMover abstracts the means to transport activity verbs.
 * o ActivitySink abstracts a class to receive activity verbs.
 * o ActivitySource abstracts a class to represent the source of a received
 *    activity verb.
 *
 * ActivityObject is a noun in the activity universe basically, from
 * the original file:
 *     A noun-ish thing in the activity universe
 *
 *     The activity streams spec talks about activity objects, while also
 *     having a tag activity:object, which is in fact an activity object.
 *     Aaaaaah!
 *
 *     This is just a thing in the activity universe. Can be the subject,
 *     object, or indirect object (target!) of an activity verb. Rotten
 *     name, and I'm propagating it. *sigh*
 * It's large enough that I've left it seperate in activityobject.php
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Brion Vibber <brion@pobox.com>
 * o James Walker <walkah@walkah.net>
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chimo <chimo@chromic.org>
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
// Class: ActivitySource
// Class abstraction of an Activity's source
//
// Variables:
// o id
// o title
// o icon
// o updated
// o links
class ActivitySource
{
    public $id;
    public $title;
    public $icon;
    public $updated;
    public $links;
}

// END OF FILE
// ============================================================================
?>