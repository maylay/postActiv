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
 * Show whether there is a friendship between two users
 *
 * @category  API
 * @package   postActiv
 * @author    Zach Copley
 * @author    Evan Prodromou
 * @author    Robin Millette <robin@millette.info>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Hannes Mannerheim <h@nnesmannerhe.im>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 * ============================================================================
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Tests for the existence of friendship between two users. Will return true if
 * user_a follows user_b, otherwise will return false.
 */
class ApiFriendshipsExistsAction extends ApiPrivateAuthAction
{
    var $profile_a = null;
    var $profile_b = null;

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

        $this->profile_a = $this->getTargetProfile($this->trimmed('user_a'));
        $this->profile_b = $this->getTargetProfile($this->trimmed('user_b'));

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

        if (empty($this->profile_a) || empty($this->profile_b)) {
            $this->clientError(
                // TRANS: Client error displayed when supplying invalid parameters to an API call checking if a friendship exists.
                _('Two valid IDs or nick names must be supplied.'),
                400
            );
        }

        $result = Subscription::exists($this->profile_a, $this->profile_b);

        switch ($this->format) {
        case 'xml':
            $this->initDocument('xml');
            $this->element('friends', null, $result);
            $this->endDocument('xml');
            break;
        case 'json':
            $this->initDocument('json');
            print json_encode($result);
            $this->endDocument('json');
            break;
        default:
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
?>