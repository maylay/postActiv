<?php
/* ============================================================================
 * Title: TagRSS
 * List a RSS feed for a tag
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
 * List a RSS feed for a tag
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Robin Millette <robin@millette.info>
 * o Jeffery To <jeffery.to@gmail.com>
 * o Zack Copley
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

class TagrssAction extends Rss10Action
{
    protected $tag;

    protected function doStreamPreparation()
    {
        $tag = common_canonical_tag($this->trimmed('tag'));
        $this->tag = Notice_tag::getKV('tag', $tag);
        if (!$this->tag instanceof Notice_tag) {
            // TRANS: Client error when requesting a tag feed for a non-existing tag.
            $this->clientError(_('No such tag.'));
        }
    }

    protected function getNotices()
    {
        $stream = Notice_tag::getStream($this->tag->tag)->getNotices(0, $this->limit);
        return $stream->fetchAll();
    }

    function getChannel()
    {
        $tagname = $this->tag->tag;
        $c = array('url' => common_local_url('tagrss', array('tag' => $tagname)),
               'title' => $tagname,
               'link' => common_local_url('tagrss', array('tag' => $tagname)),
               // TRANS: Tag feed description.
               // TRANS: %1$s is the tag name, %2$s is the StatusNet sitename.
               'description' => sprintf(_('Updates tagged with %1$s on %2$s!'),
                                        $tagname, common_config('site', 'name')));
        return $c;
    }

    function isReadOnly($args)
    {
        return true;
    }
}

// END OF FILE
// ============================================================================
?>