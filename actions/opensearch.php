<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: OpenSearch
 * Opensearch action class.
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 * Opensearch action class.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Matthew Gregg <matthew.gregg@gmail.com>
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Evan Prodromou
 * o Robin Millette <robin@millette.info>
 * o Sarven Capadisli
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