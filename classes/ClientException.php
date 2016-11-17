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
 * ClientException and descendant classes as well as the canonical error
 * definitions
 *
 * These classes represent some sort of client error, such as improper 
 * authentication credentials, or attempting to upload bad files, and the like.
 * Most of these can be fixed by the end user.
 * ----------------------------------------------------------------------------
 * @category  Exception
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2012 StatusNet, Inc
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 * ============================================================================
 */

# -----------------------------------------------------------------------------
# Canonical error codes
# The codes for server errors should reflect the closest appropriate HTTP Status
# Code, here.  See https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
define("CLIENT_EXCEPTION", 400);
define("CLIENT_EXCEPTION_UNAUTHORIZED", 403);
define("CLIENT_EXCEPTION_EMPTY_POST", 400);
define("CLIENT_EXCEPTION_PRIVATE_STREAM_NO_AUTH", 401);
define("CLIENT_EXCEPTION_PRIVATE_STREAM_UNAUTHORIZED", 403);

/* ----------------------------------------------------------------------------
 * class ClientException
 *    Subclass of PHP Exception for user or client errors.  By default, these 
 *    are put into LOG_DEBUG since most client errors aren't actually our 
 *    problem, but we may need the information for this if a 3rd party app or
 *    something is acting up.
 */
class ClientException extends Exception
{
    public function __construct($message = null, $code = CLIENT_EXCEPTION, Exception $previous = null, $severity = LOG_DEBUG) {
        parent::__construct($message, $code);
        if ($severity==LOG_DEBUG) {
           common_debug($message . " (" . $code . ")");
        } else {
           common_log($severity, $message . " (" . $code .")");
        }
    }

   // custom string representation of object
   public function __toString() {
      return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
   }
}

/* ----------------------------------------------------------------------------
 * class AuthorizationException
 *    A class for client exceptions caused by improper authorization.
 */
class AuthorizationException extends ClientException
{
    /**
     * Constructor
     *
     * @param string $message Message for the exception
     */
    public function __construct($message=null)
    {
        parent::__construct($message, CLIENT_EXCEPTION_UNAUTHORIZED);
    }
}

/* ----------------------------------------------------------------------------
 * class PrivateStreamException
 *    A class for client exceptions caused by trying to access a notice stream
 *    which is private in nature.
 */
class PrivateStreamException extends AuthorizationException
{
    var $owner = null;  // owner of the private stream
    var $reader = null; // reader, may be null if not logged in

    public function __construct(Profile $owner, Profile $reader=null)
    {
        $this->owner = $owner;
        $this->reader = $reader;

        // TRANS: Message when a private stream attemps to be read by unauthorized third party.
        $msg = sprintf(_m('This stream is protected and only authorized subscribers may see its contents.'));

        // If $reader is a profile, authentication has been made but still not accepted (403),
        // otherwise authentication may give access to this resource (401).
        parent::__construct($msg, ($reader instanceof Profile ? 
           CLIENT_EXCEPTION_PRIVATE_STREAM_UNAUTHORIZED : CLIENT_EXCEPTION_PRIVATE_STREAM_NO_AUTH));
    }
}

/* ----------------------------------------------------------------------------
 * class NoUploadedMediaException
 *    Class for a client exception caused when a POST upload does not contain a 
 *    file.
 */
class NoUploadedMediaException extends ClientException
{
    public $fieldname = null;

    public function __construct($fieldname, $msg=null)
    {
        $this->fieldname = $fieldname;

        if ($msg === null) {
            // TRANS: Exception text shown when no uploaded media was provided in POST
            // TRANS: %s is the HTML input field name.
            $msg = sprintf(_('There is no uploaded media for input field "%s".'), $this->fieldname);
        }

        parent::__construct($msg, CLIENT_EXCEPTION_EMPTY_POST);
    }
}
?>