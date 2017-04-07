<?php
/* ============================================================================
 * Title: QueueMonitor
 * Base class for Queue monitors
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
 * Monitoring output helper for IoMaster and IoManager/QueueManager
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Craig Andrews <candrews@integralblue.com>
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

// ----------------------------------------------------------------------------
// Class: QueueMonitor
// Class abstraction for a monitor that looks after a specific queue.
class QueueMonitor
{
    protected $monSocket = null;

    // ------------------------------------------------------------------------
    // Function: stats
    // Increment monitoring statistics for a given counter, if configured.
    // Only explicitly listed thread/site/queue owners will be incremented.
    //
    // Parameters:
    // o string $key   - counter name
    // o array $owners - list of owner keys like 'queue:xmpp' or 'site:stat01'
    public function stats($key, $owners=array())
    {
        $this->ping(array('counter' => $key,
                          'owners' => $owners));
    }

    // ------------------------------------------------------------------------
    // Function: logState
    // Send thread state update to the monitoring server, if configured.
    //
    // Parameters:
    // o string $thread   - ID (eg 'generic.1')
    // o string $state    - 'init', 'queue', 'shutdown' etc
    // o string $substate - optional, eg queue name 'omb' 'sms' etc
    public function logState($threadId, $state, $substate='')
    {
        $this->ping(array('thread_id' => $threadId,
                          'state' => $state,
                          'substate' => $substate,
                          'ts' => microtime(true)));
    }

    // ------------------------------------------------------------------------
    // Function: ping
    // General call to the monitoring server
    protected function ping($data)
    {
        $target = common_config('queue', 'monitor');
        if (empty($target)) {
            return;
        }

        $data = $this->prepMonitorData($data);

        if (substr($target, 0, 4) == 'udp:') {
            $this->pingUdp($target, $data);
        } else if (substr($target, 0, 5) == 'http:') {
            $this->pingHttp($target, $data);
        } else {
            common_log(LOG_ERR, __METHOD__ . ' unknown monitor target type ' . $target);
        }
    }

    // ------------------------------------------------------------------------
    // Function: pingUdp
    protected function pingUdp($target, $data)
    {
        if (!$this->monSocket) {
            $this->monSocket = stream_socket_client($target, $errno, $errstr);
        }
        if ($this->monSocket) {
            $post = http_build_query($data, '', '&');
            stream_socket_sendto($this->monSocket, $post);
        } else {
            common_log(LOG_ERR, __METHOD__ . " UDP logging fail: $errstr");
        }
    }

    // ------------------------------------------------------------------------
    // Function: pingHttp
    protected function pingHttp($target, $data)
    {
        $client = new HTTPClient();
        try {
            $result = $client->post($target, array(), $data);
        
            if (!$result->isOk()) {
                common_log(LOG_ERR, __METHOD__ . ' HTTP ' . $result->getStatus() . ': ' . $result->getBody());
            }
        } catch (NoHttpResponseException $e) {
            common_log(LOG_ERR, __METHOD__ . ':'.$e->getMessage());
        } catch (HTTP_Request2_Exception $e) {
            common_log(LOG_ERR, __CLASS__ . ": Invalid $code redirect from $url to $target");
        }
    }

    // ------------------------------------------------------------------------
    // Function: prepMonitorData
    protected function prepMonitorData($data)
    {
        #asort($data);
        #$macdata = http_build_query($data, '', '&');
        #$key = 'This is a nice old key';
        #$data['hmac'] = hash_hmac('sha256', $macdata, $key);
        return $data;
    }

}
// END OF FILE
// ----------------------------------------------------------------------------
?>