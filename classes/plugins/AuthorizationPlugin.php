<?php
/* ============================================================================
 * Title: AuthorizationPlugin
 * Superclass for plugins that do authorization
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
 * Superclass for plugins that do authorization
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Craig Andrews <candrews@integralblue.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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

abstract class AuthorizationPlugin extends Plugin
{
    //is this plugin authoritative for authorization?
    public $authoritative = false;

    //------------Auth plugin should implement some (or all) of these methods------------\\

    /**
    * Is a user allowed to log in?
    * @param user
    * @return boolean true if the user is allowed to login, false if explicitly not allowed to login, null if we don't explicitly allow or deny login
    */
    function loginAllowed($user) {
        return null;
    }

    /**
    * Does a profile grant the user a named role?
    * @param profile
    * @return boolean true if the profile has the role, false if not
    */
    function hasRole($profile, $name) {
        return false;
    }

    //------------Below are the methods that connect StatusNet to the implementing Auth plugin------------\\

    function onStartSetUser($user) {
        $loginAllowed = $this->loginAllowed($user);
        if($loginAllowed === true){
            return;
        }else if($loginAllowed === false){
            $user = null;
            return false;
        }else{
            if($this->authoritative) {
                $user = null;
                return false;
            }else{
                return;
            }
        }
    }

    function onStartSetApiUser($user) {
        return $this->onStartSetUser($user);
    }

    function onStartHasRole($profile, $name, &$has_role) {
        if($this->hasRole($profile, $name)){
            $has_role = true;
            return false;
        }else{
            if($this->authoritative) {
                $has_role = false;
                return false;
            }else{
                return;
            }
        }
    }
}

// END OF FILE
// ============================================================================
?>