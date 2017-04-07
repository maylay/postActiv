<?php
/* ============================================================================
 * Title: Unavailable_status_network
 * Data class for unavailable status networks
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
 * Data class for unavailable status networks
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mikael Nordfeldth <mmn@hethane.se>
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
 * Keeps a list of unavailable status network names
 *
 * @see      Managed_DataObject
 */

class Unavailable_status_network extends Managed_DataObject
{
    public $__table = 'unavailable_status_network'; // table name

    public $nickname;              // varchar(64) UUID
    public $created;               // datetime

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'An unavailable status network nickname',
            'fields' => array(
                'nickname' => array('type' => 'varchar',
                                    'length' => 64,
                                    'not null' => true, 'description' => 'nickname not to use'),
                'created' => array('type' => 'datetime',
                                   'not null' => true),
            ),
            'primary key' => array('nickname'),
        );
    }
}

// END OF FILE
// ============================================================================
?>