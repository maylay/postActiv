<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   OpenNIC Plugin - add a few convinence features for OpenNIC compatibility
 * such as linkifying OpenNIC TLDs                                           *
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


class OpenNICPlugin extends Plugin {
   function onPluginVersion(array &$versions)
   {
      $versions[] = array('name' => 'OpenNIC',
         'version' => '0.1',
         'author' => 'Maiyannah Bishop',
         'homepage' => 'https://www.postactiv.com',
         'rawdescription' => _m('Convinence features for compatibility with OpenNIC domains, such as linkifying OpenNIC TLDs.'));
      return true;
   }
}
?>