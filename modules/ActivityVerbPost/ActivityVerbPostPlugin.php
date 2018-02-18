<?php
/* ============================================================================
 * Title: ActivityVerbPostPlugin
 * Extends activity verb handling for plugin interfacing
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
 * Extends activity verb handling for plugin interfacing
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
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


// ----------------------------------------------------------------------------
// Class: ActivityVerbPostPlugin
// Extends activity verb handling for plugin interfacing
// 
// TODO:
// o Implement a "fallback" feature which can handle anything _as_ an 
//   activityobject "note"
class ActivityVerbPostPlugin extends ActivityVerbHandlerPlugin
{

   // -------------------------------------------------------------------------
   // Function: tag
   // Returns default tag ("post") - override to set a new default
   public function tag() {
      return 'post';
   }


   // -------------------------------------------------------------------------   
   // Function: types
   // Returns an array of acceptable ActivityObject types - override to set new
   // limits or add a new AO type.
   public function types() {
      return array(ActivityObject::ARTICLE,
                   ActivityObject::BLOGENTRY,
                   ActivityObject::NOTE,
                   ActivityObject::STATUS,
                   ActivityObject::COMMENT,
                   // null,    // if we want to follow the original Ostatus_profile::processActivity code
                   );
   }


   // -------------------------------------------------------------------------
   // Function: verbs
   // Returns an array of acceptable ActivityObject verbs - override to set new
   // limits or add a new AO verb.
    public function verbs()
    {
        return array(ActivityVerb::POST);
    }

    // FIXME: Set this to abstract public in lib/activityhandlerplugin.php when all plugins have migrated!
    protected function saveObjectFromActivity(Activity $act, Notice $stored, array $options=array())
    {
        assert($this->isMyActivity($act));

        $stored->object_type = ActivityUtils::resolveUri($act->objects[0]->type);
        if (common_valid_http_url($act->objects[0]->link)) {
            $stored->url = $act->objects[0]->link;
        }

        // We don't have to do just about anything for a new, remote notice since the fields
        // are handled in the main Notice::saveActivity function. Such as content, attachments,
        // parent/conversation etc.

        // By returning true here instead of something that evaluates
        // to false, we show that we have processed everything properly.
        return true;
    }

    public function activityObjectFromNotice(Notice $notice)
    {
        $object = new ActivityObject();

        $object->type    = $notice->object_type ?: ActivityObject::NOTE;
        $object->id      = $notice->getUri();
        $object->title   = sprintf('New %1$s by %2$s', ActivityObject::canonicalType($object->type), $notice->getProfile()->getNickname());
        $object->content = $notice->getRendered();
        $object->link    = $notice->getUrl();

        $object->extra[] = array('status_net', array('notice_id' => $notice->getID()));

        return $object;
    }

    public function deleteRelated(Notice $notice)
    {
        // No action needed as the table for data storage _is_ the notice table.
        return true;
    }


    /**
     * Command stuff
     */

    // FIXME: Move stuff from lib/command.php to here just as with Share etc.


    /**
     * Layout stuff
     */

    protected function showNoticeContent(Notice $stored, HTMLOutputter $out, Profile $scoped=null)
    {
        $out->raw($stored->getRendered());
    }

    protected function getActionTitle(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        // return page title
    }

    protected function doActionPreparation(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        // prepare Action?
    }

    protected function doActionPost(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        // handle POST
    }

    protected function getActivityForm(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        return new NoticeForm($action, array());
    }

    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'Post verb',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'https://gnu.io/',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Post handling with ActivityStreams.'));

        return true;
    }
}
