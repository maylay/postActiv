<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('POSTACTIV')) { exit(1); }

class SubQueueList extends ProfileList
{
    public function newListItem(Profile $target)
    {
        return new SubQueueListItem($target, $this->action);
    }
}
?>