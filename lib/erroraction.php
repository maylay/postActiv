<?php
/**
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Error action.
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
 * @category Action
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Base class for displaying HTTP errors
 *
 * @category Action
 * @package  StatusNet
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 */
class ErrorAction extends InfoAction
{
    static $status = array();

    var $code    = null;
    var $message = null;
    var $default = null;

    function __construct($message, $code, $output='php://output', $indent=null)
    {
        parent::__construct(null, $message, $output, $indent);

        $this->code = $code;
        $this->message = $message;
        $this->minimal = GNUsocial::isApi();

        // XXX: hack alert: usually we aren't going to
        // call this page directly, but because it's
        // an action it needs an args array anyway
        $this->prepare($_REQUEST);
    }

    function showPage()
    {
        if (GNUsocial::isAjax()) {
            $this->extraHeaders();
            $this->ajaxErrorMsg();
            exit();
        } if ($this->minimal) {
            // Even more minimal -- we're in a machine API
            // and don't want to flood the output.
            $this->extraHeaders();
            $this->showContent();
        } else {
            parent::showPage();
        }

        // We don't want to have any more output after this
        exit();
    }

    /**
     * Display content.
     *
     * @return nothing
     */
    function showContent()
    {
        $this->element('div', array('class' => 'error'), $this->message);
    }

    function showNoticeForm()
    {
    }

    /**
     * Show an Ajax-y error message
     *
     * Goes back to the browser, where it's shown in a popup.
     *
     * @param string $msg Message to show
     *
     * @return void
     */

    function ajaxErrorMsg()
    {
        $this->startHTML('text/xml;charset=utf-8', true);
        $this->elementStart('head');
        // TRANS: Page title after an AJAX error occurs on the send notice page.
        $this->element('title', null, _('Ajax Error'));
        $this->elementEnd('head');
        $this->elementStart('body');
        $this->element('p', array('id' => 'error'), $this->message);
        $this->elementEnd('body');
        $this->endHTML();
    }
}
?>