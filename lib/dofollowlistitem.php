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
 * widget for displaying a list of notices
 *
 * @category  UI
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2010 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

require_once INSTALLDIR.'/lib/noticelist.php';

/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * Widget superclass for notice list items that remove rel=nofollow
 *
 * When nofollow|external = 'sometimes', notices get rendered and saved
 * with rel=nofollow for external links. We want to remove that relationship
 * on some pages (profile, single notice, faves). This superclass for
 * some noticelistitems will strip that bit of code out when showing
 * notice content
 *
 * @category  UI
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2010 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPLv3
 * @link      http://status.net/
 */

class DoFollowListItem extends NoticeListItem
{
    /**
     * show the content of the notice
     *
     * Trims out the rel=nofollow for external links
     * if nofollow|external = 'sometimes'
     *
     * @return void
     */

    function showContent()
    {
        // FIXME: URL, image, video, audio
        $this->out->elementStart('article', array('class' => 'e-content'));

        $html = $this->notice->getRendered();

        if (common_config('nofollow', 'external') == 'sometimes') {
            // remove the nofollow part
            // XXX: cache the results here

            $html = preg_replace('/rel="(.*)nofollow ?/', 'rel="\1', $html);
        }

        $this->out->raw($html);

        $this->out->elementEnd('div');
    }
}
?>