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
 * ServerException and descendant classes as well as the canonical error 
 * definitions
 *
 * These classes represent various internal server errors that ususally are not
 * fixable by the end user.
 * ----------------------------------------------------------------------------
 * @category  Exception
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Zach Copley
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2012 StatusNet, Inc
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 * ============================================================================
 */

if (!defined('POSTACTIV')) { exit(1); }

# -----------------------------------------------------------------------------
# Canonical error codes
# The codes for server errors should reflect the closest appropriate HTTP Status
# Code, here.  See https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
define("SERVER_EXCEPTION", 500);
define("SERVER_EXCEPTION_UNSUPPORTED_MEDIA", 416);
define("SERVER_EXCEPTION_ALREADY_FULFILLED", 226);
define("SERVER_EXCEPTION_INVALID_PKEY", 500);
define("SERVER_EXCEPTION_UNKNOWN_URI", 404);
define("SERVER_EXCEPTION_UNKNOWN_MIME_EXTENSION", 500);
define("SERVER_EXCEPTION_UNKNOWN_EXTENSION", 416);
define("SERVER_EXCEPTION_FILE_NOT_HERE", 410);
define("SERVER_EXCEPTION_FILE_NOT_FOUND", 404);
define("SERVER_EXCEPTION_NO_RESULT_FOUND", 404);
define("SERVER_EXCEPTION_USER_NOT_FOUND", 404);
define("SERVER_EXCEPTION_GROUP_NOT_FOUND", 404);
define("SERVER_EXCEPTION_NO_HANDLER_FOR_TRANSPORT", 500);
define("SERVER_EXCEPTION_PROFILE_NOT_FOUND", 404);
define("SERVER_EXCEPTION_PARENT_NOTICE_NOT_FOUND", 404);
define("SERVER_EXCEPTION_NO_OBJECT_TYPE", 422);
define("SERVER_EXCEPTION_CANT_FIND_ROUTE", 404);
define("SERVER_EXCEPTION_METHOD_NOT_IMPLEMENTED", 415);
define("SERVER_EXCEPTION_ACCT_WITH_NO_URI", 500);
define("SERVER_EXCEPTION_MALFORMED_CONFIG", 500);
define("SERVER_EXCEPTION_INVALID_FILENAME", 500);
define("SERVER_EXCEPTION_INVALID_URI", 404);
define("SERVER_EXCEPTION_CANT_HASH", 500);
define("SERVER_EXCEPTION_FEED_SUB_FAILURE", 416);
define("SERVER_EXCEPTION_OSTATUS_SHADOW_FOUND", 500);
define("SERVER_EXCEPTION_WEBFINGER_FAILED", 500);

/* ----------------------------------------------------------------------------
 * class ServerException
 *    Subclass of PHP Exception for server errors. The user typically can't
 *    fix these.
 */
