<?php
/* ============================================================================
 * Title: APIGroupListAll
 * Show the newest groups
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
 * Show the newest groups
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Evan Prodromou
 * o Robin Millette <robin@millette.info>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Hannes Mannerheim <h@nnesmannerhe.im>
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
 * Returns of the lastest 20 groups for the site
 */
class ApiGroupListAllAction extends ApiPrivateAuthAction
{
    var $groups   = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        $this->user   = $this->getTargetUser(null);
        $this->groups = $this->getGroups();

        return true;
    }

    /**
     * Handle the request
     *
     * Show the user's groups
     *
     * @return void
     */
    function handle()
    {
        parent::handle();

        $sitename   = common_config('site', 'name');
        // TRANS: Message is used as a title when listing the lastest 20 groups. %s is a site name.
        $title      = sprintf(_("%s groups"), $sitename);
        $taguribase = TagURI::base();
        $id         = "tag:$taguribase:Groups";
        $link       = common_local_url('groups');
        // TRANS: Message is used as a subtitle when listing the latest 20 groups. %s is a site name.
        $subtitle   = sprintf(_("groups on %s"), $sitename);

        switch($this->format) {
        case 'xml':
            $this->showXmlGroups($this->groups);
            break;
        case 'rss':
            $this->showRssGroups($this->groups, $title, $link, $subtitle);
            break;
        case 'atom':
            $selfuri = common_root_url() .
                'api/statusnet/groups/list_all.atom';
            $this->showAtomGroups(
                $this->groups,
                $title,
                $id,
                $link,
                $subtitle,
                $selfuri
            );
            break;
        case 'json':
            $this->showJsonGroups($this->groups);
            break;
        default:
            $this->clientError(
                // TRANS: Client error displayed when coming across a non-supported API method.
                _('API method not found.'),
                404,
                $this->format
            );
            break;
        }
    }

    /**
     * Get groups
     *
     * @return array groups
     */
    function getGroups()
    {
        $qry = 'SELECT user_group.* '.
          'from user_group join local_group on user_group.id = local_group.group_id '.
          'order by created desc ';
        $offset = intval($this->page - 1) * intval($this->count);
        $limit = intval($this->count);
        if (common_config('db', 'type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
        }
        $group = new User_group();

        $group->query($qry);

        $groups = array();
        while ($group->fetch()) {
            $groups[] = clone($group);
        }

        return $groups;
    }

    /**
     * Is this action read only?
     *
     * @param array $args other arguments
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    /**
     * When was this feed last modified?
     *
     * @return string datestamp of the site's latest group
     */
    function lastModified()
    {
        if (!empty($this->groups) && (count($this->groups) > 0)) {
            return strtotime($this->groups[0]->created);
        }

        return null;
    }

    /**
     * An entity tag for this list of groups
     *
     * Returns an Etag based on the action name, language, and
     * timestamps of the first and last group the user has joined
     *
     * @return string etag
     */
    function etag()
    {
        if (!empty($this->groups) && (count($this->groups) > 0)) {

            $last = count($this->groups) - 1;

            return '"' . implode(
                ':',
                array($this->arg('action'),
                      common_user_cache_hash($this->auth_user),
                      common_language(),
                      strtotime($this->groups[0]->created),
                      strtotime($this->groups[$last]->created))
            )
            . '"';
        }

        return null;
    }
}

// END OF FILE
// ============================================================================
?>