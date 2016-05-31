<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Parent class for an exception when a POST upload does not contain a file.
 *
 * @category Exception
 * @package  GNUsocial
 * @author   Mikael Nordfeldth <mmn@hethane.se>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPLv3
 * @link     http://gnu.io/
 */

if (!defined('GNUSOCIAL')) { exit(1); }

class NoUploadedMediaException extends ClientException
{
    public $fieldname = null;

    public function __construct($fieldname, $msg=null)
    {
        $this->fieldname = $fieldname;

        if ($msg === null) {
            // TRANS: Exception text shown when no uploaded media was provided in POST
            // TRANS: %s is the HTML input field name.
            $msg = sprintf(_('There is no uploaded media for input field "%s".'), $this->fieldname);
        }

        parent::__construct($msg, 400);
    }
}
