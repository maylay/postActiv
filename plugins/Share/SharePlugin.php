<?php
/*
 * GNU Social - a federating social network
 * Copyright (C) 2014, Free Software Foundation, Inc.
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
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * @package     Activity
 * @maintainer  Mikael Nordfeldth <mmn@hethane.se>
 */
class SharePlugin extends ActivityVerbHandlerPlugin
{
    public function tag()
    {
        return 'share';
    }

    public function types()
    {
        return array();
    }

    public function verbs()
    {
        return array(ActivityVerb::SHARE);
    }

    public function onRouterInitialized(URLMapper $m)
    {
        // Web UI actions
        $m->connect('main/repeat', array('action' => 'repeat'));

        // Share for Twitter API ("Retweet")
        $m->connect('api/statuses/retweeted_by_me.:format',
                    array('action' => 'ApiTimelineRetweetedByMe',
                          'format' => '(xml|json|atom|as)'));

        $m->connect('api/statuses/retweeted_to_me.:format',
                    array('action' => 'ApiTimelineRetweetedToMe',
                          'format' => '(xml|json|atom|as)'));

        $m->connect('api/statuses/retweets_of_me.:format',
                    array('action' => 'ApiTimelineRetweetsOfMe',
                          'format' => '(xml|json|atom|as)'));

        $m->connect('api/statuses/retweet/:id.:format',
                    array('action' => 'ApiStatusesRetweet',
                          'id' => '[0-9]+',
                          'format' => '(xml|json)'));

        $m->connect('api/statuses/retweets/:id.:format',
                    array('action' => 'ApiStatusesRetweets',
                          'id' => '[0-9]+',
                          'format' => '(xml|json)'));
    }

    // FIXME: Set this to abstract public in lib/activityhandlerplugin.php when all plugins have migrated!
    protected function saveObjectFromActivity(Activity $act, Notice $stored, array $options=array())
    {
        assert($this->isMyActivity($act));

        // We don't have to save a repeat in a separate table, we can
        // find repeats by just looking at the notice.repeat_of field.

        return true;
    }

    // FIXME: Put this in lib/activityhandlerplugin.php when we're ready
    //          with the other microapps/activityhandlers as well.
    //          Also it should be StartNoticeAsActivity (with a prepped Activity, including ->context etc.)
    public function onEndNoticeAsActivity(Notice $stored, Activity $act, Profile $scoped=null)
    {
        if (!$this->isMyNotice($stored)) {
            return true;
        }

        common_debug('Extending activity '.$stored->id.' with '.get_called_class());
        $this->extendActivity($stored, $act, $scoped);
        return false;
    }

    public function extendActivity(Notice $stored, Activity $act, Profile $scoped=null)
    {
        // TODO: How to handle repeats of deleted notices?
        $target = Notice::getById($stored->repeat_of);
        // TRANS: A repeat activity's title. %1$s is repeater's nickname
        //        and %2$s is the repeated user's nickname.
        $act->title = sprintf(_('%1$s repeated a notice by %2$s'),
                              $stored->getProfile()->getNickname(),
                              $target->getProfile()->getNickname());
        $act->objects[] = $target->asActivity($scoped);
    }

    public function activityObjectFromNotice(Notice $notice)
    {
        // Repeat is a little bit special. As it's an activity, our
        // ActivityObject is instead turned into an Activity
        $object          = new Activity();
        $object->verb    = ActivityVerb::SHARE;
        $object->type    = $notice->object_type;
        $object->title   = sprintf(_('%1$s repeated a notice by %2$s'),
                              $object->getProfile()->getNickname(),
                              $target->getProfile()->getNickname());
        $object->content = $notice->rendered;
        return $object;
    }

    public function deleteRelated(Notice $notice)
    {
        try {
            $fave = Fave::fromStored($notice);
            $fave->delete();
        } catch (NoResultException $e) {
            // Cool, no problem. We wanted to get rid of it anyway.
        }
    }

    // API stuff

    /**
     * Typically just used to fill out Twitter-compatible API status data.
     *
     * FIXME: Make all the calls before this end up with a Notice instead of ArrayWrapper please...
     */
    public function onNoticeSimpleStatusArray($notice, array &$status, Profile $scoped=null, array $args=array())
    {
        if ($scoped instanceof Profile) {
            $status['favorited'] = Fave::existsForProfile($notice, $scoped);
        } else {
            $status['favorited'] = false;
        }
        return true;
    }

