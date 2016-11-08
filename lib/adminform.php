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
 * Base class for administrative forms
 *
 * @category  Widget
 * @package   postActiv
 * @author    Zach Copley
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com> 
 * @copyright 2009-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

class AdminForm extends Form
{
    /**
     * Utility to simplify some of the duplicated code around
     * params and settings.
     *
     * @param string $setting      Name of the setting
     * @param string $title        Title to use for the input
     * @param string $instructions Instructions for this field
     * @param string $section      config section, default = 'site'
     *
     * @return void
     */
    function input($setting, $title, $instructions, $section='site')
    {
        $this->out->input($setting, $title, $this->value($setting, $section), $instructions);
    }

    /**
     * Utility to simplify getting the posted-or-stored setting value
     *
     * @param string $setting Name of the setting
     * @param string $main    configuration section, default = 'site'
     *
     * @return string param value if posted, or current config value
     */
    function value($setting, $main='site')
    {
        $value = $this->out->trimmed($setting);
        if (empty($value)) {
            $value = common_config($main, $setting);
        }
        return $value;
    }
}
?>