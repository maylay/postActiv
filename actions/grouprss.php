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
 * Group main page
 *
 * @category  Group
 * @package   postActiv
 * @author    Robin Millette <robin@millette.info>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Jeffery To <jeffery.to@gmail.com>
 * @author    Zach Copley <zach@copley.name>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('POSTACTIV')) { exit(1); }

define('MEMBERS_PER_SECTION', 27);

/**
 * Group RSS feed
 */
class GroupRssAction extends TargetedRss10Action
{
    /** group we're viewing. */
    protected $group = null;

    /**
     * Is this page read-only?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    protected function doStreamPreparation()
    {

        $nickname_arg = $this->arg('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            common_redirect(common_local_url('showgroup', $args), 301);
        }

        if (!$nickname) {
            // TRANS: Client error displayed when requesting a group RSS feed without providing a group nickname.
            $this->clientError(_('No nickname.'), 404);
        }

        $local = Local_group::getKV('nickname', $nickname);

        if (!$local instanceof Local_group) {
            // TRANS: Client error displayed when requesting a group RSS feed for group that does not exist.
            $this->clientError(_('No such group.'), 404);
        }

        $this->group = $local->getGroup();
        $this->target = $this->group->getProfile();
    }

    protected function getNotices()
    {
        $stream = $this->group->getNotices(0, $this->limit);
        return $stream->fetchAll();
    }

    function getChannel()
    {
        $c = array('url' => common_local_url('grouprss',
                                             array('nickname' =>
                                                   $this->target->getNickname())),
                   // TRANS: Message is used as link title. %s is a user nickname.
                   'title' => sprintf(_('%s timeline'), $this->target->getNickname()),
                   'link' => common_local_url('showgroup', array('nickname' => $this->target->getNickname())),
                   // TRANS: Message is used as link description. %1$s is a group name, %2$s is a site name.
                   'description' => sprintf(_('Updates from members of %1$s on %2$s!'),
                                            $this->target->getNickname(), common_config('site', 'name')));
        return $c;
    }

    function getImage()
    {
        return $this->group->homepage_logo;
    }
}
?>