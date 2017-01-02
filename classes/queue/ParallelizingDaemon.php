<?php
/* ============================================================================
 * Title: Parallelizing Daemon
 * Daemon able to spawn multiple child processes to do work in parallel
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
 * Daemon able to spawn multiple child processes to do work in parallel
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Craig Andrews <candrews@integralblue.com>
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

declare(ticks = 1);


// ----------------------------------------------------------------------------
// Class: ParallelizingDaemon
// Daemon able to spawn multiple child processes to do work in parallel
class ParallelizingDaemon extends Daemon
{
   private $_children     = array();
   private $_interval     = 0; // seconds
   private $_max_children = 0; // maximum number of children
   private $_debug        = false;

   // -------------------------------------------------------------------------
   // Function: __construct
   //  Constructor
   //
   // Parameters:
   // o string  $id           - the name/id of this daemon
   // o int     $interval     - sleep this long before doing everything again
   // o int     $max_children - maximum number of child processes at a time
   // o boolean $debug        - debug output flag
   //
   // Returns:
   // o void
   function __construct($id = null, $interval = 60, $max_children = 2,
                         $debug = null)
   {
      parent::__construct(true); // daemonize
      $this->_interval     = $interval;
      $this->_max_children = $max_children;
      $this->_debug        = $debug;
      if (isset($id)) {
         $this->set_id($id);
      }
   }


   // -------------------------------------------------------------------------
   // Function: Run
   // Run the daemon
   //
   // Returns:
   // o void
   function run() {
      if (isset($this->_debug)) {
         echo $this->name() . " - Debugging output enabled.\n";
      } do {
         $objects = $this->getObjects();
         foreach ($objects as $o) {
            // Fork a child for each object
            $pid = pcntl_fork();
            if ($pid == -1) {
                die ($this->name() . ' - Couldn\'t fork!');
            }
            if ($pid) {
               // Parent
               if (isset($this->_debug)) {
                  echo $this->name() . " - Forked new child - pid $pid.\n";
               }
               $this->_children[] = $pid;
               } else {
                  // Child
                  // Do something with each object
                  $this->childTask($o);
                  exit();
               }
               // Remove child from ps list as it finishes
               while (($c = pcntl_wait($status, WNOHANG OR WUNTRACED)) > 0) {
                  if (isset($this->_debug)) {
                     echo $this->name() . " - Child $c finished.\n";
                  }
                  $this->removePs($this->_children, $c);
               }
               // Wait! We have too many damn kids.
               if (sizeof($this->_children) >= $this->_max_children) {
                  if (isset($this->_debug)) {
                     echo $this->name() . " - Too many children. Waiting...\n";
                  }
                  if (($c = pcntl_wait($status, WUNTRACED)) > 0) {
                     if (isset($this->_debug)) {
                        echo $this->name() . " - Finished waiting for child $c.\n";
                  }
                  $this->removePs($this->_children, $c);
               }
            }
         }
         // Remove all children from the process list before restarting
         while (($c = pcntl_wait($status, WUNTRACED)) > 0) {
            if (isset($this->_debug)) {
               echo $this->name() . " - Child $c finished.\n";
            }
            $this->removePs($this->_children, $c);
         }
         // Rest for a bit
         if (isset($this->_debug)) {
            echo $this->name() . ' - Waiting ' . $this->_interval . " secs before running again.\n";
         }
         if ($this->_interval > 0) {
             sleep($this->_interval);
         }
      } while (true);
   }


   // -------------------------------------------------------------------------
   // Function: removePs
   // Remove a child process from the list of children
   //
   // Parameters:
   // o array &$plist array of processes
   // o int   $ps     process id
   //
   // Returns:
   // o void
   function removePs(&$plist, $ps) {
      for ($i = 0; $i < sizeof($plist); $i++) {
         if ($plist[$i] == $ps) {
            unset($plist[$i]);
            $plist = array_values($plist);
            break;
         }
      }
   }


   // -------------------------------------------------------------------------
   // function: getObjects
   // Get a list of objects to work on in parallel
   //
   // Returns:
   // o array An array of objects to work on
   function getObjects() {
       die('Implement ParallelizingDaemon::getObjects().');
   }


   // -------------------------------------------------------------------------
   // function: childTask
   // Do something with each object in parallel
   //
   // Parameters:
   //  mixed $object - data to work on
   //
   // Returns:
   // o void
   function childTask($object) {
        die("Implement ParallelizingDaemon::childTask($object).");
   }
}

// END OF FILE
// ============================================================================
?>