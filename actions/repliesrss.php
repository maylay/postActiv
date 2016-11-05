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
 * @category  RSS
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Ciaran Gultneiks <ciaran@ciarang.com>
 * @author    Robin Millette <robin@millette.info>
 * @author    Jeffery To <jeffery.to@gmail.com>
 * @author    Zach Copley
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

// Formatting of RSS handled by Rss10Action

class RepliesrssAction extends TargetedRss10Action
{
    protected function getNotices()
    {
        $stream = $this->target->getReplies(0, $this->limit);
        return $stream->fetchAll();
    }

    function getChannel()
    {
        $c = array('url' => common_local_url('repliesrss',
                                             array('nickname' =>
                                                   $this->target->getNickname())),
                   // TRANS: RSS reply feed title. %s is a user nickname.
                   'title' => sprintf(_("Replies to %s"), $this->target->getNickname()),
                   'link' => common_local_url('replies',
                                              array('nickname' => $this->target->getNickname())),
                   // TRANS: RSS reply feed description.
                   // TRANS: %1$s is a user nickname, %2$s is the StatusNet site name.
                   'description' => sprintf(_('Replies to %1$s on %2$s.'),
                                              $this->target->getNickname(), common_config('site', 'name')));
        return $c;
    }

    function isReadOnly($args)
    {
        return true;
    }
}
?>