    public function onTwitterUserArray(Profile $profile, array &$userdata, Profile $scoped=null, array $args=array())
    {
        $userdata['favourites_count'] = Fave::countByProfile($profile);
    }

    /**
     * Typically just used to fill out StatusNet specific data in API calls in the referenced $info array.
     */
    public function onStatusNetApiNoticeInfo(Notice $notice, array &$info, Profile $scoped=null, array $args=array())
    {
        if ($scoped instanceof Profile) {
            $info['favorite'] = Fave::existsForProfile($notice, $scoped) ? 'true' : 'false';
        }
        return true;
    }

    public function onNoticeDeleteRelated(Notice $notice)
    {
        parent::onNoticeDeleteRelated($notice);

        // The below algorithm is because we want to delete fave
        // activities on any notice which _has_ faves, and not as
        // in the parent function only ones that _are_ faves.

        $fave = new Fave();
        $fave->notice_id = $notice->id;

        if ($fave->find()) {
            while ($fave->fetch()) {
                $fave->delete();
            }
        }

        $fave->free();
    }

    public function onProfileDeleteRelated(Profile $profile, array &$related)
    {
        $fave = new Fave();
        $fave->user_id = $profile->id;
        $fave->delete();    // Will perform a DELETE matching "user_id = {$user->id}"
        $fave->free();

        Fave::blowCacheForProfileId($profile->id);
        return true;
    }

    public function onStartNoticeListPrefill(array &$notices, array $notice_ids, Profile $scoped=null)
    {
        // prefill array of objects, before pluginfication it was Notice::fillFaves($notices)
        Fave::fillFaves($notice_ids);

        // DB caching
        if ($scoped instanceof Profile) {
            Fave::pivotGet('notice_id', $notice_ids, array('user_id' => $scoped->id));
        }
    }

    /**
     * show the "favorite" form in the notice options element
     * FIXME: Don't let a NoticeListItemAdapter slip in here (or extend that from NoticeListItem)
     *
     * @return void
     */
    public function onStartShowNoticeOptionItems($nli)
    {
        if (Event::handle('StartShowFaveForm', array($nli))) {
            $scoped = Profile::current();
            if ($scoped instanceof Profile) {
                if (Fave::existsForProfile($nli->notice, $scoped)) {
                    $disfavor = new DisfavorForm($nli->out, $nli->notice);
                    $disfavor->show();
                } else {
                    $favor = new FavorForm($nli->out, $nli->notice);
                    $favor->show();
                }
            }
            Event::handle('EndShowFaveForm', array($nli));
        }
    }

    public function showNoticeListItem(NoticeListItem $nli)
    {
        // pass
    }
    public function openNoticeListItemElement(NoticeListItem $nli)
    {
        // pass
    }
    public function closeNoticeListItemElement(NoticeListItem $nli)
    {
        // pass
    }

    public function onAppendUserActivityStreamObjects(UserActivityStream $uas, array &$objs)
    {
        $fave = new Fave();
        $fave->user_id = $uas->getUser()->id;

        if (!empty($uas->after)) {
            $fave->whereAdd("modified > '" . common_sql_date($uas->after) . "'");
        }

        if ($fave->find()) {
            while ($fave->fetch()) {
                $objs[] = clone($fave);
            }
        }

        return true;
    }

    public function onStartShowThreadedNoticeTailItems(NoticeListItem $nli, Notice $notice, &$threadActive)
    {
        if ($nli instanceof ThreadedNoticeListSubItem) {
            // The sub-items are replies to a conversation, thus we use different HTML elements etc.
            $item = new ThreadedNoticeListInlineFavesItem($notice, $nli->out);
        } else {
            $item = new ThreadedNoticeListFavesItem($notice, $nli->out);
        }
        $threadActive = $item->show() || $threadActive;
        return true;
    }

    public function onEndFavorNotice(Profile $actor, Notice $target)
    {
        try {
            $notice_author = $target->getProfile();
            // Don't notify ourselves of our own favorite on our own notice,
            // or if it's a remote user (since we don't know their email addresses etc.)
            if ($notice_author->id == $actor->id || !$notice_author->isLocal()) {
                return true;
            }
            $local_user = $notice_author->getUser();
            mail_notify_fave($local_user, $actor, $target);
        } catch (Exception $e) {
            // Mm'kay, probably not a local user. Let's skip this favor notification.
        }
    }

