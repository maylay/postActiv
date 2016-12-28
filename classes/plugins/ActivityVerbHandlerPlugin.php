<?php
/* ============================================================================
 * Title: ActivityVerbHandlerPlugin
 * Extends activity verb handling for plugin interfacing
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016, Maiyannah Bishop
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
 * Tested with PHP 5.6
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


/**
 * @package     Activity
 * @maintainer  Mikael Nordfeldth <mmn@hethane.se>
 */
abstract class ActivityVerbHandlerPlugin extends ActivityHandlerPlugin
{
    public function onActivityVerbTitle(ManagedAction $action, $verb, Notice $target, Profile $scoped, &$title)
    {
        if (!$this->isMyVerb($verb)) {
            return true;
        }

        $title = $this->getActionTitle($action, $verb, $target, $scoped);
        return false;
    }
    abstract protected function getActionTitle(ManagedAction $action, $verb, Notice $target, Profile $scoped);

    public function onActivityVerbShowContent(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        if (!$this->isMyVerb($verb)) {
            return true;
        }

        return $this->showActionContent($action, $verb, $target, $scoped);
    }
    protected function showActionContent(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        if (!postActiv::isAjax()) {
            $nl = new NoticeListItem($target, $action, array('options'=>false, 'attachments'=>false,
                                                             'item_tag'=>'div', 'id_prefix'=>'fave'));
            $nl->show();
        }

        $form = $this->getActivityForm($action, $verb, $target, $scoped);
        $form->show();

        return false;
    }

    public function onActivityVerbDoPreparation(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        if (!$this->isMyVerb($verb)) {
            return true;
        }

        return $this->doActionPreparation($action, $verb, $target, $scoped);
    }
    abstract protected function doActionPreparation(ManagedAction $action, $verb, Notice $target, Profile $scoped);

    public function onActivityVerbDoPost(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        if (!$this->isMyVerb($verb)) {
            return true;
        }

        return $this->doActionPost($action, $verb, $target, $scoped);
    }
    abstract protected function doActionPost(ManagedAction $action, $verb, Notice $target, Profile $scoped);

    abstract protected function getActivityForm(ManagedAction $action, $verb, Notice $target, Profile $scoped);
}

// END OF FILE
// ============================================================================
?>