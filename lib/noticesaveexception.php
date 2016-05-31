<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Base exception class for when a notice cannot be saved
 *
 * @category Exception
 * @package  GNUsocial
 * @author   Mikael Nordfeldth <mmn@hethane.se>
 * @license  https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     https://gnu.io/social
 */

if (!defined('POSTACTIV')) { exit(1); }

class NoticeSaveException extends ServerException
{
}
?>