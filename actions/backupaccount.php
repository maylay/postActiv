<?php
/* ============================================================================
 * Title: BackupAccount
 * Download a backup of your own account to the browser
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
 * Download a backup of your own account to the browser
 *
 * We go through some hoops to make this only respond to POST, since
 * it's kind of expensive and there's probably some downside to having
 * your account in all kinds of search engines.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Brion Vibber <brion@pobox.com>
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
 * Download a backup of your own account to the browser
 */
class BackupaccountAction extends FormAction
{
    protected $form = 'BackupAccount';

    function title()
    {
        // TRANS: Title for backup account page.
        return _('Backup account');
    }

    protected function doPreparation()
    {
        if (!$this->scoped->hasRight(Right::BACKUPACCOUNT)) {
            // TRANS: Client exception thrown when trying to backup an account without having backup rights.
            throw new ClientException(_('You may not backup your account.'), 403);
        }

        return true;
    }

    protected function doPost()
    {
        $stream = new UserActivityStream($this->scoped->getUser(), true, UserActivityStream::OUTPUT_RAW);

        header('Content-Disposition: attachment; filename='.urlencode($this->scoped->getNickname()).'.atom');
        header('Content-Type: application/atom+xml; charset=utf-8');

        // @fixme atom feed logic is in getString...
        // but we just want it to output to the outputter.
        $this->raw($stream->getString());

        // Don't print the page HTML
        exit(0);
    }

    public function isReadOnly($args) {
        return true;
    }

    function lastModified()
    {
        // For comparison with If-Last-Modified
        // If not applicable, return null
        return null;
    }

    function etag()
    {
        return null;
    }
}

// END OF FILE
// ============================================================================
?>