    /**
     * EndInterpretCommand for FavoritePlugin will handle the 'fav' command
     * using the class FavCommand.
     *
     * @param string  $cmd     Command being run
     * @param string  $arg     Rest of the message (including address)
     * @param User    $user    User sending the message
     * @param Command &$result The resulting command object to be run.
     *
     * @return boolean hook value
     */
    public function onStartInterpretCommand($cmd, $arg, $user, &$result)
    {
        if ($result === false && $cmd == 'fav') {
            if (empty($arg)) {
                $result = null;
            } else {
                list($other, $extra) = CommandInterpreter::split_arg($arg);
                if (!empty($extra)) {
                    $result = null;
                } else {
                    $result = new FavCommand($user, $other);
                }
            }
            return false;
        }
        return true;
    }

    public function onHelpCommandMessages(HelpCommand $help, array &$commands)
    {
        // TRANS: Help message for IM/SMS command "fav <nickname>".
        $commands['fav <nickname>'] = _m('COMMANDHELP', "add user's last notice as a 'fave'");
        // TRANS: Help message for IM/SMS command "fav #<notice_id>".
        $commands['fav #<notice_id>'] = _m('COMMANDHELP', "add notice with the given id as a 'fave'");
    }

    /**
     * Are we allowed to perform a certain command over the API?
     */
    public function onCommandSupportedAPI(Command $cmd, &$supported)
    {
        $supported = $supported || $cmd instanceof FavCommand;
    }

    // Form stuff (settings etc.)

    public function onEndEmailFormData(Action $action, Profile $scoped)
    {
        $emailfave = $scoped->getConfigPref('email', 'notify_fave') ? 1 : 0;

        $action->elementStart('li');
        $action->checkbox('email-notify_fave',
                        // TRANS: Checkbox label in e-mail preferences form.
                        _('Send me email when someone adds my notice as a favorite.'),
                        $emailfave);
        $action->elementEnd('li');

        return true;
    }

    public function onStartEmailSaveForm(Action $action, Profile $scoped)
    {
        $emailfave = $action->booleanintstring('email-notify_fave');
        try {
            if ($emailfave == $scoped->getPref('email', 'notify_fave')) {
                // No need to update setting
                return true;
            }
        } catch (NoResultException $e) {
            // Apparently there's no previously stored setting, then continue to save it as it is now.
        }

        $scoped->setPref('email', 'notify_fave', $emailfave);

        return true;
    }

    // Layout stuff

    public function onEndPersonalGroupNav(Menu $menu, Profile $target, Profile $scoped=null)
    {
        $menu->out->menuItem(common_local_url('showfavorites', array('nickname' => $target->getNickname())),
                             // TRANS: Menu item in personal group navigation menu.
                             _m('MENU','Favorites'),
                             // @todo i18n FIXME: Need to make this two messages.
                             // TRANS: Menu item title in personal group navigation menu.
                             // TRANS: %s is a username.
                             sprintf(_('%s\'s favorite notices'), $target->getBestName()),
                             $scoped instanceof Profile && $target->id === $scoped->id && $menu->actionName =='showfavorites',
                            'nav_timeline_favorites');
    }

    public function onEndPublicGroupNav(Menu $menu)
    {
        if (!common_config('singleuser', 'enabled')) {
            // TRANS: Menu item in search group navigation panel.
            $menu->out->menuItem(common_local_url('favorited'), _m('MENU','Popular'),
                                 // TRANS: Menu item title in search group navigation panel.
                                 _('Popular notices'), $menu->actionName == 'favorited', 'nav_timeline_favorited');
        }
    }

    public function onEndShowSections(Action $action)
    {
        if (!$action->isAction(array('all', 'public'))) {
            return true;
        }

        if (!common_config('performance', 'high')) {
            $section = new PopularNoticeSection($action, $action->getScoped());
            $section->show();
        }
    }

    protected function getActionTitle(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        return Fave::existsForProfile($target, $scoped)
                // TRANS: Page/dialog box title when a notice is marked as favorite already
                ? _m('TITLE', 'Unmark notice as favorite')
                // TRANS: Page/dialog box title when a notice is not marked as favorite
                : _m('TITLE', 'Mark notice as favorite');
    }

