<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Class for notice streams that does not filter anything out.
 *
 * PHP version 5
 */


if (!defined('POSTACTIV')) { exit(1); }

abstract class FullNoticeStream extends NoticeStream
{
    protected $selectVerbs = [];
}
?>