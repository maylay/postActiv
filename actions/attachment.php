<?php
/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * Show notice attachments
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  Personal
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2008-2009 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/attachmentlist.php';

/**
 * Show notice attachments
 *
 * @category Personal
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class AttachmentAction extends Action
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

    function prepare($args)
    {
        parent::prepare($args);

        if ($id = $this->trimmed('attachment')) {
            $this->attachment = File::getKV($id);
        }

        if (empty($this->attachment)) {
            // TRANS: Client error displayed trying to get a non-existing attachment.
            $this->clientError(_('No such attachment.'), 404);
            return false;
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

    function extraHead()
    {
        $this->element('link',array('rel'=>'alternate',
            'type'=>'application/json+oembed',
            'href'=>common_local_url(
                'oembed',
                array(),
                array('format'=>'json', 'url'=>
                    common_local_url('attachment',
                        array('attachment' => $this->attachment->id)))),
            'title'=>'oEmbed'),null);
        $this->element('link',array('rel'=>'alternate',
            'type'=>'text/xml+oembed',
            'href'=>common_local_url(
                'oembed',
                array(),
                array('format'=>'xml','url'=>
                    common_local_url('attachment',
                        array('attachment' => $this->attachment->id)))),
            'title'=>'oEmbed'),null);
        /* Twitter card support. See https://dev.twitter.com/docs/cards */
        /* @fixme: should we display twitter cards only for attachments posted
         *         by local users ? Seems mandatory to display twitter:creator
         */
        switch ($this->attachment->mimetype) {
            case 'image/pjpeg':
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/png':
            case 'image/gif':
                $this->element('meta', array('name'    => 'twitter:card',
                                             'content' => 'photo'),
                                       null);
                $this->element('meta', array('name'    => 'twitter:url',
                                             'content' => common_local_url('attachment',
                                                              array('attachment' => $this->attachment->id))),
                                       null );
                $this->element('meta', array('name'    => 'twitter:image',
                                             'content' => $this->attachment->url));

                $ns = new AttachmentNoticeSection($this);
                $notices = $ns->getNotices();
                $noticeArray = $notices->fetchAll();

                // Should not have more than 1 notice for this attachment.
                if( count($noticeArray) != 1 ) { break; }
                $post = $noticeArray[0];

                $flink = Foreign_link::getByUserID($post->profile_id, TWITTER_SERVICE);
                if( $flink ) { // Our local user has registered Twitter Gateway
                    $fuser = Foreign_user::getForeignUser($flink->foreign_id, TWITTER_SERVICE);
                    if( $fuser ) { // Got nickname for local user's Twitter account
                        $this->element('meta', array('name'    => 'twitter:creator',
                                                     'content' => '@'.$fuser->nickname));
                    }
                }
                break;
            default: break;
        }
    }

    /**
     * Handle input
     *
     * Only handles get, so just show the page.
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    function handle($args)
    {
        parent::handle($args);

        if (empty($this->attachment->filename)) {

            // if it's not a local file, gtfo

            common_redirect($this->attachment->url, 303);

        } else {
            $this->showPage();
        }
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
        if (!common_config('performance', 'high')) {
            $atcs = new AttachmentTagCloudSection($this);
            $atcs->show();
        }
    }
}
