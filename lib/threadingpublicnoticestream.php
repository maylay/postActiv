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

class ThreadingPublicNoticeStream extends ThreadingNoticeStream
{
    function __construct($scoped)
    {
        parent::__construct(new PublicNoticeStream($scoped));
    }
}
?>