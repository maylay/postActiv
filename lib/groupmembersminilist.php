<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * PHP version 5
 */

if (!defined('POSTACTIV')) { exit(1); }

class GroupMembersMiniList extends ProfileMiniList
{
    function newListItem(Profile $profile)
    {
        return new GroupMembersMiniListItem($profile, $this->action);
    }
}
?>