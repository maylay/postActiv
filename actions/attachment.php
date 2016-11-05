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
 * @author    Craig Andrews <candrews@integralblue.com>
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Samantha Doherty <sammy@status.net>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Jean Baptiste Favre <statusnet@jbfavre.org>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2009-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Show notice attachments
 */
class AttachmentAction extends ManagedAction
{
    /**
     * Attachment object to show
     */

    var $attachment = null;

    /**
     * Load attributes based on database arguments
     *
     * Loads all the DB stuff
     *
     * @param array $args $_REQUEST array
     *
     * @return success flag
     */

    protected function prepare(array $args=array())
    {
        parent::prepare($args);

        if ($id = $this->trimmed('attachment')) {
            $this->attachment = File::getKV($id);
        }

        if (!$this->attachment instanceof File) {
            // TRANS: Client error displayed trying to get a non-existing attachment.
            $this->clientError(_('No such attachment.'), 404);
        }
        return true;
    }

    /**
     * Is this action read-only?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    /**
     * Title of the page
     *
     * @return string title of the page
     */
    function title()
    {
        $a = new Attachment($this->attachment);
        return $a->title();
    }

    public function showPage()
    {
        if (empty($this->attachment->filename)) {
            // if it's not a local file, gtfo
            common_redirect($this->attachment->getUrl(), 303);
        }

        parent::showPage();
    }

    /**
     * Fill the content area of the page
     *
     * Shows a single notice list item.
     *
     * @return void
     */
    function showContent()
    {
        $ali = new Attachment($this->attachment, $this);
        $cnt = $ali->show();
    }

    /**
     * Don't show page notice
     *
     * @return void
     */
    function showPageNoticeBlock()
    {
    }

    /**
     * Show aside: this attachments appears in what notices
     *
     * @return void
     */
    function showSections() {
        $ns = new AttachmentNoticeSection($this);
        $ns->show();
    }
}
?>