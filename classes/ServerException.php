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

/* ----------------------------------------------------------------------------
 * class ServerException
 *    Subclass of PHP Exception for server errors. The user typically can't
 *    fix these.
 */
class ServerException extends Exception
{
    public function __construct($message = null, $code = SERVER_EXCEPTION, $severity = "LOG_ERR") {
        parent::__construct($message, $code);
        common_log($severity, $message . " (" . $code .")");
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
                                        is_null($key) ? 'not specified' : _ve($key),$called_class), SERVER_EXCEPTION_INVALID_PKEY);
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
?>