class ServerException extends Exception
{
    public function __construct($message = null, $code = SERVER_EXCEPTION, Exception $previous = null, $severity = LOG_ERR) {
        parent::__construct($message, $code);
        $file = $this->file;
        $line = $this->line;
        if ($severity==LOG_DEBUG) {
           common_debug($message . " (" . $code . ")");
        elseif ($severity==LOG_INFO) {
           common_log($severity, $message . " (" . $code .")");
        } else {
           common_log($severity, $message . " (" . $code .")  Exception raised in " . $file . " on line " . $line . ".");
        }
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

/* ----------------------------------------------------------------------------
 * class AlreadyFulfilledException
 *    Class for an exception when trying to do something that was probably
 *    already done.
 *
 *    This is a common case for example when remote sites are not up to date
 *    with our database. For example subscriptions, where a remote user may be 
 *    unsubscribed from our user, but they request it anyway.
 *
 *    This exception should be caught in a manner that lets the execution 
 *    continue _as if_ the desired action did what it was supposed to do.
 */
class AlreadyFulfilledException extends ServerException
{
    public function __construct($msg=null)
    {
        if ($msg === null) {
            // TRANS: Exception text when attempting to perform something which seems already done.
            $msg = _('Trying to do something that was already done.');
        }

        parent::__construct($msg, SERVER_EXCEPTION_ALREADY_FULFILLED);
    }
}

/* ---------------------------------------------------------------------------
 * class UnsupportedMediaException
 *    Class for a server exception caused by handling an unsupported media 
 *    type, typically through an attachment/file upload.
 */
class UnsupportedMediaException extends ServerException
{
    public function __construct($msg, $path=null)
    {
        //common_debug(sprintf('UnsupportedMediaException "%1$s". File path (if given): "%2$s"', $msg, $path));
        parent::__construct($msg, SERVER_EXCEPTION_UNSUPPORTED_MEDIA);
    }
}

/* ---------------------------------------------------------------------------
 * class UnsupportedMediaException
 *    A specific variant of UnsupportedMediaException where we do not have a
 *    thumbnail generated for the given file.
 */
class UseFileAsThumbnailException extends UnsupportedMediaException
{
    public $file = null;

    public function __construct(File $file)
    {
        $this->file = $file;
        parent::__construct('Thumbnail not generated', $this->file->getPath());
    }
}

/* ----------------------------------------------------------------------------
 * class EmptyPkeyValueException
 *    Class for a server exception caused by an empty primary key, which likely
 *    means the database has gone wonky.
 */
class EmptyPkeyValueException extends ServerException
{
    public function __construct($called_class, $key=null)
    {
        // FIXME: translate the 'not specified' case?
        parent::__construct(sprintf(_('Empty primary key (%1$s) value was given to query for a "%2$s" object'),
                                        is_null($key) ? 'not specified' : _ve($key),$called_class), SERVER_EXCEPTION_INVALID_PKEY, null, LOG_DEBUG);
    }
}

/* ----------------------------------------------------------------------------
 * class UnknownUriException
 *    Class for server exception caused by something specifying a URI that we
 *    cannont find or reach.
 */
class UnknownUriException extends ServerException
{
    public $object_uri = null;

    public function __construct($object_uri, $msg=null)
    {
        $this->object_uri = $object_uri;

        if ($msg === null) {
            // TRANS: Exception text shown when no object found with certain URI
            // TRANS: %s is the URI.
            $msg = sprintf(_('No object found with URI "%s"'), $this->object_uri);
            common_debug(__CLASS__ . ': ' . $msg);
        }

        parent::__construct($msg, SERVER_EXCEPTION_UNKNOWN_URI);
    }
}

/* ----------------------------------------------------------------------------
 * class UnknownMimeExtensionException
 *    Class for unknown MIME extension exception
 *    Thrown when we don't know the file extension for a given MIME type.
 *    This generally means that all files are accepted since if we have
 *    a list of known MIMEs then they have extensions coupled to them.
 */
class UnknownMimeExtensionException extends ServerException
{
    public function __construct($mimetype)
    {
        // TRANS: We accept the file type (we probably just accept all files)
        // TRANS: but don't know the file extension for it.
        $msg = sprintf(_('Supported mimetype but unknown extension relation: %1$s'), _ve($mimetype));
        parent::__construct($msg, SERVER_EXCEPTION_UNKNOWN_MIME_EXTENSION);
    }
}

/* ----------------------------------------------------------------------------
 * class UnknownExtensionMimeException
 *    Class for unknown extension MIME type exception
 *    Inverse of UnknownMimeExtension - error for when we know the extension 
 *    but not the MIME type.
 */
class UnknownExtensionMimeException extends ServerException
{
    public function __construct($ext)
    {
        // TRANS: We accept the file type (we probably just accept all files)
        // TRANS: but don't know the file extension for it. %1$s is the extension.
        $msg = sprintf(_('Unknown MIME type for file extension: %1$s'), _ve($ext));

        parent::__construct($msg, SERVER_EXCEPTION_UNKNOWN_EXTENSION);
    }
}

/* ----------------------------------------------------------------------------
 * class NoticeSaveException
 *    Class for a server exception caused when a notice cannot be saved.
 */
class NoticeSaveException extends ServerException
{
}

/* ----------------------------------------------------------------------------
 * class FileNotStoredLocallyException
 *    Class for a server exception caused by looking in the local filesystem
 *    for something we store remotely.
 */
class FileNotStoredLocallyException extends ServerException
{
    public $file = null;

    public function __construct(File $file)
    {
        $this->file = $file;
        $msg = 'Requested local URL for a file that is not stored locally with id=='._ve($this->file->getID());
        common_debug($msg);
        parent::__construct(_('Requested local URL for a file that is not stored locally.'), SERVER_EXCEPTION_FILE_NOT_HERE);
    }
}

/* ----------------------------------------------------------------------------
 * class FileNotFoundException
 *    Class for a server exception caused by being unable to find something we
 *    know should be stored locally.
 */
class FileNotFoundException extends ServerException
{
    public $path = null;

    public function __construct($path)
    {
        $this->path = $path;
        $msg = 'File not found exception for: '._ve($this->path);
        common_debug($msg);
        parent::__construct(_('File not found in filesystem.'), SERVER_EXCEPTION_FILE_NOT_FOUND);
    }
}

/* ----------------------------------------------------------------------------
 * class NoResultException
 *    Class for an exception when a database lookup returns no results
 */
class NoResultException extends ServerException
{
    public $obj;    // The object with query that gave no results

    public function __construct(Memcached_DataObject $obj)
    {
        $this->obj = $obj;
        // We could log an entryhere with the search parameters
        $msg = sprintf(_('No result found on %s lookup.'), get_class($obj));
        parent::__construct($msg, SERVER_EXCEPTION_NO_RESULT_FOUND, null, LOG_DEBUG);
    }
}

/* ----------------------------------------------------------------------------
 * class NoSuchUserException
 *    Class for a server exception caused by a user lookup which fails.
 */
class NoSuchUserException extends ServerException
{
    public $data = array();

    /**
     * constructor
     *
     * @param array $data user search criteria
     */

    public function __construct(array $data)
    {
        // filter on unique keys for local users
        foreach(array('id', 'email', 'nickname') as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $this->data[$key] = $data[$key];
            }
        }

        // Here we could log the failed lookup
        $msg = _('No such user found.');
        parent::__construct($msg, SERVER_EXCEPTION_USER_NOT_FOUND, null, LOG_DEBUG);
    }
}

/* ----------------------------------------------------------------------------
 * class NoSuchGroupException
 *    Class for a server exception caused by a group lookup which fails.
 */
class NoSuchGroupException extends ServerException
{
    public $data = array();

    /**
     * constructor
     *
     * @param array $data User_group search criteria
     */

    public function __construct(array $data)
    {
        // filter on unique keys for User_group entries
        foreach(array('id', 'profile_id') as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $this->data[$key] = $data[$key];
            }
        }

        // Here we could log the failed lookup
        $msg = _('No such group found.');
        parent::__construct($msg, SERVER_EXCEPTION_GROUP_NOT_FOUND, null, LOG_DEBUG);
    }
}

/* ----------------------------------------------------------------------------
 * class NoQueueHandlerException
 *    Class for a server exception caused by finding no queue handler for a 
 *    given transport.  This likely is a misconfiguration.
 */
class NoQueueHandlerException extends ServerException
{
    public $transport;    // The object with query that gave no results

