<?php
/* ============================================================================
 * Title: Doc
 * Documentation action.
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
 * Documentation action.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Robin Millette <millette@status.net>
 * o Zach Copley
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chris Buttle <chris@gatopelao.org>
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
 * Documentation class.
 */
class DocAction extends ManagedAction
{
    var $output   = null;
    var $filename = null;
    var $title    = null;

    protected function doPreparation()
    {
        $this->title  = $this->trimmed('title');
        if (!preg_match('/^[a-zA-Z0-9_-]*$/', $this->title)) {
            $this->title = 'help';
        }
        $this->output = null;

        $this->loadDoc();
    }

    public function title()
    {
        return ucfirst($this->title);
    }

    /**
     * Display content.
     *
     * Shows the content of the document.
     *
     * @return void
     */
    function showContent()
    {
        $this->raw($this->output);
    }

    function showNoticeForm()
    {
        // no notice form
    }

    /**
     * These pages are read-only.
     *
     * @param array $args unused.
     *
     * @return boolean read-only flag (false)
     */
    function isReadOnly($args)
    {
        return true;
    }

    function loadDoc()
    {
        if (Event::handle('StartLoadDoc', array(&$this->title, &$this->output))) {

            $paths = DocFile::defaultPaths();

            $docfile = DocFile::forTitle($this->title, $paths);

            if (empty($docfile)) {
                // TRANS: Client exception thrown when requesting a document from the documentation that does not exist.
                // TRANS: %s is the non-existing document.
                throw new ClientException(sprintf(_('No such document "%s".'), $this->title), 404);
            }

            $this->output = $docfile->toHTML();

            Event::handle('EndLoadDoc', array($this->title, &$this->output));
        }
    }

    function showLocalNav()
    {
        $menu = new DocNav($this);
        $menu->show();
    }
}

class DocNav extends Menu
{
    function show()
    {
        if (Event::handle('StartDocNav', array($this))) {
            $stub = new HomeStubNav($this->action);
            $this->submenu(_m('MENU','Home'), $stub);

            $docs = new DocListNav($this->action);
            $this->submenu(_m('MENU','Docs'), $docs);
            
            Event::handle('EndDocNav', array($this));
        }
    }
}

class DocListNav extends Menu
{
    function getItems()
    {
        $items = array();

        if (Event::handle('StartDocsMenu', array(&$items))) {

            $items = array(array('doc',
                                 array('title' => 'help'),
                                 _m('MENU', 'Help'),
                                 _('Getting started'),
                                 'nav_doc_help'),
                           array('doc',
                                 array('title' => 'about'),
                                 _m('MENU', 'About'),
                                 _('About this site'),
                                 'nav_doc_about'),
                           array('doc',
                                 array('title' => 'faq'),
                                 _m('MENU', 'FAQ'),
                                 _('Frequently asked questions'),
                                 'nav_doc_faq'),
                           array('doc',
                                 array('title' => 'contact'),
                                 _m('MENU', 'Contact'),
                                 _('Contact info'),
                                 'nav_doc_contact'),
                           array('doc',
                                 array('title' => 'tags'),
                                 _m('MENU', 'Tags'),
                                 _('Using tags'),
                                 'nav_doc_tags'),
                           array('doc',
                                 array('title' => 'groups'),
                                 _m('MENU', 'Groups'),
                                 _('Using groups'),
                                 'nav_doc_groups'),
                           array('doc',
                                 array('title' => 'api'),
                                 _m('MENU', 'API'),
                                 _('RESTful API'),
                                 'nav_doc_api'));

            Event::handle('EndDocsMenu', array(&$items));
        }
        return $items;
    }
}
?>