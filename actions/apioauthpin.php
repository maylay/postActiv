<?php
/* ============================================================================
 * Title: APIOAuthPin
 * Action for displaying an OAuth verifier pin
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
 * Action for displaying an OAuth verifier pin
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
 * Class for displaying an OAuth verifier pin
 *
 * XXX: I'm pretty sure we don't need to check the logged in state here. -- Zach
 */
class ApiOAuthPinAction extends InfoAction
{
    function __construct($title, $message, $verifier, $desktopMode = false)
    {
        $this->verifier    = $verifier;
        $this->title       = $title;
        $this->desktopMode = $desktopMode;
        parent::__construct($title, $message);
    }

    /**
     * Show body - override to add a special CSS class for the pin pages's
     * "desktop mode" (minimal display)
     *
     * Calls template methods
     *
     * @return nothing
     */
    function showBody()
    {
        $bodyClasses = array();

        if ($this->desktopMode) {
            $bodyClasses[] = 'oauth-desktop-mode';
        }

        if (common_current_user()) {
            $bodyClasses[] = 'user_in';
        }

        $attrs = array('id' => strtolower($this->trimmed('action')));

        if (!empty($bodyClasses)) {
            $attrs['class'] = implode(' ', $bodyClasses);
        }

        $this->elementStart('body', $attrs);

        $this->elementStart('div', array('id' => 'wrap'));
        if (Event::handle('StartShowHeader', array($this))) {
            $this->showHeader();
            Event::handle('EndShowHeader', array($this));
        }
        $this->showCore();
        if (Event::handle('StartShowFooter', array($this))) {
            $this->showFooter();
            Event::handle('EndShowFooter', array($this));
        }
        $this->elementEnd('div');
        $this->showScripts();
        $this->elementEnd('body');
    }

    /**
     * A local menu
     *
     * Shows different login/register actions.
     *
     * @return void
     */
    function showLocalNav()
    {
        // NOP
    }

    /*
     * Override - suppress output in "desktop" mode
     */
    function showHeader()
    {
        if ($this->desktopMode == false) {
            parent::showHeader();
        }
    }

    /*
     * Override - suppress output in "desktop" mode
     */
    function showAside()
    {
        if ($this->desktopMode == false) {
            parent::showAside();
        }
    }

    /*
     * Override - suppress output in "desktop" mode
     */
    function showFooter()
    {
        if ($this->desktopMode == false) {
            parent::showFooter();
        }
    }

    /**
     * Show site notice.
     *
     * @return nothing
     */
    function showSiteNotice()
    {
        // NOP
    }

    /**
     * Show notice form.
     *
     * Show the form for posting a new notice
     *
     * @return nothing
     */
    function showNoticeForm()
    {
        // NOP
    }

    /**
     * Display content.
     *
     * @return nothing
     */
    function showContent()
    {
        $this->element('div', array('class' => 'info'), $this->message);
        $this->element('div', array('id' => 'oauth_pin'), $this->verifier);
    }
}

// END OF FILE
// ============================================================================
?>