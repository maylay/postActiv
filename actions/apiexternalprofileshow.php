<?php
/* ============================================================================
 * Title: APIExternalProfileShow
 * Show an external user's profile information
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
 * Show an external user's profile information
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
 * Ouputs information for a user, specified by ID or screen name.
 * The user's most recent status will be returned inline.
 */
class ApiExternalProfileShowAction extends ApiPrivateAuthAction
{
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

        if ($this->format !== 'json') {
            $this->clientError('This method currently only serves JSON.', 415);
        }

        $profileurl = urldecode($this->arg('profileurl'));

        // TODO: Make this more ... unique!
        $this->profile = Profile::getKV('profileurl', $profileurl);        

        if (!($this->profile instanceof Profile)) {
            // TRANS: Client error displayed when requesting profile information for a non-existing profile.
            $this->clientError(_('Profile not found.'), 404);
        }

        return true;
    }

    /**
     * Handle the request
     *
     * Check the format and show the user info
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

        $twitter_user = $this->twitterUserArray($this->profile, true);

        $this->initDocument('json');
        $this->showJsonObjects($twitter_user);
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