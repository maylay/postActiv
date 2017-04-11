<?php
/* ============================================================================
 * Title: NoticeSearchRSS
 * RSS feed for notice search action class.
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2017, Maiyannah Bishop
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
 * RSS feed for notice search action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Robin Millette <robin@millette.info>
 * o Jeffrey To <jeffery.to@gmail.com>
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

/**
 * RSS feed for notice search action class.
 */
class NoticesearchrssAction extends Rss10Action
{
    protected function getNotices()
    {
        $q = $this->trimmed('q');
        $notices = array();

        $notice = new Notice();

        $search_engine = $notice->getSearchEngine('notice');
        $search_engine->set_sort_mode('chron');

        $search_engine->limit(0, $this->limit, true);
        if (false === $search_engine->query($q)) {
            $cnt = 0;
        } else {
            $cnt = $notice->find();
        }

        if ($cnt > 0) {
            while ($notice->fetch()) {
                $notices[] = clone($notice);
            }
        }

        return $notices;
    }

    function getChannel()
    {
        $q = $this->trimmed('q');
        $c = array('url' => common_local_url('noticesearchrss', array('q' => $q)),
                   // TRANS: RSS notice search feed title. %s is the query.
                   'title' => sprintf(_('Updates with "%s"'), $q),
                   'link' => common_local_url('noticesearch', array('q' => $q)),
                   // TRANS: RSS notice search feed description.
                   // TRANS: %1$s is the query, %2$s is the StatusNet site name.
                   'description' => sprintf(_('Updates matching search term "%1$s" on %2$s.'),
                                            $q, common_config('site', 'name')));
        return $c;
    }

    function getImage()
    {
        return null;
    }

    function isReadOnly($args)
    {
        return true;
    }
}

// END OF FILE
// ============================================================================
?>