<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('POSTACTIV')) { exit(1); }

class Peopletag extends PeopletagListItem
{
    protected $avatarSize = AVATAR_PROFILE_SIZE;

    function showStart()
    {
        $mode = $this->peopletag->private ? 'private' : 'public';
        $this->out->elementStart('div', array('class' => 'h-entry peopletag peopletag-profile mode-'.$mode,
                                             'id' => 'peopletag-' . $this->peopletag->id));
    }

    function showEnd()
    {
        $this->out->elementEnd('div');
    }
}
?>