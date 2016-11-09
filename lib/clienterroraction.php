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
 * Client error action.
 * Class for displaying HTTP client errors
 *
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Zach Copley
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

class ClientErrorAction extends ErrorAction
{
    static $status = array(400 => 'Bad Request',
                           401 => 'Unauthorized',
                           402 => 'Payment Required',
                           403 => 'Forbidden',
                           404 => 'Not Found',
                           405 => 'Method Not Allowed',
                           406 => 'Not Acceptable',
                           407 => 'Proxy Authentication Required',
                           408 => 'Request Timeout',
                           409 => 'Conflict',
                           410 => 'Gone',
                           411 => 'Length Required',
                           412 => 'Precondition Failed',
                           413 => 'Request Entity Too Large',
                           414 => 'Request-URI Too Long',
                           415 => 'Unsupported Media Type',
                           416 => 'Requested Range Not Satisfiable',
                           417 => 'Expectation Failed');

    function __construct($message='Error', $code=400)
    {
        parent::__construct($message, $code);
        $this->default = 400;

        if (!$this->code || $this->code < 400 || $this->code > 499) {
            $this->code = $this->default;
        }
        if (!$this->message) {
            $this->message = "Client Error $this->code";
        }
    }

    /**
     *  To specify additional HTTP headers for the action
     *
     *  @return void
     */
    function extraHeaders()
    {
        $status_string = @self::$status[$this->code];
        header('HTTP/1.1 '.$this->code.' '.$status_string);
    }

    /**
     * Page title.
     *
     * @return page title
     */

    function title()
    {
        return @self::$status[$this->code];
    }
}
?>