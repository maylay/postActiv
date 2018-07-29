<?php
/* ============================================================================
 * Title: Nodeinfo_2_0Action
 * NodeInfo 2.0 statistics endpoint for the API
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
 * NodeInfo 2.0 statistics endpoint.
 * Originally a GNU Social plugin at <https://github.com/chimo/gs-nodeinfo> by
 * Chimo
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Chimo
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * ============================================================================
 */


// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

// ============================================================================
// Class: Nodeinfo_2_0Action
// Class abstraction of the Nodeinfo 2.0 API endpoint.
class Nodeinfo_2_0Action extends ApiAction {
   private $plugins;

   protected function handle() {
      parent::handle();
      $this->plugins = $this->getActivePluginList();
      $this->showNodeInfo();
   }

   function getActivePluginList() {
      $pluginversions = array();
      $plugins = array();
      Event::handle('PluginVersion', array(&$pluginversions));
      foreach($pluginversions as $plugin) {
          $plugins[strtolower($plugin['name'])] = 1;
      }
      return $plugins;
   }

   // -------------------------------------------------------------------------
   // Function: getActiveUsers($days)
   //  Technically, the NodeInfo spec defines 'active' as 'signed in at least
   // once', but GNU social doesn't keep track of when users last logged in,
   // so let's return the number of users that 'posted at least once', I
   // guess. - chimo
   //
   // (The same is true of postActiv - mb)
   function getActiveUsers($days) {
        $notices = new Notice();
        $notices->joinAdd(array('profile_id', 'user:id'));
        $notices->whereAdd('notice.created >= NOW() - INTERVAL ' . $days . ' DAY');
        $activeUsersCount = $notices->count('distinct profile_id');
        return $activeUsersCount;
   }

   // -------------------------------------------------------------------------
   // Function: getRegistrationsStatus
   // Returns a boolean whether the instance allows registrations.
   function getRegistrationsStatus() {
      $areRegistrationsClosed = (common_config('site', 'closed')) ? true : false;
      $isSiteInviteOnly = (common_config('site', 'inviteonly')) ? true : false;
      return !($areRegistrationsClosed || $isSiteInviteOnly);
   }

   // -------------------------------------------------------------------------
   // Function: getUserCount
   // Return the TOTAL number of users (active and inactive)
   function getUserCount() {
      $users = new User();
      $userCount = $users->count();
      return $userCount;
   }

   // -------------------------------------------------------------------------
   // Function: getPostCount
   // Returns the number of posts - root messages of a conversations in this
   // context.
   function getPostCount() {
      $notices = new Notice();
      $notices->is_local = Notice::LOCAL_PUBLIC;
      $notices->whereAdd('reply_to IS NULL');
      $noticeCount = $notices->count();
      return $noticeCount;
   }


   // -------------------------------------------------------------------------
   // Function: getCommentCount
   // Returns the number of comments - replies to a post in this context.
   function getCommentCount() {
      $notices = new Notice();
      $notices->is_local = Notice::LOCAL_PUBLIC;
      $notices->whereAdd('reply_to IS NOT NULL');
      $commentCount = $notices->count();
      return $commentCount;
   }
   

   // -------------------------------------------------------------------------
   // Function: getProtocols
   // Returns the protocols which the instance speaks.
   function getProtocols() {
      $oStatusEnabled  = (array_key_exists('ostatus', $this->plugins) | array_key_exists('federateOstatus', $this->plugins));
      $xmppEnabled     = (array_key_exists('xmpp', $this->plugins) && common_config('xmpp', 'enabled')) ? true : false;
      $diasporaEnabled = array_key_exists('federateDiaspora', $this->plugins);
      $protocols = array();
      if (Event::handle('StartNodeInfoProtocols', array(&$protocols))) {
         // Until the OStatus and XMPP plugins handle this themselves,
         // try to figure out if they're enabled ourselves.
         if ($oStatusEnabled) {
            $protocols[] = 'ostatus';
         }
         if ($diasporaEnabled) {
            $protocols[] = 'diaspora';
         }
         if ($xmppEnabled) {
            $protocols[] = 'xmpp';
         }
      }
      Event::handle('EndNodeInfoProtocols', array(&$protcols));
      return $protocols;
   }

