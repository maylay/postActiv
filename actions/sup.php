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