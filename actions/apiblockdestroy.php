<?php
/* ============================================================================
 * Title: APIBlockDestroy
 * Un-block a user via the API
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
 * Un-block a user via the API
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
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
 * Un-blocks the user specified in the ID parameter for the authenticating user.
 * Returns the un-blocked user in the requested format when successful.
 */
class ApiBlockDestroyAction extends ApiAuthAction
{
    protected $needPost = true;

    var $other   = null;

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

        $this->other  = $this->getTargetProfile($this->arg('id'));

        return true;
    }

    /**
     * Handle the request
     *
     * Save the new message
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (empty($this->user) || empty($this->other)) {
            // TRANS: Client error when user not found for an API action to remove a block for a user.
            $this->clientError(_('No such user.'), 404);
        }

        if ($this->user->hasBlocked($this->other)) {
            if (Event::handle('StartUnblockProfile', array($this->user, $this->other))) {
                $result = $this->user->unblock($this->other);
                if ($result) {
                    Event::handle('EndUnblockProfile', array($this->user, $this->other));
                }
            }
        }

        if (!$this->user->hasBlocked($this->other)) {
            $this->initDocument($this->format);
            $this->showProfile($this->other, $this->format);
            $this->endDocument($this->format);
        } else {
            // TRANS: Server error displayed when unblocking a user has failed.
            $this->serverError(_('Unblock user failed.'));
        }
    }
}

// END OF FILE
// ============================================================================
?>