    protected function doActionPreparation(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        if ($action->isPost()) {
            // The below tests are only for presenting to the user. POSTs which inflict
            // duplicate favorite entries are handled with AlreadyFulfilledException. 
            return false;
        }

        $exists = Fave::existsForProfile($target, $scoped);
        $expected_verb = $exists ? ActivityVerb::UNFAVORITE : ActivityVerb::FAVORITE;

        switch (true) {
        case $exists && ActivityUtils::compareTypes($verb, array(ActivityVerb::FAVORITE, ActivityVerb::LIKE)):
        case !$exists && ActivityUtils::compareTypes($verb, array(ActivityVerb::UNFAVORITE, ActivityVerb::UNLIKE)):
            common_redirect(common_local_url('activityverb',
                                array('id'   => $target->getID(),
                                      'verb' => ActivityUtils::resolveUri($expected_verb, true))));
            break;
        default:
            // No need to redirect as we are on the correct action already.
        }

        return false;
    }

    protected function doActionPost(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        switch (true) {
        case ActivityUtils::compareTypes($verb, array(ActivityVerb::FAVORITE, ActivityVerb::LIKE)):
            Fave::addNew($scoped, $target);
            break;
        case ActivityUtils::compareTypes($verb, array(ActivityVerb::UNFAVORITE, ActivityVerb::UNLIKE)):
            Fave::removeEntry($scoped, $target);
            break;
        default:
            throw new ServerException('ActivityVerb POST not handled by plugin that was supposed to do it.');
        }
        return false;
    }

    protected function getActivityForm(ManagedAction $action, $verb, Notice $target, Profile $scoped)
    {
        return Fave::existsForProfile($target, $scoped)
                ? new DisfavorForm($action, $target)
                : new FavorForm($action, $target);
    }

    public function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'Favorite',
                            'version' => GNUSOCIAL_VERSION,
                            'author' => 'Mikael Nordfeldth',
                            'homepage' => 'http://gnu.io/',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Favorites (likes) using ActivityStreams.'));

        return true;
    }
}

/**
 * Notify a user that one of their notices has been chosen as a 'fave'
 *
 * @param User    $rcpt   The user whose notice was faved
 * @param Profile $sender The user who faved the notice
 * @param Notice  $notice The notice that was faved
 *
 * @return void
 */
function mail_notify_fave(User $rcpt, Profile $sender, Notice $notice)
{
    if (!$rcpt->receivesEmailNotifications() || !$rcpt->getConfigPref('email', 'notify_fave')) {
        return;
    }

    // This test is actually "if the sender is sandboxed"
    if (!$sender->hasRight(Right::EMAILONFAVE)) {
        return;
    }

    if ($rcpt->hasBlocked($sender)) {
        // If the author has blocked us, don't spam them with a notification.
        return;
    }

    // We need the global mail.php for various mail related functions below.
    require_once INSTALLDIR.'/lib/mail.php';

    $bestname = $sender->getBestName();

    common_switch_locale($rcpt->language);

    // TRANS: Subject for favorite notification e-mail.
    // TRANS: %1$s is the adding user's long name, %2$s is the adding user's nickname.
    $subject = sprintf(_('%1$s (@%2$s) added your notice as a favorite'), $bestname, $sender->getNickname());

    // TRANS: Body for favorite notification e-mail.
    // TRANS: %1$s is the adding user's long name, $2$s is the date the notice was created,
    // TRANS: %3$s is a URL to the faved notice, %4$s is the faved notice text,
    // TRANS: %5$s is a URL to all faves of the adding user, %6$s is the StatusNet sitename,
    // TRANS: %7$s is the adding user's nickname.
    $body = sprintf(_("%1\$s (@%7\$s) just added your notice from %2\$s".
                      " as one of their favorites.\n\n" .
                      "The URL of your notice is:\n\n" .
                      "%3\$s\n\n" .
                      "The text of your notice is:\n\n" .
                      "%4\$s\n\n" .
                      "You can see the list of %1\$s's favorites here:\n\n" .
                      "%5\$s"),
                    $bestname,
                    common_exact_date($notice->created),
                    common_local_url('shownotice',
                                     array('notice' => $notice->id)),
                    $notice->content,
                    common_local_url('showfavorites',
                                     array('nickname' => $sender->getNickname())),
                    common_config('site', 'name'),
                    $sender->getNickname()) .
            mail_footer_block();

    $headers = _mail_prepare_headers('fave', $rcpt->getNickname(), $sender->getNickname());

    common_switch_locale();
    mail_to_user($rcpt, $subject, $body, $headers);
}
