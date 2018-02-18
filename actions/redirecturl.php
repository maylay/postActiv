<?php
/* ============================================================================
 * Title: RedirectURL
 * Redirect to the given URL
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
 * Redirect to the given URL
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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
 * Redirect to a given URL
 *
 * This is our internal low-budget URL-shortener
 */

class RedirecturlAction extends ManagedAction
{
    protected $file = null;

    protected function doPreparation()
    {
        $this->file = File::getByID($this->int('id'));

        return true;
    }

    public function showPage()
    {
        common_redirect($this->file->getUrl(false), 301);
    }

    function isReadOnly($args)
    {
        return true;
    }

    function lastModified()
    {
        // For comparison with If-Last-Modified
        // If not applicable, return null

        return strtotime($this->file->modified);
    }

    /**
     * Return etag, if applicable.
     *
     * MAY override
     *
     * @return string etag http header
     */
    function etag()
    {
        return 'W/"' . implode(':', array($this->getActionName(),
                                          common_user_cache_hash(),
                                          common_language(),
                                          $this->file->getID())) . '"';
    }
}

// END OF FILE
// ============================================================================
?>