    public function __construct($transport)
    {
        $this->transport = $transport;
        $msg = sprintf(_('No queue handler found for transport %s.'), _ve($this->transport));
        parent::__construct($msg, SERVER_EXCEPTION_NO_HANDLER_FOR_TRANSPORT, null, LOG_ERR);
    }
}
/* -----------------------------------------------------------------------------
 * class NoProfileException
 *    Parent class for an exception when a profile is missing.
 */
class NoProfileException extends ServerException
{
    public $profile_id = null;

    public function __construct($profile_id, $msg=null)
    {
        $this->profile_id = $profile_id;

        if ($msg === null) {
            // TRANS: Exception text shown when no profile can be found for a user.
            // TRANS: %u is a profile ID (number).
            $msg = sprintf(_('There is no profile with id==%u'), $this->profile_id);
        }

        parent::__construct($msg, SERVER_EXCEPTION_PROFILE_NOT_FOUND, null, LOG_INFO);
    }
}

/* ----------------------------------------------------------------------------
 * class UserNoProfileException
 *    Class for an exception when the user profile is missing
 */
class UserNoProfileException extends NoProfileException
{
    protected $user = null;

   /**
    * constructor
    *
    * @param User $user User that's missing a profile
    */
   public function __construct(User $user)
   {
      $this->user = $user;

      // TRANS: Exception text shown when no profile can be found for a user.
      // TRANS: %1$s is a user nickname, $2$d is a user ID (number).
      $msg = sprintf(_('User %1$s (%2$d) has no profile record.'),
                       $user->nickname, $user->id);

      parent::__construct($user->id, $msg);
   }

   /**
    * Accessor for user
    *
    * @return User the user that triggered this exception
    */
   protected function getUser() {
      return $this->user;
   }
}

/* ----------------------------------------------------------------------------
 * class GroupNoProfileException
 *    Basically UserNoProfileException, but for groups
 */
class GroupNoProfileException extends NoProfileException
{
   protected $group = null;

