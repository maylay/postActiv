<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Geocode action class
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
 * @author    Craig Andrews <candrews@integralblue.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

/**
 * Geocode action class
 */
class GeocodeAction extends Action
{
    var $lat = null;
    var $lon = null;
    var $location = null;

    function prepare(array $args = array())
    {
        parent::prepare($args);
        $token = $this->trimmed('token');
        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token. '.
                                 'Try again, please.'));
        }
        $this->lat = $this->trimmed('lat');
        $this->lon = $this->trimmed('lon');
        $this->location = Location::fromLatLon($this->lat, $this->lon);
        return true;
    }

    /**
     * Class handler
     *
     * @param array $args query arguments
     *
     * @return nothing
     *
     */
    function handle()
    {
        header('Content-Type: application/json; charset=utf-8');
        $location_object = array();
        $location_object['lat']=$this->lat;
        $location_object['lon']=$this->lon;
        if($this->location) {
            $location_object['location_id']=$this->location->location_id;
            $location_object['location_ns']=$this->location->location_ns;
            $location_object['name']=$this->location->getName();
            $location_object['url']=$this->location->getUrl();
        }
        print(json_encode($location_object));
    }

    /**
     * Is this action read-only?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }
}
?>