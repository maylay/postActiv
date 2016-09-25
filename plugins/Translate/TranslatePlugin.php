<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   Translate - a plugin to provide notice translations on demand using an
 * Apertium-APy server interfacing with postActiv                            *
   Copyright (C) 2016 Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *                                                                           *
   @category     Notices
 * @package      postActiv                                                   *
   @author       Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright    2016 Maiyannah Bishop                                       *
   @license      http://www.fsf.org/licensing/licenses/gpl-3.0.html GPL 3.0
 * @link         http://postactiv.com/                                       *
   @dependancies None
 *  ------------------------------------------------------------------------ */

if (!defined('POSTACTIV')) { exit(1); }


class TranslatePlugin extends Plugin {
   function onPluginVersion(array &$versions)
   {
      $versions[] = array('name' => 'Translate',
         'version' => '0.1',
         'author' => 'Maiyannah Bishop',
         'homepage' => 'https://www.postactiv.com',
         'rawdescription' => _m('Provides machine translation on demand for postActiv notices using an Apertium-APy server.'));
      return true;
   }
}
?>