   /**
    * constructor
    *
    * @param User_group $user User_group that's missing a profile
    */
   public function __construct(User_group $group)
   {
      $this->group = $group;

      // TRANS: Exception text shown when no profile can be found for a group.
      // TRANS: %1$s is a group nickname, $2$d is a group profile_id (number).
      $message = sprintf(_('Group "%1$s" (%2$d) has no profile record.'),
                           $group->nickname, $group->getID());

      parent::__construct($group->profile_id, $message);
   }

   /**
    * Accessor for user
    *
    * @return User_group the group that triggered this exception
    */
   protected function getGroup()
   {
       return $this->group;
   }
}

/* ----------------------------------------------------------------------------
 * class NoParentNoticeException
 *    Class for a server exception caused by a notice not having a parent.
 *    This happens a lot, since many notices are not part of an existing convo,
 *    so I have put it in LOG_DEBUG severity level.
 */
class NoParentNoticeException extends ServerException
{
    public $notice;    // The notice which has no parent

    public function __construct(Notice $notice)
    {
        $this->notice = $notice;
        $msg = sprintf(_('No parent for notice with ID "%s".'), $this->notice->id);
        parent::__construct($msg, SERVER_EXCEPTION_PARENT_NOTICE_NOT_FOUND, null, LOG_DEBUG);
    }
}

/* ----------------------------------------------------------------------------
 * class NoAvatarException
 *    Class for a server exception caused by not being able to find the avatar
 *    for a given user.  A user might not have set one, so I put this at 
 *    LOG_DEBUG severity since it can happen during normal operation.
 */
class NoAvatarException extends NoResultException
{
    public $target;

    public function __construct(Profile $target, Avatar $obj)
    {
        $this->target = $target;
        parent::__construct($obj);
    }
}

/* ----------------------------------------------------------------------------
 * class NoObjectTypeException
 *    Class for a server exception caused by a notice having no given type;
 *    most often this will happen because of an unrecognized activity verb.
 *    Since this is not neccesarialy an error, but rather a federation
 *    incompatability, I have put it in LOG_WARNING severity.
 */
class NoObjectTypeException extends ServerException
{
    public $stored;    // The object with query that gave no results

    public function __construct(Notice $stored)
    {
        $this->stored = $stored;
        $msg =
        parent::__construct($msg, SERVER_EXCEPTION_NO_OBJECT_TYPE, null, LOG_WARNING);
    }
}

/* ----------------------------------------------------------------------------
 * class NoRouteMapException
 *    Class for a server exception caused by not finding a route map to the 
 *    given location.  This happens in normal operation with 404 errors but can
 *    also happen with malfunctioning or misprogrammed plugins, so I have given
 *    it LOG_DEBUG severity.
 */
class NoRouteMapException extends ServerException
{
    public $path;    // The object with query that gave no results

    public function __construct($path)
    {
        $this->path = $path;
        $msg = sprintf(_('Could not find a handler for the given path %s.'), _ve($this->path));
        parent::__construct($msg, SERVER_EXCEPTION_CANT_FIND_ROUTE, null, LOG_DEBUG);
    }
}

/* -----------------------------------------------------------------------------
 * class MethodNotImplementedException
 *    Class for a server exception caused when we recognize what the client is
 *    attempting to request, but postActiv does not currently support it.  This
 *    is caused internally by malfunctioning plugins, usually, so I have assigned
 *    it LOG_WARNING severity.
 */
class MethodNotImplementedException extends ServerException
{
   public function __construct($method)
   {
      $msg = sprintf(_('Method %s not implemented'), $method);
      parent::__construct($msg, SERVER_EXCEPTION_METHOD_NOT_IMPLEMENTED, null, LOG_WARNING);
   }
}

/* ----------------------------------------------------------------------------
 * class ProfileNoAcctUriException
 *    Class for a server exception caused by finding no URI associated with an
 *    account.  This represents a malformed DB entry, usually.
 */
class ProfileNoAcctUriException extends ServerException
{
    public $profile = null;

