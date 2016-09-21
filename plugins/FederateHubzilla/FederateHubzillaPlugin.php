<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    FederateHubzilla - a plugin to implement better Hubzilla federation for
 *  postActiv and GNU social                                                 *
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


class FederateHubzillaPlugin extends Plugin {
   function onPluginVersion(array &$versions)
   {
      $versions[] = array('name' => 'Federate Hubzilla',
         'version' => '0.1',
         'author' => 'Maiyannah Bishop',
         'homepage' => 'https://www.postactiv.com',
         'rawdescription' => _m('A prototype plugin to better extend postActiv federation with Hubzilla.'));
      return true;
   }
}
?>