<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Widget for displaying a list of notice attachments
 *
 * There are a number of actions that display a list of notices, in
 * reverse chronological order. This widget abstracts out most of the
 * code for UI for notice lists. It's overridden to hide some
 * data for e.g. the profile page.
 *
 * @category  UI
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Sarven Capadisli
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 *
 * @see      Notice
 * @see      NoticeListItem
 * @see      ProfileNoticeList
 * ============================================================================
 */

if (!defined('POSTACTIV')) { exit(1); }

class AttachmentList extends Widget
{
    /** the current stream of notices being displayed. */

    var $notice = null;

    /**
     * constructor
     *
     * @param Notice $notice stream of notices from DB_DataObject
     */
    function __construct(Notice $notice, $out=null)
    {
        parent::__construct($out);
        $this->notice = $notice;
    }

    /**
     * show the list of attachments
     *
     * "Uses up" the stream by looping through it. So, probably can't
     * be called twice on the same list.
     *
     * @return int count of items listed.
     */
    function show()
    {
    	$attachments = $this->notice->attachments();
        foreach ($attachments as $key=>$att) {
            // Remove attachments which are not representable with neither a title nor thumbnail
            if ($att->getTitle() === null && !$att->hasThumbnail()) {
                unset($attachments[$key]);
            }
        }
        if (!count($attachments)) {
            return 0;
        }

        if ($this->notice->getProfile()->isSilenced()) {
            // TRANS: Message for inline attachments list in notices when the author has been silenced.
            $this->element('div', ['class'=>'error'], _('Attachments are hidden because this profile has been silenced.'));
            return 0;
        }

        $this->showListStart();

        foreach ($attachments as $att) {
            $item = $this->newListItem($att);
            $item->show();
        }

        $this->showListEnd();

        return count($attachments);
    }

    function showListStart()
    {
        $this->out->elementStart('ol', array('class' => 'attachments'));
    }

    function showListEnd()
    {
        $this->out->elementEnd('ol');
    }

    /**
     * returns a new list item for the current attachment
     *
     * @param File $attachment the current attachment
     *
     * @return AttachmentListItem a list item for displaying the attachment
     */
    function newListItem(File $attachment)
    {
        return new AttachmentListItem($attachment, $this->out);
    }
}
?>