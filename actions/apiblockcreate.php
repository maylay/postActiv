<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
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
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Block a user via the API
 *
 * @category  API
 * @package   postActiv
 * @author    Zach Copley <zach@status.net>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copryight 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Blocks the user specified in the ID parameter as the authenticating user.
 * Destroys a friendship to the blocked user if it exists. Returns the
 * blocked user in the requested format when successful.
 */
class ApiBlockCreateAction extends ApiAuthAction
{
    protected $needPost = true;

    var $other   = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     *
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
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (empty($this->user) || empty($this->other)) {
            // TRANS: Client error displayed when trying to block a non-existing user or a user from another site.
            $this->clientError(_('No such user.'), 404);
        }

        // Don't allow blocking yourself!

        if ($this->user->id == $this->other->id) {
            // TRANS: Client error displayed when users try to block themselves.
            $this->clientError(_("You cannot block yourself!"), 403);
        }

        if (!$this->user->hasBlocked($this->other)) {
            if (Event::handle('StartBlockProfile', array($this->user, $this->other))) {
                $result = $this->user->block($this->other);
                if ($result) {
                    Event::handle('EndBlockProfile', array($this->user, $this->other));
                }
            }
        }

        if ($this->user->hasBlocked($this->other)) {
            $this->initDocument($this->format);
            $this->showProfile($this->other, $this->format);
            $this->endDocument($this->format);
        } else {
            // TRANS: Server error displayed when blocking a user has failed.
            $this->serverError(_('Block user failed.'), 500);
        }
    }
}
?>