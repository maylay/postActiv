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
 * @category  API
 * @package   postActiv
 * @author    Adrian Lang <mail@adrianlang.de>
 * @author    Brenda Wallace <shiny@cpan.org>
 * @author    Craig Andrews <candrews@integralblue.com>
 * @author    Dan Moore <dan@moore.cx>
 * @author    Evan Prodromou
 * @author    mEDI <medi@milaro.net>
 * @author    Sarven Capadisli
 * @author    Zach Copley
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

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
?>