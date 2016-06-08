<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * PHP version 5 
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
 * @category  Actions
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPLv3
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('STATUSNET') && !defined('LACONICA')) { exit(1); }

// @todo FIXME: documentation needed.
class SupAction extends Action
{
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

    function availablePeriods()
    {
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

    function getUpdates($seconds)
    {
        $notice = new Notice();

        // XXX: cache this. Depends on how big this protocol becomes;
        // Re-doing this query every 15 seconds isn't the end of the world.

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

    function isReadOnly($args)
    {
        return true;
    }
}
?>