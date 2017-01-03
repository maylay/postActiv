<?php
/* ============================================================================
 * Title: ActivityVerb
 * Class for deleting a notice
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
 * Class for deleting a notice
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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


class ActivityverbAction extends ManagedAction
{
    protected $needLogin = true;
    protected $canPost   = true;

    protected $verb      = null;

    public function title()
    {
        $title = null;
        Event::handle('ActivityVerbTitle', array($this, $this->verb, $this->notice, $this->scoped, &$title));
        return $title;
    }

    protected function doPreparation()
    {
        $this->verb = $this->trimmed('verb');
        if (empty($this->verb)) {
            throw new ServerException('A verb has not been specified.');
        }

        $this->notice = Notice::getByID($this->trimmed('id'));

        if (!$this->notice->inScope($this->scoped)) {
            // TRANS: %1$s is a user nickname, %2$d is a notice ID (number).
            throw new ClientException(sprintf(_('%1$s has no access to notice %2$d.'),
                                        $this->scoped->getNickname(), $this->notice->getID()), 403);
        }

        Event::handle('ActivityVerbDoPreparation', array($this, $this->verb, $this->notice, $this->scoped));
    }

    protected function doPost()
    {
        if (Event::handle('ActivityVerbDoPost', array($this, $this->verb, $this->notice, $this->scoped))) {
            // TRANS: Error when a POST method for an activity verb has not been handled by a plugin.
            throw new ClientException(sprintf(_('Could not handle POST for verb "%1$s".'), $this->verb));
        }
    }

    protected function showContent()
    {
        if (Event::handle('ActivityVerbShowContent', array($this, $this->verb, $this->notice, $this->scoped))) {
            // TRANS: Error when a page for an activity verb has not been handled by a plugin.
            $this->element('div', 'error', sprintf(_('Could not show content for verb "%1$s".'), $this->verb));
        }
    }
}

// END OF FILE
// ============================================================================
?>