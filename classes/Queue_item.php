<?php
/* ============================================================================
 * Title: Queue_item
 * Table Definition for queue_item
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
 * Table Definition for queue_item
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Brion Vibber <brion@pobox.com>
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
// Class: Queue_item
// Superclass containing the representation of an queue item as held in the 
// database prior to being processed by the queue handler.
//
// Properties:
// o __table = 'queue_item' - table name
// o id        - int(4)  primary_key not_null
// o frame     - blob not_null
// o transport - varchar(32)
// o created   - datetime()   not_null
// o claimed   - datetime()
class Queue_item extends Managed_DataObject {
   public $__table = 'queue_item';                      // table name
   public $id;                              // int(4)  primary_key not_null
   public $frame;                           // blob not_null
   public $transport;                       // varchar(32)
   public $created;                         // datetime()   not_null
   public $claimed;                         // datetime()


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array with a representation of the table schema in the DB.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'serial', 'not null' => true, 'description' => 'unique identifier'),
            'frame' => array('type' => 'blob', 'not null' => true, 'description' => 'data: object reference or opaque string'),
            'transport' => array('type' => 'varchar', 'length' => 32, 'not null' => true, 'description' => 'queue for what? "email", "xmpp", "sms", "irc", ...'),
            'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            'claimed' => array('type' => 'datetime', 'description' => 'date this item was claimed'),),
         'primary key' => array('id'),
         'indexes' => array(
            'queue_item_created_idx' => array('created'),),);
   }


   // -------------------------------------------------------------------------
   // Function: top
   // Processes the current top queue item for a given transport.  If no transport
   // is explicitly specified, it will be the top item of all transports.
   //
   // Parameters: $transports - name of a single queue or array of queues to pull from
   //
   static function top($transports=null, array $ignored_transports=array()) {
      $qi = new Queue_item();
      if ($transports) {
         if (is_array($transports)) {
            // @fixme use safer escaping
            $list = implode("','", array_map(array($qi, 'escape'), $transports));
            $qi->whereAdd("transport in ('$list')");
         } else {
            $qi->transport = $transports;
         }
      }
      if (!empty($ignored_transports)) {
         // @fixme use safer escaping
         $list = implode("','", array_map(array($qi, 'escape'), $ignored_transports));
         $qi->whereAdd("transport NOT IN ('$list')");
      }
      $qi->orderBy('created');
      $qi->whereAdd('claimed is null');
      $qi->limit(1);
      $cnt = $qi->find(true);
      if ($cnt) {
         // XXX: potential race condition
         // can we force it to only update if claimed is still null
         // (or old)?
         common_log(LOG_INFO, 'Claiming queue item id = ' . $qi->getID() . ' for transport ' . $qi->transport);
         $orig = clone($qi);
         $qi->claimed = common_sql_now();
         $result = $qi->update($orig);
         if ($result) {
            common_log(LOG_DEBUG, 'Claim succeeded.');
            return $qi;
         } else {
            common_log(LOG_ERR, 'Claim of queue item id= ' . $qi->getID() . ' for transport ' . $qi->transport . ' failed.');
         }
      }
      $qi = null;
      return null;
   }


   // -------------------------------------------------------------------------
   // Fuinction: releaseClaim
   // Release a claimed item.
   function releaseClaim() {
      // DB_DataObject doesn't let us save nulls right now
      $sql = sprintf("UPDATE queue_item SET claimed=NULL WHERE id=%d", $this->getID());
      $this->query($sql);
      $this->claimed = null;
      $this->encache();
   }
}

// END OF FILE
// ============================================================================
?>