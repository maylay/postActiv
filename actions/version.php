<?php
/* ============================================================================
 * Title: Version
 * Show version information for this software and plugins
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
 * Show version information for this software and plugins
 *
 * A page that shows version information for this site. Helpful for
 * debugging, for giving credit to authors, and for linking to more
 * complete documentation for admins.
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Sarven Capadisli
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Craig Andrews <candrews@integralblue.com>
 * o Zach Copley
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


// ============================================================================
// Class: VersionAction
// Version info page
//
// Variables:
// o pluginVersions - array to hold plugin information
// o contributors - HARDCODED array of contributors
class VersionAction extends Action
{
   var $pluginVersions = array();

   // -------------------------------------------------------------------------
   // Function: isReadOnly
   // Return true since we're read-only.
   //
   // Param:
   // o array $args -  other arguments
   //
   // Returns:
   // o boolean is read only action?
   function isReadOnly($args) {
      return true;
   }

   // -------------------------------------------------------------------------
   // Function: title
   // Returns the page title
   //
   // @return string page title
   function title() {
      // TRANS: Title for version page. %1$s is the engine name, %2$s is the engine version.
      return sprintf(_('%1$s %2$s'), GNUSOCIAL_ENGINE, GNUSOCIAL_VERSION);
   }

   // -------------------------------------------------------------------------
   // Function: prepare
   // Prepare to run
   //
   // Fire off an event to let plugins report their
   // versions.
   //
   // Parameters:
   // o array $args - array misc. arguments
   //
   // Returns:
   // o boolean success
   protected function prepare(array $args=array()) {
      try {
         parent::prepare($args);
         Event::handle('PluginVersion', array(&$this->pluginVersions));
         return true;
      } catch (exception $e) {
         return false;
      }
   }

   // -------------------------------------------------------------------------
   // Function: handle
   // Shows a page with the version information in the
   // content area.
   //
   // Parameters:
   // o array $args - ignored.
   //
   // Returns:
   // o void
   protected function handle() {
      parent::handle();
      $this->showPage();
   }

   // -------------------------------------------------------------------------
   // Function: showContentBlock
   // Override to add h-entry, and content-inner classes
   //
   // Returns:
   // o void
   function showContentBlock() {
      $this->elementStart('div', array('id' => 'content', 'class' => 'h-entry'));
      $this->showPageTitle();
      $this->showPageNoticeBlock();
      $this->elementStart('div', array('id' => 'content_inner',
                                          'class' => 'e-content'));
      // show the actual content (forms, lists, whatever)
      $this->showContent();
      $this->elementEnd('div');
      $this->elementEnd('div');
   }

    /*
    * Overrride to add entry-title class
    *
    * @return void
    */
   function showPageTitle() {
      $this->element('h1', array('class' => 'entry-title'), $this->title());
   }


   // -------------------------------------------------------------------------
   // Function: showContent
   // Show version information
   //
   // TODO: This will need templated, when Smarty is implemented
   //
   // @return void
   function showContent() {
      $this->elementStart('p');
      // TRANS: Content part of engine version page.
      // TRANS: %1$s is the engine name (GNU social) and %2$s is the GNU social version.
      $this->raw(sprintf(_('This site is powered by %1$s version %2$s, '.
                           'Copyright 2008-2013 StatusNet, Inc. '.
                           'and contributors.'),
                           XMLStringer::estring('a', array('href' => GNUSOCIAL_ENGINE_URL),
                                                // TRANS: Engine name.
                                                GNUSOCIAL_ENGINE),
                           GNUSOCIAL_VERSION));
      $this->elementEnd('p');
      // TRANS: Header for engine software contributors section on the version page.
      $this->element('h2', null, _('Contributors'));
      sort($this->contributors);
      $this->element('p', null, implode(', ', $this->contributors));
      // TRANS: Header for engine software license section on the version page.
      $this->element('h2', null, _('License'));
      $this->element('p', null,
                       // TRANS: Content part of engine software version page. %1s is engine name
                       sprintf(_('%1$s is free software: you can redistribute it and/or modify '.
                         'it under the terms of the GNU Affero General Public License as published by '.
                         'the Free Software Foundation, either version 3 of the License, or '.
                         '(at your option) any later version.'), GNUSOCIAL_ENGINE));
      $this->element('p', null,
                       // TRANS: Content part of engine software version page.
                       _('This program is distributed in the hope that it will be useful, '.
                         'but WITHOUT ANY WARRANTY; without even the implied warranty of '.
                         'MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the '.
                         'GNU Affero General Public License for more details.'));
      $this->elementStart('p');
      // TRANS: Content part of engine version page.
      // TRANS: %s is a link to the AGPL license with link description "http://www.gnu.org/licenses/agpl.html".
      $this->raw(sprintf(_('You should have received a copy of the GNU Affero General Public License '.
                           'along with this program.  If not, see %s.'),
                           XMLStringer::estring('a', array('href' => 'http://www.gnu.org/licenses/agpl.html'),
                                                'http://www.gnu.org/licenses/agpl.html')));
      $this->elementEnd('p');
      // XXX: Theme information?
      if (count($this->pluginVersions)) {
         // TRANS: Header for engine plugins section on the version page.
         $this->element('h2', null, _('Plugins'));
         $this->elementStart('table', array('id' => 'plugins_enabled'));
         $this->elementStart('thead');
         $this->elementStart('tr');
         // TRANS: Column header for plugins table on version page.
         $this->element('th', array('id' => 'plugin_name'), _m('HEADER','Name'));
         // TRANS: Column header for plugins table on version page.
         $this->element('th', array('id' => 'plugin_version'), _m('HEADER','Version'));
         // TRANS: Column header for plugins table on version page.
         $this->element('th', array('id' => 'plugin_authors'), _m('HEADER','Author(s)'));
         // TRANS: Column header for plugins table on version page.
         $this->element('th', array('id' => 'plugin_description'), _m('HEADER','Description'));
         $this->elementEnd('tr');
         $this->elementEnd('thead');
         $this->elementStart('tbody');
         foreach ($this->pluginVersions as $plugin) {
            $this->elementStart('tr');
            if (array_key_exists('homepage', $plugin)) {
               $this->elementStart('th');
               $this->element('a', array('href' => $plugin['homepage']),
                              $plugin['name']);
               $this->elementEnd('th');
            } else {
               $this->element('th', null, $plugin['name']);
            }
            $this->element('td', null, $plugin['version']);
            if (array_key_exists('author', $plugin)) {
               $this->element('td', null, $plugin['author']);
            }
            if (array_key_exists('rawdescription', $plugin)) {
               $this->elementStart('td');
               $this->raw($plugin['rawdescription']);
               $this->elementEnd('td');
            } else if (array_key_exists('description', $plugin)) {
               $this->element('td', null, $plugin['description']);
            }
            $this->elementEnd('tr');
         }
      $this->elementEnd('tbody');
      $this->elementEnd('table');
      }
   }

    var $contributors = array('Evan Prodromou (StatusNet)',
                              'Zach Copley (StatusNet)',
                              'Earle Martin (StatusNet)',
                              'Marie-Claude Doyon (StatusNet)',
                              'Sarven Capadisli (StatusNet)',
                              'Robin Millette (StatusNet)',
                              'Ciaran Gultnieks',
                              'Michael Landers',
                              'Ori Avtalion',
                              'Garret Buell',
                              'Mike Cochrane',
                              'Matthew Gregg',
                              'Florian Biree',
                              'Erik Stambaugh',
                              'drry',
                              'Gina Haeussge',
                              'Tryggvi Björgvinsson',
                              'Adrian Lang',
                              'Meitar Moscovitz',
                              'Sean Murphy',
                              'Leslie Michael Orchard',
                              'Eric Helgeson',
                              'Ken Sedgwick',
                              'Brian Hendrickson',
                              'Tobias Diekershoff',
                              'Dan Moore',
                              'Fil',
                              'Jeff Mitchell',
                              'Brenda Wallace',
                              'Jeffery To',
                              'Federico Marani',
                              'Craig Andrews',
                              'mEDI',
                              'Brett Taylor',
                              'Brigitte Schuster',
                              'Brion Vibber (StatusNet)',
                              'Siebrand Mazeland',
                              'Samantha Doherty (StatusNet)',
                              'Mikael Nordfeldth (FSF)');
}

// END OF FILE
// ============================================================================
?>