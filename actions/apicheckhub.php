<?php
/* ============================================================================
 * Title: APICheckHub
 * Check if a url has a push-hub, i.e. if it is possible to subscribe
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
 * Check if a url has a push-hub, i.e. if it is possible to subscribe
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Hannes Mannerheim <h@nnesmannerhe.im>
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
 * Check if a url has a push-hub, i.e. if it is possible to subscribe
 *
 */
class ApiCheckHubAction extends ApiAuthAction
{
    protected $url = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        if ($this->format !== 'json') {
            $this->clientError('This method currently only serves JSON.', 415);
        }

        $this->url = urldecode($args['url']);
        
        if (empty($this->url)) {
            $this->clientError(_('No URL.'), 403);
        }

        if (!common_valid_http_url($this->url)) {
            $this->clientError(_('Invalid URL.'), 403);
        }

        return true;
    }

    /**
     * Handle the request
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        $discover = new FeedDiscovery();

        try {
            $feeduri = $discover->discoverFromURL($this->url);
            if($feeduri) {
                $huburi = $discover->getHubLink();                
            }
        } catch (FeedSubNoFeedException $e) {
            $this->clientError(_('No feed found'), 403);
        } catch (FeedSubBadResponseException $e) {
            $this->clientError(_('No hub found'), 403);
        }
		
		$hub_status = array();
		if ($huburi) {
			$hub_status = array('huburi' => $huburi);
		}
			
		$this->initDocument('json');
		$this->showJsonObjects($hub_status);
		$this->endDocument('json');
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