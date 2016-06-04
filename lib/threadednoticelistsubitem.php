<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('POSTACTIV')) { exit(1); }

// @todo FIXME: needs documentation.
class ThreadedNoticeListSubItem extends NoticeListItem
{
    protected $root = null;

    function __construct(Notice $notice, $root, $out)
    {
        $this->root = $root;
        parent::__construct($notice, $out);
    }

    function avatarSize()
    {
        return AVATAR_STREAM_SIZE; // @fixme would like something in between
    }

    function showNoticeLocation()
    {
        //
    }

    function showNoticeSource()
    {
        //
    }

    function getAttentionProfiles()
    {
        $all = parent::getAttentionProfiles();

        $profiles = array();

        $rootAuthor = $this->root->getProfile();

        foreach ($all as $profile) {
            if ($profile->id != $rootAuthor->id) {
                $profiles[] = $profile;
            }
        }

        return $profiles;
    }

    function showEnd()
    {
        $threadActive = null;   // unused here for now, but maybe in the future?
        if (Event::handle('StartShowThreadedNoticeTailItems', array($this, $this->notice, &$threadActive))) {
            // Repeats and Faves/Likes are handled in plugins.
            Event::handle('EndShowThreadedNoticeTailItems', array($this, $this->notice, &$threadActive));
        }
        parent::showEnd();
    }
}
