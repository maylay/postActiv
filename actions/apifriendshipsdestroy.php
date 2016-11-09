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
 * Unsubscribe to a user via API
 *
 * @category  API
 * @package   postActiv
 * @author    Zach Copley <zach@copley.name>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Robin Millette <robin@millette.info>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Allows the authenticating users to unfollow (unsubscribe) the user specified in
 * the ID parameter.  Returns the unfollowed user in the requested format when
 * successful.  Returns a string describing the failure condition when unsuccessful.
 */
class ApiFriendshipsDestroyAction extends ApiAuthAction
{
    protected $needPost = true;

    protected $other = null;

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

        $this->other = $this->getTargetProfile($this->arg('id'));

        return true;
    }

    /**
     * Handle the request
     *
     * Check the format and show the user info
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        if (!in_array($this->format, array('xml', 'json'))) {
            $this->clientError(
                // TRANS: Client error displayed when coming across a non-supported API method.
                _('API method not found.'),
                404
            );
        }

        if (!$this->other instanceof Profile) {
            $this->clientError(
                // TRANS: Client error displayed when trying to unfollow a user that cannot be found.
                _('Could not unfollow user: User not found.'),
                403
            );
        }

        // Don't allow unsubscribing from yourself!

        if ($this->scoped->id == $this->other->id) {
            $this->clientError(
                // TRANS: Client error displayed when trying to unfollow self.
                _("You cannot unfollow yourself."),
                403
            );
        }

        // throws an exception on error
        Subscription::cancel($this->scoped, $this->other);

        $this->initDocument($this->format);
        $this->showProfile($this->other, $this->format);
        $this->endDocument($this->format);
    }
}
?>