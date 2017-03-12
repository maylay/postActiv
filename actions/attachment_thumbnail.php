<?php
/* ============================================================================
 * Title: Attachment_thumbnail
 * Show notice attachment thumbnails
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
 * Show notice attachments
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Robin Millette <robin@millette.info>
 * o Sarven Capadisli
 * o Zach Copley
 * o Evan Prodromou
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
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
 * Show notice attachments
 */
class Attachment_thumbnailAction extends AttachmentAction
{
    protected $thumb_w = null;  // max width
    protected $thumb_h = null;  // max height
    protected $thumb_c = null;  // crop?

    protected function doPreparation()
    {
        parent::doPreparation();

        $this->thumb_w = $this->int('w');
        $this->thumb_h = $this->int('h');
        $this->thumb_c = $this->boolean('c');
    }

    public function showPage()
    {
        // Returns a File_thumbnail object or throws exception if not available
        try {
            $thumbnail = $this->attachment->getThumbnail($this->thumb_w, $this->thumb_h, $this->thumb_c);
        } catch (UseFileAsThumbnailException $e) {
            common_redirect($e->file->getUrl(), 302);
        }

        common_redirect(File_thumbnail::url($thumbnail->getFilename()), 302);
    }
}
?>