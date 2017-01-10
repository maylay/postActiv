<?php
/* ============================================================================
 * Title: SystemListItem
 * Superclass for system event items
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
 * Superclass for system event items
 *
 * PHP version:
 * Tested with PHP 5.6
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
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
// Class: SystemListItem
// NoticeListItemAdapter for system activities
class SystemListItem extends NoticeListItemAdapter
{
   // -------------------------------------------------------------------------
   // Function: showNotice
   // Show the activity
   //
   // Returns:
   // o void
   function showNotice() {
      $out = $this->nli->out;
      $out->elementStart('div', 'entry-title');
      $this->showContent();
      $out->elementEnd('div');
   }


   // -------------------------------------------------------------------------
   // Function: showContent
   // Show the activity in HTML format
   function showContent() {
      $notice = $this->nli->notice;
      $out    = $this->nli->out;
      // FIXME: get the actual data on the leave
      $out->elementStart('div', 'system-activity');
      $out->raw($notice->getRendered());
      $out->elementEnd('div');
   }


   // -------------------------------------------------------------------------
   // Function: showNoticeOptions
   function showNoticeOptions() {
      if (Event::handle('StartShowNoticeOptions', array($this))) {
         $user = common_current_user();
         if (!empty($user)) {
            $this->nli->out->elementStart('div', 'notice-options');
            if (Event::handle('StartShowNoticeOptionItems', array($this))) {
               $this->showReplyLink();
               Event::handle('EndShowNoticeOptionItems', array($this));
            }
            $this->nli->out->elementEnd('div');
         }
         Event::handle('EndShowNoticeOptions', array($this));
      }
   }
}

// END OF FILE
// ============================================================================
?>