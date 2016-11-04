<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 * Superclass for system event items
 *
 * @category  Activity
 * @package   StatusNet
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * NoticeListItemAdapter for system activities
 */
class SystemListItem extends NoticeListItemAdapter
{
    /**
     * Show the activity
     *
     * @return void
     */
    function showNotice()
    {
        $out = $this->nli->out;
        $out->elementStart('div', 'entry-title');
        $this->showContent();
        $out->elementEnd('div');
    }

    function showContent()
    {
        $notice = $this->nli->notice;
        $out    = $this->nli->out;

        // FIXME: get the actual data on the leave

        $out->elementStart('div', 'system-activity');

        $out->raw($notice->getRendered());

        $out->elementEnd('div');
    }

    function showNoticeOptions()
    {
        if (Event::handle('StartShowNoticeOptions', array($this))) {
            $user = common_current_user();
            if (!empty($user)) {
                $this->nli->out->elementStart('div', 'notice-options');
                if (Event::handle('StartShowNoticeOptionItems', array($this))) {
                    $this->showReplyLink();
                    Event::handle('EndShowNoticeOptionItems', array($this));
                }
                $this->nli->out->elementEnd('div');
            }
            Event::handle('EndShowNoticeOptions', array($this));
        }
    }
}
