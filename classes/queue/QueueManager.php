<?php
/* ============================================================================
 * Title: QueueManager
 * Base class for Queue managers
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
 * Abstract class for queue managers
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Brion Vibber <brion@pobox.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Zach Copley
 * o Mikael Nordfelth <mmn@hethane.se>
 * o Joshua Judson Rosen <rozzin@geekspace.com>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */
 
// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

// ----------------------------------------------------------------------------
// Class: QueueManager
// Completed child classes must implement the enqueue() method.
//
// For background processing, classes should implement either socket-based
// input (handleInput(), getSockets()) or idle-loop polling (idle()).
//
// Inherits from: IoManager
//
// Variables:
// o static $qm = null;
// o protected $master = null;
// o protected $handlers = array();
// o protected $groups = array();
// o protected $activeGroups = array();
// o protected $ignoredTransports = array();
// o protected $itemsUntilExpiration = null;
abstract class QueueManager extends IoManager
{
    static $qm = null;

    protected $master = null;
    protected $handlers = array();
    protected $groups = array();
    protected $activeGroups = array();
    protected $ignoredTransports = array();
    protected $itemsUntilExpiration = null;

    // ------------------------------------------------------------------------
    // Function: __construct
    // Constructor procedure
    function __construct()
    {
        $this->initialize();
    }

    // ------------------------------------------------------------------------
    // Function: setItemsUntilExpiration
    // Set a value for the number of items to process before respawning a daemon
    // process
    //
    // Parameters:
    // o int $itemsUntilExpiration - number of items until daemon process is respawned
    public function setItemsUntilExpiration($itemsUntilExpiration) {
        // Don't allow negative values
        if ($itemsUntilExpiration > 0)
            $this->itemsUntilExpiration = $itemsUntilExpiration;
    }

    // ------------------------------------------------------------------------
    // Function: recordItemHandled
    // Keeps track of the number of items left to process before the daemon
    // process has to be respawned.
    //
    // Returns:
    // o true if limit has not been reached, false otherwise
    public function recordItemHandled() {
        if ($this->itemsUntilExpiration !== null) {
            $this->_log(LOG_DEBUG, "Items until expiration: $this->itemsUntilExpiration");
            // Don't keep decrementing after hitting zero.
            if ($this->itemsUntilExpiration <= 0 || $this->itemsUntilExpiration-- <= 0)
                return false;
        }
        return true;
    }

    // ------------------------------------------------------------------------
    // Function: isExpired
    // Return true if the maximum number of items for a given daemon process
    // has been reached.
    //
    // Returns:
    // o boolean true if there are no more items until expiration or if a limit
    // has not been set.
    public function isExpired() {
        return ($this->itemsUntilExpiration !== null && $this->itemsUntilExpiration <= 0);
    }

    // ------------------------------------------------------------------------
    // Function: _log
    // Log a string to the common log
    //
    // Parameters:
    // o level - log level to log at
    // o msg   - message to log
    protected function _log($level, $msg)
    {
        $class = get_class($this);
        if ($this->activeGroups) {
            $groups = ' (' . implode(',', $this->activeGroups) . ')';
        } else {
            $groups = '';
        }
        common_log($level, "$class$groups: $msg");
    }

    // ------------------------------------------------------------------------
    // Function: initialize
    // Initialize the list of queue handlers for the current site.
    //
    // Events:
    // o StartInitializeQueueManager
    // o EndInitializeQueueManager
    function initialize()
    {
        $this->handlers = array();
        $this->groups = array();
        $this->groupsByTransport = array();

        if (Event::handle('StartInitializeQueueManager', array($this))) {
            $this->connect('distrib', 'DistribQueueHandler');
            $this->connect('ping', 'PingQueueHandler');
            if (common_config('sms', 'enabled')) {
                $this->connect('sms', 'SmsQueueHandler');
            }

            // Background user management tasks...
            $this->connect('deluser', 'DelUserQueueHandler');
            $this->connect('feedimp', 'FeedImporter');
            $this->connect('actimp', 'ActivityImporter');
            $this->connect('acctmove', 'AccountMover');
            $this->connect('actmove', 'ActivityMover');

            // For compat with old plugins not registering their own handlers.
            $this->connect('plugin', 'PluginQueueHandler');
        }
        Event::handle('EndInitializeQueueManager', array($this));
    }

