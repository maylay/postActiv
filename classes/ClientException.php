<?php
/* ============================================================================
 * Title: ClientException
 * ClientException and descendant classes as well as the canonical error
 * definitions
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
 * ClientException and descendant classes as well as the canonical error
 * definitions
 *
 * These classes represent some sort of client error, such as improper 
 * authentication credentials, or attempting to upload bad files, and the like.
 * Most of these can be fixed by the end user.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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

// -----------------------------------------------------------------------------
// Canonical error codes
// The codes for server errors should reflect the closest appropriate HTTP Status
// Code, here.  See https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
define("CLIENT_EXCEPTION", 400);
define("CLIENT_EXCEPTION_UNAUTHORIZED", 403);
define("CLIENT_EXCEPTION_EMPTY_POST", 400);
define("CLIENT_EXCEPTION_PRIVATE_STREAM_NO_AUTH", 401);
define("CLIENT_EXCEPTION_PRIVATE_STREAM_UNAUTHORIZED", 403);
define("CLIENT_EXCEPTION_BAD_QUEUE_MANAGER_KEY", 403);

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

/* ----------------------------------------------------------------------------
 * class RunQueueBadKeyException
 *    Class for a client exception caused by an interfacing queue software not
 *    presenting a valid manager key.
 */
class RunQueueBadKeyException extends ClientException
{
    public $qmkey;

    public function __construct($qmkey)
    {
        $this->qmkey = $qmkey;
        $msg = _('Bad queue manager key was used.');
        parent::__construct($msg, CLIENT_EXCEPTION_BAD_QUEUE_MANAGER_KEY);
    }
}

/* ----------------------------------------------------------------------------
 * class RunQueueOutOfWorkException
 *    Class for a client exception caused by the queue running out of queue
 *    items.  This is not normally an error state.
 */
class RunQueueOutOfWorkException extends ServerException
{
   public function __construct()
   {
      $msg = _('Opportunistic queue manager is out of work (no more items).');
      parent::__construct($msg,0,null,LOG_DEBUG);
   }
}

// END OF FILE
// ============================================================================
?>