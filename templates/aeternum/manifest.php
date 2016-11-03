<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 * Manifest for the Aeternum template.  Essentially, this file tells postActiv
 * which files to load for which router paths.
 *
 * @category  Templates
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

$aeternum = new Template;
$aeternum->mapCompileDir(AETERNUM_PATH . "/templates_c/");
$aeternum->mapTemplate("single_notice", AETERNUM_PATH . "/templates/notice.tpl");
?>