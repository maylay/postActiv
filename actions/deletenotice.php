<?php
/* ============================================================================
 * Title: DeleteNotice
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
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * @author    Matthew Gregg <matthew.gregg@gmail.com>
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Evan Prodromou
 * @author    Zach Copley
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
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
class DeletenoticeAction extends FormAction
{
    protected $notice = null;

    protected function doPreparation()
    {
        $this->notice = Notice::getByID($this->trimmed('notice'));

        if (!$this->scoped->sameAs($this->notice->getProfile()) &&
                   !$this->scoped->hasRight(Right::DELETEOTHERSNOTICE)) {
            // TRANS: Error message displayed trying to delete a notice that was not made by the current user.
            $this->clientError(_('Cannot delete this notice.'));
        }

        $this->formOpts['notice'] = $this->notice;
    }

    function getInstructions()
    {
        // TRANS: Instructions for deleting a notice.
        return _('You are about to permanently delete a notice. ' .
                 'Once this is done, it cannot be undone.');
    }

    function title()
    {
        // TRANS: Page title when deleting a notice.
        return _('Delete notice');
    }

    protected function doPost()
    {
        if ($this->arg('yes')) {
            if (Event::handle('StartDeleteOwnNotice', array($this->scoped->getUser(), $this->notice))) {
                $this->notice->deleteAs($this->scoped);
                Event::handle('EndDeleteOwnNotice', array($this->scoped->getUser(), $this->notice));
            }
        }

        common_redirect(common_get_returnto(), 303);
    }
}

// END OF FILE
// ============================================================================
?>