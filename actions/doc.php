<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
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
 * PHP version 5
 *
 * Documentation action.
 *
 * @category  Action
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Robin Millette <millette@status.net>
 * @author    Zach Copley <zach@copley.name>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Chris Buttle <chris@gatopelao.org>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

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