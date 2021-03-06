<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Show most recent notices that are repeats in user's inbox
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  API
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Show most recent notices that are repeats in user's inbox
 *
 * @category API
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class ApiTimelineRetweetedToMeAction extends ApiAuthAction
{
    const DEFAULTCOUNT = 20;
    const MAXCOUNT     = 200;
    const MAXNOTICES   = 3200;

    var $repeats  = null;
    var $cnt      = self::DEFAULTCOUNT;
    var $page     = 1;
    var $since_id = null;
    var $max_id   = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        $cnt = $this->int('count', self::DEFAULTCOUNT, self::MAXCOUNT, 1);

        $page = $this->int('page', 1, (self::MAXNOTICES/$this->cnt));

        $since_id = $this->int('since_id');

        $max_id = $this->int('max_id');

        return true;
    }

    /**
     * Handle the request
     *
     * show a timeline of the user's repeated notices
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        $offset = ($this->page-1) * $this->cnt;
        $limit  = $this->cnt;

        // TRANS: Title for Atom feed "repeated to me". %s is the user nickname.
        $title      = sprintf(_("Repeated to %s"), $this->scoped->getNickname());
        $subtitle   = sprintf(
            // @todo FIXME: $profile is not defined.
            // TRANS: Subtitle for API action that shows most recent notices that are repeats in user's inbox.
            // TRANS: %1$s is the sitename, %2$s is a user nickname, %3$s is a user profile name.
            _('%1$s notices that were to repeated to %2$s / %3$s.'),
            $sitename, $this->scoped->getNickname(), $profile->getBestName()
        );
        $taguribase = TagURI::base();
        $id         = "tag:$taguribase:RepeatedToMe:" . $this->scoped->id;

        $link = common_local_url(
            'all',
             array('nickname' => $this->scoped->getNickname())
        );

        $strm = $this->scoped->repeatedToMe($offset, $limit, $this->since_id, $this->max_id);

        switch ($this->format) {
        case 'xml':
            $this->showXmlTimeline($strm);
            break;
        case 'json':
            $this->showJsonTimeline($strm);
            break;
        case 'atom':
            header('Content-Type: application/atom+xml; charset=utf-8');

            $atom = new AtomNoticeFeed($this->scoped->getUser());

            $atom->setId($id);
            $atom->setTitle($title);
            $atom->setSubtitle($subtitle);
            $atom->setUpdated('now');
            $atom->addLink($link);

            $id = $this->arg('id');

            $atom->setSelfLink($self);
            $atom->addEntryFromNotices($strm);

            $this->raw($atom->getString());

            break;
        case 'as':
            header('Content-Type: ' . ActivityStreamJSONDocument::CONTENT_TYPE);
            $doc = new ActivityStreamJSONDocument($this->scoped->getUser());
            $doc->setTitle($title);
            $doc->addLink($link, 'alternate', 'text/html');
            $doc->addItemsFromNotices($strm);
            $this->raw($doc->asString());
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), $code = 404);
            break;
        }
    }

    /**
     * Return true if read only.
     *
     * MAY override
     *
     * @param array $args other arguments
     *
     * @return boolean is read only action?
     */
    function isReadOnly($args)
    {
        return true;
    }
}
