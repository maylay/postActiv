<?php
/*
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
 * Script to test if a user activity would be deemed as spam
 *
 * @category  Spam
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      http://www.postactiv.com/
  
 */

define('INSTALLDIR', realpath(dirname(__FILE__) . '/../../..'));

$shortoptions = 'i:n:a';
$longoptions = array('id=', 'nickname=', 'all');

$helptext = <<<END_OF_TESTUSER_HELP
testuser.php [options]
Test user activities against the spam filter

  -i --id       ID of user to export
  -n --nickname nickname of the user to export
  -a --all      All users
END_OF_TESTUSER_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

function testAllUsers($filter) {
    $found = false;
    $offset = 0;
    $limit  = 1000;

    do {

        $user = new User();
        $user->orderBy('created');
        $user->limit($offset, $limit);

        $found = $user->find();

        if ($found) {
            while ($user->fetch()) {
                try {
                    testUser($filter, $user);
                } catch (Exception $e) {
                    printfnq("ERROR testing user %s\n: %s", $user->nickname, $e->getMessage());
                }
            }
            $offset += $found;
        }

    } while ($found > 0);
}

function testUser($filter, $user) {

    printfnq("Testing user %s\n", $user->nickname);

    $profile = Profile::getKV('id', $user->id);

    $str = new ProfileNoticeStream($profile, $profile);

    $offset = 0;
    $limit  = 100;

    do {
        $notice = $str->getNotices($offset, $limit);
        while ($notice->fetch()) {
            try {
                printfv("Testing notice %d...", $notice->id);
                $result = $filter->test($notice);
                Spam_score::save($notice, $result);
                printfv("%s\n", ($result->isSpam) ? "SPAM" : "HAM");
            } catch (Exception $e) {
                printfnq("ERROR testing notice %d: %s\n", $notice->id, $e->getMessage());
            }
        }
        $offset += $notice->N;
    } while ($notice->N > 0);
}

try {
    $filter = null;
    Event::handle('GetSpamFilter', array(&$filter));
    if (empty($filter)) {
        throw new Exception(_("No spam filter."));
    }
    if (have_option('a', 'all')) {
        testAllUsers($filter);
    } else {
        $user = getUser();
        testUser($filter, $user);
    }
} catch (Exception $e) {
    print $e->getMessage()."\n";
    exit(1);
}
