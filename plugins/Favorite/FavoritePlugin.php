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
class FavoritePlugin extends ActivityHandlerPlugin
{
    public function tag()
    {
        return 'favorite';
    }

    public function types()
    {
        return array();
    }

    public function verbs()
    {
        return array(ActivityVerb::FAVORITE);
    }
    
    public function onCheckSchema()
    {
        $schema = Schema::get();
        $schema->ensureTable('fave', Fave::schemaDef());
        return true;
    }
    
    public function onEndUpgrade()
    {
        printfnq("Ensuring all faves have a URI...");
    
        $fave = new Fave();
        $fave->whereAdd('uri IS NULL');
    
        if ($fave->find()) {
            while ($fave->fetch()) {
                try {
                    $fave->decache();
                    $fave->query(sprintf('UPDATE fave '.
                                         'SET uri = "%s", '.
                                         '    modified = "%s" '.
                                         'WHERE user_id = %d '.
                                         'AND notice_id = %d',
                                         Fave::newURI($fave->user_id, $fave->notice_id, $fave->modified),
                                         common_sql_date(strtotime($fave->modified)),
                                         $fave->user_id,
                                         $fave->notice_id));
                } catch (Exception $e) {
                    common_log(LOG_ERR, "Error updating fave URI: " . $e->getMessage());
                }
            }
        }
    
        printfnq("DONE.\n");
    }

    public function onRouterInitialized(URLMapper $m)
    {
        // Web UI actions
        $m->connect('main/favor', array('action' => 'favor'));
        $m->connect('main/disfavor', array('action' => 'disfavor'));

        if (common_config('singleuser', 'enabled')) {
            $nickname = User::singleUserNickname();

            $m->connect('favorites',
                        array('action' => 'showfavorites',
                              'nickname' => $nickname));
            $m->connect('favoritesrss',
                        array('action' => 'favoritesrss',
                              'nickname' => $nickname));
        } else {
            $m->connect('favoritedrss', array('action' => 'favoritedrss'));
            $m->connect('favorited/', array('action' => 'favorited'));
            $m->connect('favorited', array('action' => 'favorited'));

            $m->connect(':nickname/favorites',
                        array('action' => 'showfavorites'),
                        array('nickname' => Nickname::DISPLAY_FMT));
            $m->connect(':nickname/favorites/rss',
                        array('action' => 'favoritesrss'),
                        array('nickname' => Nickname::DISPLAY_FMT));
        }

        // Favorites for API
        $m->connect('api/favorites/create.:format',
                    array('action' => 'ApiFavoriteCreate',
                          'format' => '(xml|json)'));
        $m->connect('api/favorites/destroy.:format',
                    array('action' => 'ApiFavoriteDestroy',
                          'format' => '(xml|json)'));
        $m->connect('api/favorites/list.:format',
                    array('action' => 'ApiTimelineFavorites',
                          'format' => '(xml|json|rss|atom|as)'));
        $m->connect('api/favorites/:id.:format',
                    array('action' => 'ApiTimelineFavorites',
                          'id' => Nickname::INPUT_FMT,
                          'format' => '(xml|json|rss|atom|as)'));
        $m->connect('api/favorites.:format',
                    array('action' => 'ApiTimelineFavorites',
                          'format' => '(xml|json|rss|atom|as)'));
        $m->connect('api/favorites/create/:id.:format',
                    array('action' => 'ApiFavoriteCreate',
                          'id' => '[0-9]+',
                          'format' => '(xml|json)'));
        $m->connect('api/favorites/destroy/:id.:format',
                    array('action' => 'ApiFavoriteDestroy',
                          'id' => '[0-9]+',
                          'format' => '(xml|json)'));

        // AtomPub API
        $m->connect('api/statusnet/app/favorites/:profile/:notice.atom',
                    array('action' => 'AtomPubShowFavorite'),
                    array('profile' => '[0-9]+',
                          'notice' => '[0-9]+'));

        $m->connect('api/statusnet/app/favorites/:profile.atom',
                    array('action' => 'AtomPubFavoriteFeed'),
                    array('profile' => '[0-9]+'));

        // Required for qvitter API
        $m->connect('api/statuses/favs/:id.:format',
                    array('action' => 'ApiStatusesFavs',
                          'id' => '[0-9]+',
                          'format' => '(xml|json)'));
    }

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
        $fave = new Fave();
        $fave->notice_id = $notice->id;

        if ($fave->find()) {
            while ($fave->fetch()) {
                Memcached_DataObject::blow('fave:ids_by_user_own:%d', $fave->user_id);
                Memcached_DataObject::blow('fave:ids_by_user_own:%d;last', $fave->user_id);
                Memcached_DataObject::blow('fave:ids_by_user:%d', $fave->user_id);
                Memcached_DataObject::blow('fave:ids_by_user:%d;last', $fave->user_id);
                $fave->delete();
            }
        }

        $fave->free();
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

    public function onAppendUserActivityStreamObjects(UserActivityStream $uas, array &$objs)
    {
        $faves = array();
        $fave = new Fave();
        $fave->user_id = $uas->user->id;

        if (!empty($uas->after)) {
            $fave->whereAdd("modified > '" . common_sql_date($uas->after) . "'");
        }

        if ($fave->find()) {
            while ($fave->fetch()) {
                $faves[] = clone($fave);
            }
        }

        return $faves;
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
                list($other, $extra) = $this->split_arg($arg);
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
    public function onCommandSupportedAPI(Command $cmd, array &$supported)
    {
        $supported = $supported || $cmd instanceof FavCommand;
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
