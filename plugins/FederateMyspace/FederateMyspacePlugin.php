<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   FederateMyspace - a plugin to implement Myspace federation for postActiv
 * and GNU social                                                            *
   Copyright (C) 2016 Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *                                                                           *
   @category     Federation
 * @package      postActiv                                                   *
   @author       Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright    2016 Maiyannah Bishop                                       *
   @license      http://www.fsf.org/licensing/licenses/gpl-3.0.html GPL 3.0
 * @link         http://postactiv.com/                                       *
   @dependancies None
 *  ------------------------------------------------------------------------ */

if (!defined('POSTACTIV')) { exit(1); }


class FederateMyspacePlugin extends Plugin {
   function onPluginVersion(array &$versions)
   {
      $versions[] = array('name' => 'Federate Myspace',
         'version' => '0.1',
         'author' => 'Maiyannah Bishop',
         'homepage' => 'https://www.postactiv.com',
         'rawdescription' => _m('A prototype plugin to add postActiv federation with Myspace.'));
      return true;
   }
}
?>