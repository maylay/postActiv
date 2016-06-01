<?php
/**
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * Register a user to a site by their email address
 * 
 * PHP version 5
 *
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
 * @category  DomainStatusNetwork
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * An action to globally register a new user
 *
 * @category  Action
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

class GlobalregisterAction extends GlobalApiAction
{
    /**
     * For initializing members of the class.
     *
     * @param array $argarray misc. arguments
     *
     * @return boolean true
     */

    function prepare(array $args = array())
    {
        try {
            parent::prepare($args);
            return true;
        } catch (ClientException $e) {
            $this->showError($e->getMessage(), $e->getCode());
            return false;
        } catch (Exception $e) {
            common_log(LOG_ERR, $e->getMessage());
            $this->showError(_('An internal error occurred.'), 500);
            return false;
        }
    }

    /**
     * Handler method
     *
     * @param array $argarray is ignored since it's now passed in in prepare()
     *
     * @return void
     */

    function handle($argarray=null)
    {
        try {
            $confirm = DomainStatusNetworkPlugin::registerEmail($this->email);
            EmailRegistrationPlugin::sendConfirmEmail($confirm);
            $this->showSuccess();
        } catch (ClientException $e) {
            $this->showError($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            common_log(LOG_ERR, $e->getMessage());
            $this->showError(_('An internal error occurred.'), 500);
        }

        return;
    }
}