    // ------------------------------------------------------------------------
    // Function: get
    // Factory function to pull the appropriate QueueManager object
    // for this site's configuration. It can then be used to queue
    // events for later processing or to spawn a processing loop.
    //
    // Plugins can add to the built-in types by hooking StartNewQueueManager.
    //
    // Returns:
    //    $qm - QueueManager object
    public static function get()
    {
        if (empty(self::$qm)) {

            if (Event::handle('StartNewQueueManager', array(&self::$qm))) {

                $enabled = common_config('queue', 'enabled');
                $type = common_config('queue', 'subsystem');
                $itemsUntilExpiration = common_config('queue', 'items_to_handle');

                if (!$enabled) {
                    // does everything immediately
                    self::$qm = new UnQueueManager();
                } else {
                    switch ($type) {
                     case 'db':
                        self::$qm = new DBQueueManager();
                        break;
                     case 'stomp':
                        self::$qm = new StompQueueManager();
                        break;
                     case 'redis':
                        self::$qm = new RedisQueueManager();
                        break;
                     default:
                        throw new ServerException("No queue manager class for type '$type'");
                    }
                    self::$qm->setItemsUntilExpiration($itemsUntilExpiration);
                }
            }
        }

        return self::$qm;
    }

    // ------------------------------------------------------------------------
    // Function: multiSite
    //
    // Fixme:
    // wouldn't necessarily work with other class types.
    // Better to change the interface...?
    public static function multiSite()
    {
        if (common_config('queue', 'subsystem') == 'stomp') {
            return IoManager::INSTANCE_PER_PROCESS;
        } else {
            return IoManager::SINGLE_ONLY;
        }
    }

    // ------------------------------------------------------------------------
    // Function: sendControlSignal
    // Optional; ping any running queue handler daemons with a notification
    // such as announcing a new site to handle or requesting clean shutdown.
    // This avoids having to restart all the daemons manually to update configs
    // and such.
    //
    // Called from scripts/queuectl.php controller utility.
    //
    // Parameters:
    // o string $event - event key
    // o string $param - optional parameter to append to key
    //
    // Returns:
    // o boolean success
    public function sendControlSignal($event, $param='')
    {
        throw new Exception(get_class($this) . " does not support control signals.");
    }

    // ------------------------------------------------------------------------
    // Function: enqueue
    // Store an object (usually/always a Notice) into the given queue
    // for later processing. No guarantee is made on when it will be
    // processed; it could be immediately or at some unspecified point
    // in the future.
    //
    // Must be implemented by any queue manager.
    //
    // Parameters:
    // o Notice $object
    // o string $queue
    abstract function enqueue($object, $queue);

    // ------------------------------------------------------------------------
    // Function: logrep
    // Build a string representation of an object for logging purpose
    //
    // Parameters:
    // o Object - object to build the representation for.
    //
    // Returns:
    // o string $object
    function logrep($object) {
        if (is_object($object)) {
            $class = get_class($object);
            if (isset($object->id)) {
                return "$class $object->id";
            }
            return $class;
        } elseif (is_string($object)) {
            $len = strlen($object);
            $fragment = mb_substr($object, 0, 32);
            if (mb_strlen($object) > 32) {
                $fragment .= '...';
            }
            return "string '$fragment' ($len bytes)";
        } elseif (is_array($object)) {
            return 'array with ' . count($object) .
                   ' elements (keys:[' .  implode(',', array_keys($object)) . '])';
        }
        return strval($object);
    }

    // ------------------------------------------------------------------------
    // Function: encode
    // Encode an object for queued storage.
    //
    // Parameters:
    // o mixed $item
    //
    // Returns:
    // o string $item
    protected function encode($item)
    {
        return serialize($item);
    }

