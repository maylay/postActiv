<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Backwards compatible class for plugins for GNU social <1.2
 * and thus only have the class StatusNet defined.
 */
class StatusNet
{
    public static function getActivePlugins()
    {
        return GNUsocial::getActivePlugins();
    }

    public static function isHTTPS()
    {
        return GNUsocial::isHTTPS();
    }
}
