<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('POSTACTIV')) { exit(1); }

// XXX SubscriptionsList and SubscriptionList are dangerously close

class SubscriptionsList extends SubscriptionList
{
    function newListItem(Profile $profile)
    {
        return new SubscriptionsListItem($profile, $this->owner, $this->action);
    }
}
?>