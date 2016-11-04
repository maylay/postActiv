<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
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
 * PHP version 5
 *
 * Opensearch action class.
 *
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Robin Millette <millette@status.net>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }
/**
 * Opensearch action class.
 *
 * Formatting of RSS handled by Rss10Action
 */
class OpensearchAction extends Action
{
    /**
     * Class handler.
     *
     * @return boolean false if user doesn't exist
     */
    function handle()
    {
        parent::handle();
        $type       = $this->trimmed('type');
        $short_name = '';
        if ($type == 'people') {
            $type       = 'peoplesearch';
            // TRANS: ShortName in the OpenSearch interface when trying to find users.
            $short_name = _('People Search');
        } else {
            $type       = 'noticesearch';
            // TRANS: ShortName in the OpenSearch interface when trying to find notices.
            $short_name = _('Notice Search');
        }
        header('Content-Type: application/opensearchdescription+xml');
        $this->startXML();
        $this->elementStart('OpenSearchDescription', array('xmlns' => 'http://a9.com/-/spec/opensearch/1.1/'));
        $short_name =  common_config('site', 'name').' '.$short_name;
        $this->element('ShortName', null, $short_name);
        $this->element('Contact', null, common_config('site', 'email'));
        $this->element('Url', array('type' => 'text/html', 'method' => 'get',
                       'template' => str_replace('---', '{searchTerms}', common_local_url($type, array('q' => '---')))));
        $this->element('Image', array('height' => 16, 'width' => 16, 'type' => 'image/vnd.microsoft.icon'), common_path('favicon.ico'));
        $this->element('Image', array('height' => 50, 'width' => 50, 'type' => 'image/png'), Theme::path('logo.png'));
        $this->element('AdultContent', null, 'false');
        $this->element('Language', null, common_language());
        $this->element('OutputEncoding', null, 'UTF-8');
        $this->element('InputEncoding', null, 'UTF-8');
        $this->elementEnd('OpenSearchDescription');
        $this->endXML();
    }

    function isReadOnly($args)
    {
        return true;
    }
}
?>