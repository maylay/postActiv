<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * PHP version 5
 *
 * @license   https://www.gnu.org/licenses/agpl.html 
 */

// @todo FIXME: add documentation.

if (!defined('POSTACTIV')) { exit(1); }

class GroupMemberList extends ProfileList
{
    var $group = null;

    function __construct($profile, $group, $action)
    {
        parent::__construct($profile, $action);

        $this->group = $group;
    }

    function newListItem(Profile $profile)
    {
        return new GroupMemberListItem($profile, $this->group, $this->action);
    }
}
?>