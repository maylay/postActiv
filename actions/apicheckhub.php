<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Show a notice's attachment
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  API
 * @package   postActiv
 * @author    Hannes Mannerheim <h@nnesmannerhe.im>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2013-2016 Hannes Mannerheim
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Check if a url have a push-hub, i.e. if it is possible to subscribe
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
?>