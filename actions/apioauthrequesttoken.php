<?php
/* ============================================================================
 * Title: APIOAuthRequestToken
 * Issue temporary OAuth credentials (a request token)
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 * Issue temporary OAuth credentials (a request token)
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Zach Copley
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
 * Issue temporary OAuth credentials (a request token)
 */
class ApiOAuthRequestTokenAction extends ApiOAuthAction
{
    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        // XXX: support "force_login" parameter like Twitter? (Forces the user to enter
        // their credentials to ensure the correct users account is authorized.)

        return true;
    }

    /**
     * Handle a request for temporary OAuth credentials
     *
     * Make sure the request is kosher, then emit a set of temporary
     * credentials -- AKA an unauthorized request token.
     *
     * @return void
     */
    function handle()
    {
        parent::handle();

        $datastore   = new ApiGNUsocialOAuthDataStore();
        $server      = new OAuthServer($datastore);
        $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();

        $server->add_signature_method($hmac_method);

        try {

            $req = OAuthRequest::from_request();

            // verify callback
            if (!$this->verifyCallback($req->get_parameter('oauth_callback'))) {
                throw new OAuthException(
                    "You must provide a valid URL or 'oob' in oauth_callback.",
                    400
                );
            }

            // check signature and issue a new request token
            $token = $server->fetch_request_token($req);

            common_log(
                LOG_INFO,
                sprintf(
                    "API OAuth - Issued request token %s for consumer %s with oauth_callback %s",
                    $token->key,
                    $req->get_parameter('oauth_consumer_key'),
                    "'" . $req->get_parameter('oauth_callback') ."'"
                )
            );

            // return token to the client
            $this->showRequestToken($token);

        } catch (OAuthException $e) {
            common_log(LOG_WARNING, 'API OAuthException - ' . $e->getMessage());

            // Return 401 for for bad credentials or signature problems,
            // and 400 for missing or unsupported parameters

            $code = $e->getCode();
            $this->clientError($e->getMessage(), empty($code) ? 401 : $code, 'text');
        }
    }

    /*
     * Display temporary OAuth credentials
     */
    function showRequestToken($token)
    {
        header('Content-Type: application/x-www-form-urlencoded');
        print $token;
        print '&oauth_callback_confirmed=true';
    }

    /* Make sure the callback parameter contains either a real URL
     * or the string 'oob'.
     *
     * @todo Check for evil/banned URLs here
     *
     * @return boolean true or false
     */
    function verifyCallback($callback)
    {
        if ($callback == "oob") {
            common_debug("OAuth request token requested for out of band client.");

            // XXX: Should we throw an error if a client is registered as a
            // web application but requests the pin based workflow? For now I'm
            // allowing the workflow to proceed and issuing a pin. --Zach

            return true;
        } else {
            return filter_var($callback, FILTER_VALIDATE_URL);
        }
    }
}

// END OF FILE
// ============================================================================
?>