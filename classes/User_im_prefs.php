<?php
/* ============================================================================
 * Title: Location Preferences
 * Class to hold a user's instant messaging preferences
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016, Maiyannah Bishop
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
 * Data class for user IM preferences
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Craig Andrews <candrews@integralblue.com>
 * o Brion Vibber <brion@pobox.com>
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

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

// -----------------------------------------------------------------------------
// Class: User_im_prefs
// Class to hold user IM preferences
//
// Variables:
// o __table
// o user_id
// o screenname
// o transport
// o notify
// o replies
// o updatefrompresence
// o created
// o modified
class User_im_prefs extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_im_prefs';       // table name
    public $user_id;                         // int(4)  primary_key not_null
    public $screenname;                      // varchar(191)  not_null   not 255 because utf8mb4 takes more space
    public $transport;                       // varchar(191)  not_null   not 255 because utf8mb4 takes more space
    public $notify;                          // tinyint(1)
    public $replies;                         // tinyint(1)
    public $updatefrompresence;              // tinyint(1)
    public $created;                         // datetime   not_null default_0000-00-00%2000%3A00%3A00
    public $modified;                        // timestamp   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns the schema definition for this class
   //
   // Returns:
   // o array
   public static function schemaDef()
   {
      return array(
         'fields' => array(
            'user_id' => array('type' => 'int', 'not null' => true, 'description' => 'user'),
            'screenname' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'screenname on this service'),
            'transport' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'transport (ex xmpp, aim)'),
            'notify' => array('type' => 'int', 'size' => 'tiny', 'not null' => true, 'default' => 0, 'description' => 'Notify when a new notice is sent'),
            'replies' => array('type' => 'int', 'size' => 'tiny', 'not null' => true, 'default' => 0, 'description' => 'Send replies  from people not subscribed to'),
            'updatefrompresence' => array('type' => 'int', 'size' => 'tiny', 'not null' => true, 'default' => 0, 'description' => 'Send replies from people not subscribed to.'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
         ),
         'primary key' => array('user_id', 'transport'),
         'unique keys' => array(
            'transport_screenname_key' => array('transport', 'screenname'),
         ),
         'foreign keys' => array(
            'user_im_prefs_user_id_fkey' => array('user', array('user_id' => 'id')),
         ),
      );
   }
}
// END OF FILE
// ============================================================================
?>