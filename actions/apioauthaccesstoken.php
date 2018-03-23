<?php
/* ============================================================================
 * Title: APIOAuthAccessToken
 * Action for getting OAuth token credentials
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
 * Action for getting OAuth token credentials (exchange an authorized
 * request token for an access token)
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
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
 * Action for getting OAuth token credentials (exchange an authorized
 * request token for an access token)
 */
class ApiOAuthAccessTokenAction extends ApiOAuthAction
{
    protected $reqToken = null;
    protected $verifier = null;

    /**
     * Class handler.
     *
     * @param array $args array of arguments
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

        $atok = $app = null;

        // XXX: Insist that oauth_token and oauth_verifier be populated?
        // Spec doesn't say they MUST be.

        try {
            $req  = OAuthRequest::from_request();

            $this->reqToken = $req->get_parameter('oauth_token');
            $this->verifier = $req->get_parameter('oauth_verifier');

            $app  = $datastore->getAppByRequestToken($this->reqToken);
            $atok = $server->fetch_access_token($req);
        } catch (Exception $e) {
            common_log(LOG_WARNING, 'API OAuthException - ' . $e->getMessage());
            common_debug(var_export($req, true));
            $code = $e->getCode();
            $this->clientError($e->getMessage(), empty($code) ? 401 : $code, 'text');
        }

        if (empty($atok)) {
            // Token exchange failed -- log it

            $msg = sprintf(
                'API OAuth - Failure exchanging OAuth request token for access token, '
                    . 'request token = %s, verifier = %s',
                $this->reqToken,
                $this->verifier
            );

            common_log(LOG_WARNING, $msg);
            // TRANS: Client error given from the OAuth API when the request token or verifier is invalid.
            $this->clientError(_('Invalid request token or verifier.'), 400, 'text');
        } else {
            common_log(
                LOG_INFO,
                sprintf(
                    "Issued access token '%s' for application %d (%s).",
                    $atok->key,
                    $app->id,
                    $app->name
                )
            );
            $this->showAccessToken($atok);
        }
    }

    /*
     * Display OAuth token credentials
     *
     * @param OAuthToken token the access token
     */
    function showAccessToken($token)
    {
        header('Content-Type: application/x-www-form-urlencoded');
        print $token;
    }
}

// END OF FILE
// =============================================================================
?>