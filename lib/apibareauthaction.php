<?php
/* ============================================================================
 * Title: APIBareAuthAction
 * Actions extending this class will require auth unless a target
 * user ID has been specified
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
 * Actions extending this class will require auth unless a target
 * user ID has been specified
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Adrian Lang <mail@adrianlang.de>
 * o Brenda Wallace <shiny@cpan.org>
 * o Craig Andrews <candrews@integralblue.com>
 * o Dan Moore <dan@moore.cx>
 * o Evan Prodromou
 * o mEDI <medi@milaro.net>
 * o Sarven Capadisli
 * o Zach Copley
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
 * Actions extending this class will require auth unless a target
 * user ID has been specified
 */
class ApiBareAuthAction extends ApiAuthAction
{
    /**
     * Does this API resource require authentication?
     *
     * @return boolean true or false
     */
    function requiresAuth()
    {
        // If the site is "private", all API methods except statusnet/config
        // need authentication
        if (common_config('site', 'private')) {
            return true;
        }

        // check whether a user has been specified somehow
        if (!$this->arg('id') && !$this->arg('user_id')
                && mb_strlen($this->arg('screen_name'))===0) {
            return true;
        }

        return false;
    }
}

// END OF FILE
// ============================================================================
?>