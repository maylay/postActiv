<?php
/* ============================================================================
 * Title: Entry Point
 * Main postActiv entry point
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
 * Main postActiv entry point
 *
 * Please note that the software will not execute if the install code is present
 * on a live install.
 *
 * Defines:
 * o INSTALLDIR - root directory of the install where index.php is running from
 * o POSTACTIV  - security constant
 * o GNUSOCIAL  - legacy security constant
 * o STATUSNET  - legacy security constant
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Evan Prodromou
 *  o Gina Haeussge <osd@foosel.net>
 *  o Mike Cochrane <mikec@mikenz.geek.nz>
 *  o Ciaran Gultneiks <ciaran@ciarang.com>
 *  o Robin Millette <robin@millette.info>
 *  o Sarven Capadisli
 *  o Jeffery To <jeffery.to@gmail.com>
 *  o Tom Adams <tom@holizz.com>
 *  o Christopher Vollick <psycotica0@gmail.com>
 *  o Craig Andrews <candrews@integralblue.com>
 *  o Brenda Wallace <shiny@cpan.org>
 *  o Zach Copley
 *  o Brion Vibber <brion@pobox.com>
 *  o James Walker <walkah@walkah.net>
 *  o Siebrand Mazeland <s.mazeland@xs4all.nl>
 *  o Mikael Nordfeldth <mmn@hethane.se>
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

$_startTime = microtime(true);
$_perfCounters = array();

// We provide all our dependencies through our own autoload.
// This will probably be configurable for distributing with
// system packages (like with Debian apt etc. where included
// libraries are maintained through repositories)
set_include_path('.');  // mainly fixes an issue where /usr/share/{pear,php*}/DB/DataObject.php is _old_ on various systems...

define('INSTALLDIR', dirname(__FILE__));
define('POSTACTIV', true);
define('GNUSOCIAL', true);  // compatibility
define('STATUSNET', true);  // compatibility

$user = null;
$action = null;

if (file_exists(INSTALLDIR . '/install.php') & file_exists(INSTALLDIR . '/config.php'))
{
   die("Installation script present on a live install.  Please remove the installation script.");
}

// =============================================================================
// Group: Helper functions
// Functions used to help bootstrap the application.
// -----------------------------------------------------------------------------
// function: getPath
// Returns the path we are operating postActiv under.
//
// Parameters:
// $req
//
// Returns:
// string
function getPath($req)
{
    $p = null;

    if ((common_config('site', 'fancy') || !array_key_exists('PATH_INFO', $_SERVER))
        && array_key_exists('p', $req)
    ) {
        $p = $req['p'];
    } else if (array_key_exists('PATH_INFO', $_SERVER)) {
        $path = $_SERVER['PATH_INFO'];
        $script = $_SERVER['SCRIPT_NAME'];
        if (substr($path, 0, mb_strlen($script)) == $script) {
            $p = substr($path, mb_strlen($script) + 1);
        } else {
            $p = $path;
        }
    } else {
        $p = null;
    }

    // Trim all initial '/'

    $p = ltrim($p, '/');

    return $p;
}

// -----------------------------------------------------------------------------
// Function: handleError
// Logs and then displays error messages
//
// Parameters:
// $error - exception
//
// Return:
// void
function handleError($error)
{
    try {

        if ($error->getCode() == DB_DATAOBJECT_ERROR_NODATA) {
            return;
        }

        $logmsg = "Exception thrown: " . _ve($error->getMessage());
        if ($error instanceof PEAR_Exception && common_config('log', 'debugtrace')) {
            $logmsg .= " PEAR: ". $error->toText();
        }
        // DB queries often end up with a lot of newlines; merge to a single line
        // for easier grepability...
        $logmsg = str_replace("\n", " ", $logmsg);
        common_log(LOG_ERR, $logmsg);

        // @fixme backtrace output should be consistent with exception handling
        if (common_config('log', 'debugtrace')) {
            $bt = $error->getTrace();
            foreach ($bt as $n => $line) {
                common_log(LOG_ERR, formatBacktraceLine($n, $line));
            }
        }
        if ($error instanceof DB_DataObject_Error
            || $error instanceof DB_Error
            || ($error instanceof PEAR_Exception && $error->getCode() == -24)
        ) {
            //If we run into a DB error, assume we can't connect to the DB at all
            //so set the current user to null, so we don't try to access the DB
            //while rendering the error page.
            global $_cur;
            $_cur = null;

            $msg = sprintf(
                // TRANS: Database error message.
                _('The database for %1$s is not responding correctly, '.
                  'so the site will not work properly. '.
                  'The site admins probably know about the problem, '.
                  'but you can contact them at %2$s to make sure. '.
                  'Otherwise, wait a few minutes and try again.'
                ),
                common_config('site', 'name'),
                common_config('site', 'email')
            );

            $erraction = new DBErrorAction($msg, 500);
        } elseif ($error instanceof ClientException) {
            $erraction = new ClientErrorAction($error->getMessage(), $error->getCode());
        } elseif ($error instanceof ServerException) {
            $erraction = new ServerErrorAction($error->getMessage(), $error->getCode(), $error);
        } else {
            // If it wasn't specified more closely which kind of exception it was
            $erraction = new ServerErrorAction($error->getMessage(), 500, $error);
        }
        $erraction->showPage();

    } catch (Exception $e) {
        // TRANS: Error message.
        echo _('An error occurred.');
        exit(-1);
    }
    exit(-1);
}

set_exception_handler('handleError');

// quick check for fancy URL auto-detection support in installer.
if (preg_replace("/\?.+$/", "", $_SERVER['REQUEST_URI']) === preg_replace("/^\/$/", "", (dirname($_SERVER['REQUEST_URI']))) . '/check-fancy') {
    die("Fancy URL support detection succeeded. We suggest you enable this to get fancy (pretty) URLs.");
}

require_once INSTALLDIR . '/lib/common.php';

// -----------------------------------------------------------------------------
// function: formatBacktraceLine
// Format a backtrace line for debug output roughly like debug_print_backtrace() does.
// Exceptions already have this built in, but PEAR error objects just give us the array.
//
// Parameters:
// o $n - int line number
// o $line - per-frame array item from debug_backtrace()
//
// Returns:
// string
function formatBacktraceLine($n, $line)
{
    $out = "#$n ";
    if (isset($line['class'])) $out .= $line['class'];
    if (isset($line['type'])) $out .= $line['type'];
    if (isset($line['function'])) $out .= $line['function'];
    $out .= '(';
    if (isset($line['args'])) {
        $args = array();
        foreach ($line['args'] as $arg) {
            // debug_print_backtrace seems to use var_export
            // but this gets *very* verbose!
            $args[] = gettype($arg);
        }
        $out .= implode(',', $args);
    }
    $out .= ')';
    $out .= ' called at [';
    if (isset($line['file'])) $out .= $line['file'];
    if (isset($line['line'])) $out .= ':' . $line['line'];
    $out .= ']';
    return $out;
}

// -----------------------------------------------------------------------------
// function: setupRW
// Sets up read/write access to the underlying database
//
// Parameters:
// None
//
// Returns:
// Void
function setupRW()
{
    global $config;

    static $alwaysRW = array('session', 'remember_me');

    $rwdb = $config['db']['database'];

    if (Event::handle('StartReadWriteTables', array(&$alwaysRW, &$rwdb))) {

        // We ensure that these tables always are used
        // on the master DB

        $config['db']['database_rw'] = $rwdb;
        $config['db']['ini_rw'] = INSTALLDIR.'/classes/statusnet.ini';

        foreach ($alwaysRW as $table) {
            $config['db']['table_'.$table] = 'rw';
        }

        Event::handle('EndReadWriteTables', array($alwaysRW, $rwdb));
    }

    return;
}


// ============================================================================
// Group: Entry points
// ----------------------------------------------------------------------------
// Function: isLoginAction
// Returns true of the index is being accessed as part of a login action, false
// if not
//
// Parameters:
// $action
//
// Returns
// boolean
function isLoginAction($action)
{
    static $loginActions =  array('login', 'recoverpassword', 'api', 'doc', 'register', 'publicxrds', 'otp', 'opensearch', 'rsd');

    $login = null;

    if (Event::handle('LoginAction', array($action, &$login))) {
        $login = in_array($action, $loginActions);
    }

    return $login;
}

// ----------------------------------------------------------------------------
// Function: main
// Main entry point for the server application
//
// Parameters:
// None
//
// Returns:
// Void
function main()
{
    global $user, $action;

    if (!_have_config()) {
        $msg = sprintf(
            // TRANS: Error message displayed when there is no StatusNet configuration file.
            _("No configuration file found. Try running ".
              "the installation program first."
            )
        );
        $sac = new ServerErrorAction($msg);
        $sac->showPage();
        return;
    }

    // Make sure RW database is setup

    setupRW();

    // XXX: we need a little more structure in this script

    // get and cache current user (may hit RW!)

    $user = common_current_user();

    // initialize language env

    common_init_language();

    $path = getPath($_REQUEST);

    $r = Router::get();

    $args = $r->map($path);

    // If the request is HTTP and it should be HTTPS...
    if (postActiv::useHTTPS() && !postActiv::isHTTPS()) {
        common_redirect(common_local_url($args['action'], $args));
    }

    $args = array_merge($args, $_REQUEST);

    Event::handle('ArgsInitialize', array(&$args));

    $action = basename($args['action']);

    if (!$action || !preg_match('/^[a-zA-Z0-9_-]*$/', $action)) {
        common_redirect(common_local_url('public'));
    }

    // If the site is private, and they're not on one of the "public"
    // parts of the site, redirect to login

    if (!$user && common_config('site', 'private')
        && !isLoginAction($action)
        && !preg_match('/rss$/', $action)
        && $action != 'robotstxt'
        && !preg_match('/^Api/', $action)) {

        // set returnto
        $rargs =& common_copy_args($args);
        unset($rargs['action']);
        if (common_config('site', 'fancy')) {
            unset($rargs['p']);
        }
        if (array_key_exists('submit', $rargs)) {
            unset($rargs['submit']);
        }
        foreach (array_keys($_COOKIE) as $cookie) {
            unset($rargs[$cookie]);
        }
        common_set_returnto(common_local_url($action, $rargs));

        common_redirect(common_local_url('login'));
    }

    $action_class = ucfirst($action).'Action';

    if (!class_exists($action_class)) {
        // TRANS: Error message displayed when trying to perform an undefined action.
        throw new ClientException(_('Unknown action'), 404);
    }

    call_user_func("$action_class::run", $args);
}

// Sometimes, under currently-mysterious circumstances, a spurious empty line
// is getting added to the beginning of the output. Cleaning the output buffer
// works around the issue.
ob_clean();

main();

// XXX: cleanup exit() calls or add an exit handler so
// this always gets called

Event::handle('CleanupPlugin');

// END OF FILE
// =============================================================================
?>