    public function __construct(Profile $profile, $msg=null)
    {
        $this->profile = $profile;

        if ($msg === null) {
            // TRANS: Exception text shown when no profile can be found for a user.
            // TRANS: %1$s is a user nickname, $2$d is a user ID (number).
            $msg = sprintf(_('Could not get an acct: URI for profile with id==%u'), $this->profile->id);
        }

        parent::__construct($msg, SERVER_EXCEPTION_ACCT_WITH_NO_URI);
    }
}

/* ----------------------------------------------------------------------------
 * class ConfigException
 *    Class for a server exception caused by a malformed config.php
 */
class ConfigException extends ServerException
{
    public function __construct($message=null) {
        parent::__construct($message, SERVER_EXCEPTION_MALFORMED_CONFIG);
    }
}

/* ----------------------------------------------------------------------------
 * class InvalidFilenameException
 *    Class for a server exception caused by passing an illegal filename as a
 *    parameter.  This represents likely a failure to save something, so I have
 *    assigned it a LOG_WARNING severity.
 */
class InvalidFilenameException extends ServerException
{
    public $filename = null;

    public function __construct($filename)
    {
        $this->filename = $filename;
        // TODO: We could log an entry here with the search parameters
        $msg = _('Invalid filename.');
        parent::__construct($msg, SERVER_EXCEPTION_INVALID_FILENAME, null, LOG_WARNING);
    }
}

/* ----------------------------------------------------------------------------
 * class InvalidUriException
 *    Class for an exception when a URL is invalid.  I put this in LOG_INFO 
 *    since it can be useful to find federation errors in normal operation.
 */
class InvalidUrlException extends ServerException
{
    public $url = null;

    public function __construct($url)
    {
        $this->url = $url;
        // TODO: We could log an entry here with the search parameters
        $msg = _('Invalid URL.');
        parent::__construct($msg, SERVER_EXCEPTION_INVALID_URI, null, LOG_INFO);
    }
}

/* ----------------------------------------------------------------------------
 * class PasswordHashException
 *    Class for a server exception caused by password hashing to fail.  Since
 *    this compromises the security of client accounts, I have assigned this
 *    LOG_CRITICAL severity.
 */
class PasswordHashException extends ServerException
{
    public $obj;    // The object with query that gave no results

    public function __construct($msg=null, $code=SERVER_EXCEPTION_CANT_HASH)
    {
        if ($msg === null) {
            $msg = _('Password hashing failed.');
        }

        parent::__construct($msg, $code, null, LOG_CRITICAL);
    }
}

/* ----------------------------------------------------------------------------
 * class FeedSubException
 *   Class for a server exception caused by the server being unable to process
 *   a feedsub properly.  This is probably fairly integral, but doesn't usually
 *   stop execution, so LOG_WARNING it is.  It will usually only happen when we 
 *   have got the sub content, but it's malformed in some way, so not an error
 *   per se, but definitely worth a warning.
 */
class FeedSubException extends ServerException
{
    function __construct($msg=null)
    {
        $type = get_class($this);
        if ($msg) {
            parent::__construct("$type: $msg", SERVER_EXCEPTION_FEED_SUB_FAILURE, null, LOG_WARNING);
        } else {
            parent::__construct($type, SERVER_EXCEPTION_FEED_SUB_FAILURE, null, LOG_WARNING);
        }
    }
} 

/* ----------------------------------------------------------------------------
 * class OStatusShadowException
 *    Exception indicating we've got a remote reference to a local user,
 *    not a remote user!
 *
 *    If we can ue a local profile after all, it's available as $e->profile.
 *    -mmn
 *
 *    Most of the time this can happen entirely innocently, especially with
 *    older versions of GNU social or StatusNet, but it is worth noting, so I
 *    have assigned it LOG_INFO severity. -mb
 */
class OStatusShadowException extends Exception
{
    public $profile;

    /**
     * @param Profile $profile
     * @param string $message
     */
    function __construct(Profile $profile, $message) {
        $this->profile = $profile;
        parent::__construct($message, SERVER_EXCEPTION_OSTATUS_SHADOW_FOUND, null, LOG_INFO);
    }
}

/* ----------------------------------------------------------------------------
 * class WebFingerReconstructionException
 *    Class for a server exception cause when a WebFinger acct: URI can not be
 *    constructed using the data we have in a Profile.
 */

class WebFingerReconstructionException extends ServerException
{
    public $target = null;

    public function __construct(Profile $target)
    {
        $this->target = $target;

        // We could log an entry here with the search parameters
        $msg = _('WebFinger URI generation failed.');
        parent::__construct($msg, SERVER_EXCEPTION_WEBFINGER_FAILED, null, LOG_INFO);
    }
}

?>