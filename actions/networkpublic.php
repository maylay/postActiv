<?php
/* ============================================================================
 * Title: NetworkPublic
 * Network timeline
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
 *
 * Derived from code copyright various sources:
 * o GNU Social (C) 2013-2016, Free Software Foundation, Inc
 * o StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
 * License:
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
 * <https://www.gnu.org/licenses/agpl.html>
 * ----------------------------------------------------------------------------
 * About:
 * Network timeline
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

class NetworkpublicAction extends SitestreamAction
{
    protected function streamPrepare()
    {
        if (!$this->scoped instanceof Profile && common_config('public', 'localonly')) {
            $this->clientError(_('Network wide public feed is not permitted without authorization'), 403);
        }
        if ($this->scoped instanceof Profile && $this->scoped->isLocal() && $this->scoped->getUser()->streamModeOnly()) {
            $this->stream = new NetworkPublicNoticeStream($this->scoped);
        } else {
            $this->stream = new ThreadingNetworkPublicNoticeStream($this->scoped);
        }
    }

    function title()
    {
        if ($this->page > 1) {
            // TRANS: Title for all public timeline pages but the first.
            // TRANS: %d is the page number.
            return sprintf(_('Network public timeline, page %d'), $this->page);
        } else {
            // TRANS: Title for the first public timeline page.
            return _('Network public timeline');
        }
    }

    function showSections()
    {
        // Show invite button, as long as site isn't closed, and
        // we have a logged in user.
        if (common_config('invite', 'enabled') && !common_config('site', 'closed') && common_logged_in()) {
            if (!common_config('site', 'private')) {
                $ibs = new InviteButtonSection(
                    $this,
                    // TRANS: Button text for inviting more users to the StatusNet instance.
                    // TRANS: Less business/enterprise-oriented language for public sites.
                    _m('BUTTON', 'Send invite')
                );
            } else {
                $ibs = new InviteButtonSection($this);
            }
            $ibs->show();
        }

        // Network public tag cloud?
    }

    function getFeeds()
    {
        return array(new Feed(Feed::JSON,
                              common_local_url('ApiTimelineNetworkPublic',
                                               array('format' => 'as')),
                              // TRANS: Link description for the _global_ network public timeline feed.
                              _('Network Public Timeline Feed (Activity Streams JSON)')),
                    new Feed(Feed::RSS1, common_local_url('publicrss'),
                              // TRANS: Link description for the _global_ network public timeline feed.
                              _('Network Public Timeline Feed (RSS 1.0)')),
                     new Feed(Feed::RSS2,
                              common_local_url('ApiTimelineNetworkPublic',
                                               array('format' => 'rss')),
                              // TRANS: Link description for the _global_ network public timeline feed.
                              _('Network Public Timeline Feed (RSS 2.0)')),
                     new Feed(Feed::ATOM,
                              common_local_url('ApiTimelineNetworkPublic',
                                               array('format' => 'atom')),
                              // TRANS: Link description for the _global_ network public timeline feed.
                              _('Network Public Timeline Feed (Atom)')));
    }
}

// END OF FILE
// ============================================================================
?>