    // ------------------------------------------------------------------------
    // Function: decode
    // Decode an object from queued storage.
    // Accepts notice reference entries and serialized items.
    //
    // Parameters:
    // o string $frame
    //
    // Returns:
    // o deserialized object
    protected function decode($frame)
    {
        $object = unserialize($frame);

        // If it is a string, we really store a JSON object in there
        // except if it begins with '<', because then it is XML.
        if (is_string($object) &&
            substr($object, 0, 1) != '<' &&
            !is_numeric($object))
        {
            $json = json_decode($object);
            if ($json === null) {
                throw new Exception('Bad frame in queue item');
            }

            // The JSON object has a type parameter which contains the class
            if (empty($json->type)) {
                throw new Exception('Type not specified for queue item');
            }
            if (!is_a($json->type, 'Managed_DataObject', true)) {
                throw new Exception('Managed_DataObject class does not exist for queue item');
            }

            // And each of these types should have a unique id (or uri)
            if (isset($json->id) && !empty($json->id)) {
                $object = call_user_func(array($json->type, 'getKV'), 'id', $json->id);
            } elseif (isset($json->uri) && !empty($json->uri)) {
                $object = call_user_func(array($json->type, 'getKV'), 'uri', $json->uri);
            }

            // But if no object was found, there's nothing we can handle
            if (!$object instanceof Managed_DataObject) {
                throw new Exception('Queue item frame referenced a non-existant object');
            }
        }

        // If the frame was not a string, it's either an array or an object.

        return $object;
    }

    /**
     * Instantiate the appropriate QueueHandler class for the given queue.
     *
     * @param string $queue
     * @return mixed QueueHandler or null
     */
    function getHandler($queue)
    {
        if (isset($this->handlers[$queue])) {
            $class = $this->handlers[$queue];
            if(is_object($class)) {
                return $class;
            } else if (class_exists($class)) {
                return new $class();
            } else {
                $this->_log(LOG_ERR, "Nonexistent handler class '$class' for queue '$queue'");
            }
        }
        throw new NoQueueHandlerException($queue);
    }

    /**
     * Get a list of registered queue transport names to be used
     * for listening in this daemon.
     *
     * @return array of strings
     */
    function activeQueues()
    {
        $queues = array();
        foreach ($this->activeGroups as $group) {
            if (isset($this->groups[$group])) {
                $queues = array_merge($queues, $this->groups[$group]);
            }
        }

        return array_keys($queues);
    }

    // ------------------------------------------------------------------------
    // Function: getIgnoredTransports
    function getIgnoredTransports()
    {
        return array_keys($this->ignoredTransports);
    }

    // ------------------------------------------------------------------------
    // Function: ignoreTransport
    function ignoreTransport($transport)
    {
        // key is used for uniqueness, value doesn't mean anything
        $this->ignoredTransports[$transport] = true;
    }

    // ------------------------------------------------------------------------
    // Function: connect
    // Register a queue transport name and handler class for your plugin.
    // Only registered transports will be reliably picked up!
    //
    // Parameters:
    // o string $transport
    // o string $class class name or object instance
    // o string $group
    public function connect($transport, $class, $group='main')
    {
        $this->handlers[$transport] = $class;
        $this->groups[$group][$transport] = $class;
        $this->groupsByTransport[$transport] = $group;
    }

    // ------------------------------------------------------------------------
    // Function: setActiveGroup
    // Set the active group which will be used for listening.
    // @param string $group
    function setActiveGroup($group)
    {
        $this->activeGroups = array($group);
    }

    // ------------------------------------------------------------------------
    // Function: setActiveGroups
    // Set the active group(s) which will be used for listening.
    //
    // Parameters:
    // o array $groups
    function setActiveGroups($groups)
    {
        $this->activeGroups = $groups;
    }

    // ------------------------------------------------------------------------
    // Function: queueGroup
    //
    // Returns:
    // o string - queue group for this queue
    function queueGroup($queue)
    {
        if (isset($this->groupsByTransport[$queue])) {
            return $this->groupsByTransport[$queue];
        } else {
            throw new Exception("Requested group for unregistered transport $queue");
        }
    }

    // ------------------------------------------------------------------------
    // Function: stats
    // Send a statistic ping to the queue monitoring system,
    // optionally with a per-queue id.
    //
    // Parameters:
    // o string $key
    // o string $queue (default false)
    function stats($key, $queue=false)
    {
        $owners = array();
        if ($queue) {
            $owners[] = "queue:$queue";
            $owners[] = "site:" . common_config('site', 'server');
        }
        if (isset($this->master)) {
            $this->master->stats($key, $owners);
        } else {
            $monitor = new QueueMonitor();
            $monitor->stats($key, $owners);
        }
    }
}

// END OF FILE
// ============================================================================
?>
