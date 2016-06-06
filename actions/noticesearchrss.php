<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * PHP version 5
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
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Robin Millette <millette@status.net>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('GNUSOCIAL')) { exit(1); }

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
?>