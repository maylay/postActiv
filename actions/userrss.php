<?php
/* ============================================================================
 * Title: UserRSS
 * Code to handle serving up user RSS feeds.
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
 * Code to handle serving up user RSS feeds.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Robin Millette <robin@millette.info>
 * o Adrian Lang <mail@adrianlang.de>
 * o Jeffrey To <jeffery.to@gmail.com>
 * o Zach Copley
 * o Christopher Vollick <psycotica0@gmail.com>
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

// Formatting of RSS handled by Rss10Action

class UserrssAction extends TargetedRss10Action
{
    protected $tag = null;

    protected function doStreamPreparation()
    {
        parent::doStreamPreparation();

        $this->tag  = $this->trimmed('tag');
    }

    protected function getNotices()
    {
        if (!empty($this->tag)) {
            $stream = $this->getTarget()->getTaggedNotices($this->tag, 0, $this->limit);
            return $stream->fetchAll();
        }
        // otherwise we fetch a normal user stream

        $stream = $this->getTarget()->getNotices(0, $this->limit);
        return $stream->fetchAll();
    }

    function getChannel()
    {
        $c = array('url' => common_local_url('userrss',
                                             array('nickname' =>
                                                   $this->target->getNickname())),
                   // TRANS: Message is used as link title. %s is a user nickname.
                   'title' => sprintf(_('%s timeline'), $this->target->getNickname()),
                   'link' => $this->target->getUrl(),
                   // TRANS: Message is used as link description. %1$s is a username, %2$s is a site name.
                   'description' => sprintf(_('Updates from %1$s on %2$s!'),
                                            $this->target->getNickname(), common_config('site', 'name')));
        return $c;
    }

    // override parent to add X-SUP-ID URL

    function initRss()
    {
        $url = common_local_url('sup', null, null, $this->target->getID());
        header('X-SUP-ID: '.$url);
        parent::initRss();
    }

    function isReadOnly($args)
    {
        return true;
    }
}
?>