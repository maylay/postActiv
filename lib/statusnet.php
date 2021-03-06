<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * @license   https://www.gnu.org/licenses/agpl.html
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Backwards compatible class for plugins for GNU social <1.2
 * and thus only have the class StatusNet defined.
 */
class StatusNet
{
    public static function getActivePlugins()
    {
        return postActiv::getActivePlugins();
    }

    public static function isHTTPS()
    {
        return postActiv::isHTTPS();
    }
}
?>