   // -------------------------------------------------------------------------
   // Function: getInboundServices
   // Returns an array of protocols this instance accepts INCOMING.
   //
   // Possible: XMPP, ostatus, twitter, diaspora, atom, rss
   //
   // activpost refers to a future internal protocol for PA, which all public
   // nodes will accept and will contain a superset of different federation
   // network features.
   function getInboundServices() {
      $diaspora_enabled = array_key_exists('federateDiaspora', $this->plugins);
      $ostatus_enabled  = array_key_exists('ostatus', $this->plugins);
      $twitter_enabled  = array_key_exists('twitterbridge', $this->plugins) && $config['twitterimport']['enabled'];
      $xmpp_enabled     = (array_key_exists('xmpp', $this->plugins) && common_config('xmpp', 'enabled')) ? true : false;

      // FIXME: Are those always on?
      $inboundServices = array('atom1.0', 'rss2.0', 'activpost');

      if ($diaspora_enabled) {
         $inboundServices[] = 'diaspora';
      }
      if ($ostatus_enabled) {
         $inboundServices[] = 'ostatus';
      }
      if ($twitter_enabled) {
         $inboundServices[] = 'twitter';
      }
      if ($xmpp_enabled) {
         $inboundServices[] = 'xmpp';
      }
      return $inboundServices;
   }

   // -------------------------------------------------------------------------
   // Function: getOutboundServices
   // Returns a list of the protocols this instance will dispense output in.
   //
   // Possible: XMPP, ostatus, twitter, diaspora, atom, rss
   //
   // activpost refers to a future internal protocol for PA, which all public
   // nodes will accept and will contain a superset of different federation
   // network features.
   function getOutboundServices() {
      $diaspora_enabled = array_key_exists('federateDiaspora', $this->plugins);
      $ostatus_enabled  = array_key_exists('ostatus', $this->plugins);
      $twitter_enabled  = array_key_exists('twitterbridge', $this->plugins) && $config['twitterimport']['enabled'];
      $xmpp_enabled     = (array_key_exists('xmpp', $this->plugins) && common_config('xmpp', 'enabled')) ? true : false;

      // FIXME: Are those always on?
      $inboundServices = array('atom1.0', 'rss2.0', 'activpost');
      
      if ($diaspora_enabled) {
         $outboundServices[] = 'diaspora';
      }
      if ($ostatus_enabled) {
         $outboundServices[] = 'ostatus';
      }
      if ($twitter_enabled) {
         $outboundServices[] = 'twitter';
      }
      if ($xmpp_enabled) {
         $outboundServices[] = 'xmpp';
      }
      return $outboundServices;
   }

   // -------------------------------------------------------------------------
   // Function: showNodeInfo
   // Output an aggregated json message with the nodeinfo statistics for the
   // instance.
   //
   // TODO: Have options which allow us to determine how much of this or how
   // little of it we want to share.
   function showNodeInfo() {
      $openRegistrations = $this->getRegistrationsStatus();
      $userCount = $this->getUserCount();
      $postCount = $this->getPostCount();
      $commentCount = $this->getCommentCount();
      $usersActiveHalfyear = $this->getActiveUsers(180);
      $usersActiveMonth = $this->getActiveUsers(30);
      $protocols = $this->getProtocols();
      $inboundServices = $this->getInboundServices();
      $outboundServices = $this->getOutboundServices();
      $json = json_encode([
         'version' => '2.0',
         'software' => [
            'name' => 'postActiv',
            'version' => GNUSOCIAL_VERSION],
         'protocols' => $protocols,
         // TODO: Have plugins register services
         'services' => [
            'inbound' => $inboundServices,
            'outbound' => $outboundServices],
         'openRegistrations' => $openRegistrations,
         'usage' => [
            'users' => [
               'total' => $userCount,
               'activeHalfyear' => $usersActiveHalfyear,
               'activeMonth' => $usersActiveMonth],
            'localPosts' => $postCount,
            'localComments' => $commentCount],
         'metadata' => new stdClass()]);
      $this->initDocument('json');
      print $json;
      $this->endDocument('json');
   }
}

// END OF FILE
// ============================================================================
?>