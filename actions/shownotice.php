<?php
// !TODO: I WRITE HTML, REFACTOR FOR SMARTY

/* ============================================================================
 * Title: SingleNotice
 * Show a single notice
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
 * Show a single notice
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Sarven Capadisli
 * o Robin Millette <robin@millette.info>
 * o Craig Andrews <candrews@integralblue.com>
 * o Marcel van der Boom <marcel@hsdev.com>a
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Brion Vibber <brion@pobox.com>
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Zach Copley
 * o Samantha Doherty
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

require_once INSTALLDIR.'/lib/noticelist.php';

/**
 * Show a single notice
 */
class ShownoticeAction extends ManagedAction
{
    /**
     * Notice object to show
     */
    var $notice = null;

    /**
     * Profile of the notice object
     */
    var $profile = null;

    /**
     * Avatar of the profile of the notice object
     */
    var $avatar = null;

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
        if ($this->boolean('ajax')) {
            postActiv::setApi(true);
        }

        $this->notice = $this->getNotice();
        $this->target = $this->notice;

        if (!$this->notice->inScope($this->scoped)) {
            // TRANS: Client exception thrown when trying a view a notice the user has no access to.
            throw new ClientException(_('Access restricted.'), 403);
        }

        $this->profile = $this->notice->getProfile();

        if (!$this->profile instanceof Profile) {
            // TRANS: Server error displayed trying to show a notice without a connected profile.
            $this->serverError(_('Notice has no profile.'), 500);
        }

        try {
            $this->user = $this->profile->getUser();
        } catch (NoSuchUserException $e) {
            // FIXME: deprecate $this->user stuff in extended classes
            $this->user = null;
        }

        try {
            $this->avatar = $this->profile->getAvatar(AVATAR_PROFILE_SIZE);
        } catch (Exception $e) {
            $this->avatar = null;
        }

        return true;
    }

    /**
     * Fetch the notice to show. This may be overridden by child classes to
     * customize what we fetch without duplicating all of the prepare() method.
     *
     * @return Notice
     */
    protected function getNotice()
    {
        $id = $this->arg('notice');

        $notice = null;
        try {
            $notice = Notice::getByID($id);
            // Alright, got it!
            return $notice;
        } catch (NoResultException $e) {
            // Hm, not found.
            $deleted = null;
            Event::handle('IsNoticeDeleted', array($id, &$deleted));
            if ($deleted === true) {
                // TRANS: Client error displayed trying to show a deleted notice.
                throw new ClientException(_('Notice deleted.'), 410);
            }
        }
        // TRANS: Client error displayed trying to show a non-existing notice.
        throw new ClientException(_('No such notice.'), 404);
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
     * Last-modified date for page
     *
     * When was the content of this page last modified? Based on notice,
     * profile, avatar.
     *
     * @return int last-modified date as unix timestamp
     */
    function lastModified()
    {
        return max(strtotime($this->notice->modified),
                   strtotime($this->profile->modified),
                   ($this->avatar) ? strtotime($this->avatar->modified) : 0);
    }

    /**
     * An entity tag for this page
     *
     * Shows the ETag for the page, based on the notice ID and timestamps
     * for the notice, profile, and avatar. It's weak, since we change
     * the date text "one hour ago", etc.
     *
     * @return string etag
     */
    function etag()
    {
        $avtime = ($this->avatar) ?
          strtotime($this->avatar->modified) : 0;

        return 'W/"' . implode(':', array($this->arg('action'),
                                          common_user_cache_hash(),
                                          common_language(),
                                          $this->notice->id,
                                          strtotime($this->notice->created),
                                          strtotime($this->profile->modified),
                                          $avtime)) . '"';
    }

    /**
     * Title of the page
     *
     * @return string title of the page
     */
    function title()
    {
        return $this->notice->getTitle();
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
        $this->elementStart('ol', array('class' => 'notices xoxo'));
        $nli = new NoticeListItem($this->notice, $this);
        $nli->show();
        $this->elementEnd('ol');
    }

    /**
     * Don't show page notice
     *
     * @return void
     */
    function showPageNoticeBlock()
    {
    }

    function getFeeds()
    {
        return array(new Feed(Feed::JSON,
                              common_local_url('ApiStatusesShow',
                                               array(
                                                    'id' => $this->target->getID(),
                                                    'format' => 'json')),
                              // TRANS: Title for link to single notice representation.
                              // TRANS: %s is a user nickname.
                              sprintf(_('Single notice (JSON)'))),
                     new Feed(Feed::ATOM,
                              common_local_url('ApiStatusesShow',
                                               array(
                                                    'id' => $this->target->getID(),
                                                    'format' => 'atom')),
                              // TRANS: Title for link to notice feed.
                              // TRANS: %s is a user nickname.
                              sprintf(_('Single notice (Atom)'))));
    }

    /**
     * Extra <head> content
     *
     * Facebook OpenGraph metadata.
     *
     * @return void
     */
    function extraHead()
    {
        // Extras to aid in sharing notices to Facebook
        $avatarUrl = $this->profile->avatarUrl(AVATAR_PROFILE_SIZE);
        $this->element('meta', array('property' => 'og:image',
                                     'content' => $avatarUrl));
        $this->element('meta', array('property' => 'og:description',
                                     'content' => $this->notice->content));
    }
}

// END OF FILE
// ============================================================================
?>