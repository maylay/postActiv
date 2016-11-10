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
 * Code to handle serving up user RSS feeds.
 *
 * @category  Personal
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Robin Millette <robin@millette.info>
 * @author    Adrian Lang <mail@adrianlang.de>
 * @author    Jeffrey To <jeffery.to@gmail.com>
 * @author    Zach Copley
 * @author    Christopher Vollick <psycotica0@gmail.com>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================
 */

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