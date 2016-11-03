<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 *
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
 * PHP version 5
 *
 * Show notice attachments
 *
 * @category  Notices
 * @package   postActiv
 * @author    Robin Millette <robin@millette.info>
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Zach Copley <zach@copley.name>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://www.postactiv.com
 */

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