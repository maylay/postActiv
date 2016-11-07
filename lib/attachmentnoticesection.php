<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * FIXME
 * These are the widgets that show interesting data about a person * group, or site.
 *
 * @category  Widget
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2009-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

class AttachmentNoticeSection extends NoticeSection
{
    function showContent() {
        parent::showContent();
        return false;
    }

    function getNotices()
    {
        $notice = new Notice;

        $notice->joinAdd(array('id', 'file_to_post:post_id'));
        $notice->whereAdd(sprintf('file_to_post.file_id = %d', $this->out->attachment->id));

        $notice->orderBy('created desc');
        $notice->selectAdd('post_id as id');
        $notice->find();
        return $notice;
    }

    function title()
    {
        // TRANS: Title.
        return _('Notices where this attachment appears');
    }

    function divId()
    {
        return 'attachment_section';
    }
}
?>