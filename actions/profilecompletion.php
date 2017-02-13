<?php
/* ============================================================================
 * Title: ProfileCompletion
 * Profile completion action
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
 * Profile completion action
 *
 * PHP version:
 * Tested with PHP 5.6, PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Shashi Gowda <connect2shashi@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl
 * o Jean Baptiste Favre <statusnet@jbfavre.org>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Marcel van der Boom <marcel@hsdev.com>
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

require_once INSTALLDIR . '/lib/peopletageditform.php';

/**
 * Subscription action
 *
 * Subscribing to a profile. Does not work for OMB 0.1 remote subscriptions,
 * but may work for other remote subscription protocols, like OStatus.
 *
 * Takes parameters:
 *
 *    - subscribeto: a profile ID
 *    - token: session token to prevent CSRF attacks
 *    - ajax: boolean; whether to return Ajax or full-browser results
 *
 * Only works if the current user is logged in.
 */

class ProfilecompletionAction extends Action
{
    var $user;
    var $peopletag;
    var $field;
    var $msg;

    /**
     * Check pre-requisites and instantiate attributes
     *
     * @param Array $args array of arguments (URL, GET, POST)
     *
     * @return boolean success flag
     */
    function prepare(array $args = array())
    {
        parent::prepare($args);

        // CSRF protection

        $token = $this->trimmed('token');

        if (!$token || $token != common_session_token()) {
            // TRANS: Client error displayed when the session token does not match or is not given.
            $this->clientError(_('There was a problem with your session token.'.
                                 ' Try again, please.'));
        }

        // Only for logged-in users

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Error message displayed when trying to perform an action that requires a logged in user.
            $this->clientError(_('Not logged in.'));
        }

        $id = $this->arg('peopletag_id');
        $this->peopletag = Profile_list::getKV('id', $id);

        if (empty($this->peopletag)) {
            // TRANS: Client error displayed trying to reference a non-existing list.
            $this->clientError(_('No such list.'));
        }

        $field = $this->arg('field');
        if (!in_array($field, array('fulltext', 'nickname', 'fullname', 'description', 'location', 'uri'))) {
            // TRANS: Client error displayed when trying to add an unindentified field to profile.
            // TRANS: %s is a field name.
            $this->clientError(sprintf(_('Unidentified field %s.'), htmlspecialchars($field)), 404);
        }
        $this->field = $field;

        return true;
    }

    /**
     * Handle request
     *
     * Does the subscription and returns results.
     *
     * @return void
     */

    function handle()
    {
        $this->msg = null;

        $this->startHTML('text/xml;charset=utf-8');
        $this->elementStart('head');
        // TRANS: Page title.
        $this->element('title', null, _m('TITLE','Search results'));
        $this->elementEnd('head');
        $this->elementStart('body');
        $profiles = $this->getResults();

        if ($this->msg !== null) {
            $this->element('p', 'error', $this->msg);
        } else {
            if (count($profiles) > 0) {
                $this->elementStart('ul', array('id' => 'profile_search_results', 'class' => 'profile-lister'));
                foreach ($profiles as $profile) {
                    $this->showProfileItem($profile);
                }
                $this->elementEnd('ul');
            } else {
                // TRANS: Output when there are no results for a search.
                $this->element('p', 'error', _('No results.'));
            }
        }
        $this->elementEnd('body');
        $this->endHTML();
    }

    function getResults()
    {
        $profiles = array();
        $q = $this->arg('q');
        $q = strtolower($q);
        if (strlen($q) < 3) {
            // TRANS: Error message in case a search is shorter than three characters.
            $this->msg = _('The search string must be at least 3 characters long.');
        }
        $page = $this->arg('page');
        $page = (int) (empty($page) ? 1 : $page);

        $profile = new Profile();
        $search_engine = $profile->getSearchEngine('profile');

        if (Event::handle('StartProfileCompletionSearch', array($this, &$profile, $search_engine))) {
            $search_engine->set_sort_mode('chron');
            $search_engine->limit((($page-1)*PROFILES_PER_PAGE), PROFILES_PER_PAGE + 1);

            if (false === $search_engine->query($q)) {
                $cnt = 0;
            }
            else {
                $cnt = $profile->find();
            }
            Event::handle('EndProfileCompletionSearch', array($this, &$profile, $search_engine));
        }

        while ($profile->fetch()) {
            $profiles[] = clone($profile);
        }
        return $this->filter($profiles);
    }

    function filter($profiles)
    {
        $current = $this->user->getProfile();
        $filtered_profiles = array();
        foreach ($profiles as $profile) {
            if ($current->canTag($profile)) {
                $filtered_profiles[] = $profile;
            }
        }
        return $filtered_profiles;
    }

    function showProfileItem($profile)
    {
        $this->elementStart('li', 'entity_removable_profile');
        $item = new TaggedProfileItem($this, $profile);
        $item->show();
        $this->elementStart('span', 'entity_actions');

        if ($profile->isTagged($this->peopletag)) {
            $untag = new UntagButton($this, $profile, $this->peopletag);
            $untag->show();
        } else {
            $tag = new TagButton($this, $profile, $this->peopletag);
            $tag->show();
        }

        $this->elementEnd('span');
        $this->elementEnd('li');
    }
}
?>