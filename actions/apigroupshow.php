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
 * Show information about a group
 *
 * @category  API
 * @package   postActiv
 * @author    Zach Copley <zach@copley.name>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Robin Millette <robin@millette.info>
 * @author    Eric Helgeson <erichelgeson@gmail.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Michele Azzolari <macno@macno.org>
 * @author    Craig Andrews <candrews@integralblue.com>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Hannes Mannerheim <h@nnesmannerhe.im>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Outputs detailed information about the group specified by ID
 */
class ApiGroupShowAction extends ApiPrivateAuthAction
{
    var $group = null;

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

        $this->group = $this->getTargetGroup($this->arg('id'));

        if (empty($this->group)) {
            $alias = Group_alias::getKV(
                'alias',
                common_canonical_nickname($this->arg('id'))
            );
            if (!empty($alias)) {
                $args = array('id' => $alias->group_id, 'format' => $this->format);
                common_redirect(common_local_url('ApiGroupShow', $args), 301);
            } else {
                // TRANS: Client error displayed when trying to show a group that could not be found.
                $this->clientError(_('Group not found.'), 404);
            }
            return;
        }

        return true;
    }

    /**
     * Handle the request
     *
     * Check the format and show the user info
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        switch($this->format) {
        case 'xml':
            $this->showSingleXmlGroup($this->group);
            break;
        case 'json':
            $this->showSingleJsonGroup($this->group);
            break;
        default:
            // TRANS: Client error displayed when coming across a non-supported API method.
            $this->clientError(_('API method not found.'), 404);
        }
    }

    /**
     * When was this group last modified?
     *
     * @return string datestamp of the latest notice in the stream
     */
    function lastModified()
    {
        if (!empty($this->group)) {
            return strtotime($this->group->modified);
        }

        return null;
    }

    /**
     * An entity tag for this group
     *
     * Returns an Etag based on the action name, language, and
     * timestamps of the notice
     *
     * @return string etag
     */
    function etag()
    {
        if (!empty($this->group)) {

            return '"' . implode(
                ':',
                array($this->arg('action'),
                      common_user_cache_hash($this->auth_user),
                      common_language(),
                      $this->group->id,
                      strtotime($this->group->modified))
            )
            . '"';
        }

        return null;
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
?>