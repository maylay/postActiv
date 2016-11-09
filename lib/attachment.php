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
 * widget for displaying a list of notice attachments
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
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * used for one-off attachment action
 */
class Attachment extends AttachmentListItem
{
    function showNoticeAttachment() {
        if (Event::handle('StartShowAttachmentLink', array($this->out, $this->attachment))) {
            $this->out->elementStart('div', array('id' => 'attachment_view',
                                                  'class' => 'h-entry'));
            $this->out->elementStart('div', 'entry-title');
            $this->out->element('a', $this->linkAttr(), _('Download link'));
            $this->out->elementEnd('div');

            $this->out->elementStart('article', 'e-content');
            $this->showRepresentation();
            $this->out->elementEnd('article');
            Event::handle('EndShowAttachmentLink', array($this->out, $this->attachment));
            $this->out->elementEnd('div');
        }
    }

    function show() {
        $this->showNoticeAttachment();
    }

    function linkAttr() {
        return array('rel' => 'external', 'href' => $this->attachment->getUrl());
    }
}
?>