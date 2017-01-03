<?php
/* ============================================================================
 * Title: APIAttachment
 * Show a notice's attachment
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
 * Show a notice's attachment
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
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
 * Show a notice's attachment
 *
 */
class ApiAttachmentAction extends ApiAuthAction
{
    const MAXCOUNT = 100;

    var $original = null;
    var $cnt      = self::MAXCOUNT;

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

        return true;
    }

    /**
     * Handle the request
     *
     * Make a new notice for the update, save it, and show it
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();
        
        $file = new File();
        $file->selectAdd(); // clears it
        $file->selectAdd('url');
        $file->id = $this->trimmed('id');
        $url = $file->fetchAll('url');
        
		$file_txt = '';
		if(strstr($url[0],'.html')) {
			$file_txt['txt'] = file_get_contents($url[0]);
			$file_txt['body_start'] = strpos($file_txt['txt'],'<body>')+6;
			$file_txt['body_end'] = strpos($file_txt['txt'],'</body>');
			$file_txt = substr($file_txt['txt'],$file_txt['body_start'],$file_txt['body_end']-$file_txt['body_start']);
			}

		$this->initDocument('json');
		$this->showJsonObjects($file_txt);
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