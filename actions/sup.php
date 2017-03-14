<?php
/* ============================================================================
 * Title: SUP
 * A SUP action to produce correct SUP json output
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
 * A SUP action to produce correct SUP json output
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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


// ============================================================================
// Class: SupAction
// Action class to update a stream based on a given interval
class SupAction extends Action {
   // -------------------------------------------------------------------------
   // Function: handle
   // Get updates for the stream based on the update period specified.
   // Prints a json encoded string with them.
   //
   // Parameters:
   // o none
   //
   // Returns:
   // o void
   function handle()
   {
      parent::handle();
      $seconds = $this->trimmed('seconds');
      if (!$seconds) {
         $seconds = 15;
      }
      $updates = $this->getUpdates($seconds);
      header('Content-Type: application/json; charset=utf-8');
      print json_encode(array('updated_time' => date('c'),
                              'since_time' => date('c', time() - $seconds),
                              'available_periods' => $this->availablePeriods(),
                              'period' => $seconds,
                              'updates' => $updates));
   }


   // -------------------------------------------------------------------------
   // Function: availablePeriods
   // Determines which of the various HARDCODED arbitrary periods our interval
   // is closest to and returns that.
   //
   // Parameters:
   // o none
   //
   // Returns:
   // o $array - periods we can use based on the given desired interval
   function availablePeriods() {
      static $periods = array(86400, 43200, 21600, 7200,
                              3600, 1800, 600, 300, 120,
                              60, 30, 15);
      $available = array();
      foreach ($periods as $period) {
         $available[$period] = common_local_url('sup',
                                                array('seconds' => $period));
      }
      return $available;
   }


   // --------------------------------------------------------------------------
   // Function: getUpdates
   // Fetches and returns new notices for a stream since the last update
   //
   // Parameters:
   // o seconds - update interval
   //
   // Returns:
   // o array %updates - new array of ids of the new notices
   function getUpdates($seconds) {
      $notice = new Notice();
      $divider = common_sql_date(time() - $seconds);
      $notice->query('SELECT profile_id, max(id) AS max_id ' .
                     'FROM ( ' .
                     'SELECT profile_id, id FROM notice ' .
                      ((common_config('db','type') == 'pgsql') ?
                     'WHERE extract(epoch from created) > (extract(epoch from now()) - ' . $seconds . ') ' :
                     'WHERE created > "'.$divider.'" ' ) .
                     ') AS latest ' .
                     'GROUP BY profile_id');
      $updates = array();
      while ($notice->fetch()) {
         $updates[] = array($notice->profile_id, $notice->max_id);
      }
      return $updates;
   }


   // -------------------------------------------------------------------------
   // Function: isReadOnly
   // Returns whether the action class is read only (yes)
   //
   // Parameters:
   // o array $args - ignored
   //
   // Returns:
   // o boolean true
   function isReadOnly($args) {
      return true;
   }
}

// END OF FILE
// ============================================================================
?>