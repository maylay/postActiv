<?php
/* ============================================================================
 * Title: APIAccountBackgroundColor
 * Update a user's background color
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
 * Update a user's background color.  This is pretty qvitter-centric and we
 * probably don't need it for non-qvitter sites.
 *
 * PHP version:
 * Tested with PHP 7.0
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


class ApiAccountUpdateBackgroundColorAction extends ApiAuthAction
{
    var $backgroundcolor = null;

    protected $needPost = true;

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

        $this->backgroundcolor = $this->trimmed('backgroundcolor');
        return true;
    }

    /**
     * Handle the request
     *
     * Try to save the user's colors in her design. Create a new design
     * if the user doesn't already have one.
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();
    
        $validhex = preg_match('/^[a-f0-9]{6}$/i',$this->backgroundcolor);
        if ($validhex === false || $validhex == 0) {
            $this->clientError(_('Not a valid hex color.'), 400);
        }
    
        // save the new color
        $original = clone($this->auth_user);
        $this->auth_user->backgroundcolor = $this->backgroundcolor;
        if (!$this->auth_user->update($original)) {
            $this->clientError(_('Error updating user.'), 404);
        }

        $twitter_user = $this->twitterUserArray($this->scoped, true);

        $this->initDocument('json');
        $this->showJsonObjects($twitter_user);
        $this->endDocument('json');
    }
}

// END OF FILE
// ============================================================================
?>