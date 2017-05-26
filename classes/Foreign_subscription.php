<?php
/* ============================================================================
 * Title: Foreign_subscription
 * Table Definition for foreign_subscription
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
 * Table Definition for foreign_subscription
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou <evan@prodromou.name>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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


// ============================================================================
// Class: Foreign_subscription
// Superclass representing the local record for a foreign user subscribing to
// a local one.
//
// Properties:
// o __table = 'foreign_subscription';            // table name
// o service    - int(4)  primary_key not_null
// o subscriber - int(4)  primary_key not_null
// o subscribed - int(4)  primary_key not_null
// o created    - datetime()   not_null
class Foreign_subscription extends Managed_DataObject {
   public $__table = 'foreign_subscription';            // table name
   public $service;                         // int(4)  primary_key not_null
   public $subscriber;                      // int(4)  primary_key not_null
   public $subscribed;                      // int(4)  primary_key not_null
   public $created;                         // datetime()   not_null


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the table schema in the DB.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'service' => array('type' => 'int', 'not null' => true, 'description' => 'service where relationship happens'),
            'subscriber' => array('type' => 'int', 'size' => 'big', 'not null' => true, 'description' => 'subscriber on foreign service'),
            'subscribed' => array('type' => 'int', 'size' => 'big', 'not null' => true, 'description' => 'subscribed user'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),),
         'primary key' => array('service', 'subscriber', 'subscribed'),
         'foreign keys' => array(
            'foreign_subscription_service_fkey' => array('foreign_service', array('service' => 'id')),
            'foreign_subscription_subscriber_fkey' => array('foreign_user', array('subscriber' => 'id', 'service' => 'service')),
            'foreign_subscription_subscribed_fkey' => array('foreign_user', array('subscribed' => 'id', 'service' => 'service')),),
          'indexes' => array(
            'foreign_subscription_subscriber_idx' => array('service', 'subscriber'),
            'foreign_subscription_subscribed_idx' => array('service', 'subscribed'),),);
    }
}

// END OF FILE
// ============================================================================
?>