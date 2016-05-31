<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('POSTACTIV')) { exit(1); }

class ThreadingGroupNoticeStream extends ThreadingNoticeStream
{
    function __construct($group, Profile $scoped=null)
    {
        parent::__construct(new GroupNoticeStream($group, $scoped));
    }
}
?>