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
 * Update a user's background color
 *
 * @category  API
 * @package   postActiv
 * @author    Hannes Mannerheim <h@nnesmannerhe.im>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2013-2016 Hannes Mannerheim
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

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
?>