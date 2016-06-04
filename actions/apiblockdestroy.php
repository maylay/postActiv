<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Un-block a user via the API
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
 * @author    Zach Copley <zach@status.net>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * Un-blocks the user specified in the ID parameter for the authenticating user.
 * Returns the un-blocked user in the requested format when successful.
 *
 * @category  API
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Zach Copley <zach@status.net>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2009-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://git.gnu.io/maiyannah/postActiv
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
?>