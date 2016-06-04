<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * An exception for private streams
 *
 * @category  Exception
 * @package   GNUsocial
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @copyright 2016 Free Software Foundation, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 */

if (!defined('POSTACTIV')) { exit(1); }

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
        parent::__construct($msg, ($reader instanceof Profile ? 403 : 401));
    }
}
?>