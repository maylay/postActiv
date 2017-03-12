<?php
/* ============================================================================
 * Title: APIAccountRateLimitStatus
 * Dummy action that emulates Twitter's rate limit status API resource
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
 * Dummy action that emulates Twitter's rate limit status API resource
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Brenda Wallace <shiny@cpan.org>
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
 * We don't have a rate limit, but some clients check this method.
 * It always returns the same thing: 150 hits left.
 */
class ApiAccountRateLimitStatusAction extends ApiBareAuthAction
{
    /**
     * Handle the request
     *
     * Return some Twitter-ish data about API limits
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (!in_array($this->format, array('xml', 'json'))) {
            $this->clientError(
                // TRANS: Client error displayed when coming across a non-supported API method.
                _('API method not found.'),
                404,
                $this->format
            );
        }

        $reset   = new DateTime();
        $reset->modify('+1 hour');

        $this->initDocument($this->format);

         if ($this->format == 'xml') {
             $this->elementStart('hash');
             $this->element('remaining-hits', array('type' => 'integer'), 150);
             $this->element('hourly-limit', array('type' => 'integer'), 150);
             $this->element(
                 'reset-time', array('type' => 'datetime'),
                 common_date_iso8601($reset->format('r'))
             );
             $this->element(
                 'reset_time_in_seconds',
                 array('type' => 'integer'),
                 strtotime('+1 hour')
             );
             $this->elementEnd('hash');
         } elseif ($this->format == 'json') {
             $out = array(
                 'reset_time_in_seconds' => strtotime('+1 hour'),
                 'remaining_hits' => 150,
                 'hourly_limit' => 150,
                 'reset_time' => common_date_rfc2822(
                     $reset->format('r')
                  )
             );
             print json_encode($out);
         }

        $this->endDocument($this->format);
    }

    /**
     * Return true if read only.
     *
     * MAY override
     *
     * @param array $args other arguments
     *
     * @return boolean is read only action?
     */
    function isReadOnly($args)
    {
        return true;
    }
}

// END OF FILE
// ============================================================================
?>