<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * @license   https://www.gnu.org/licenses/agpl.html 
 */

if (!defined('POSTACTIV')) { exit(1); }

class SubscriptionListItem extends ProfileListItem
{
    /** Owner of this list */
    var $owner = null;

    function __construct(Profile $profile, $owner, $action)
    {
        parent::__construct($profile, $action);

        $this->owner = $owner;
    }

    function showProfile()
    {
        $this->startProfile();
        $this->showAvatar($this->profile);
        $this->showNickname();
        $this->showFullName();
        $this->showLocation();
        $this->showHomepage();
        $this->showBio();
        // Relevant portion!
        $this->showTags();
        if ($this->isOwn()) {
            $this->showOwnerControls();
        }
        $this->endProfile();
    }

    function showOwnerControls()
    {
        // pass
    }

    function isOwn()
    {
        $user = common_current_user();
        return (!empty($user) && ($this->owner->id == $user->id));
    }
}
?>