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
 * Script to train user activities against the spam filter
 *
 * @category  Spam
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2012 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/

 */

define('INSTALLDIR', realpath(dirname(__FILE__) . '/../../..'));

$shortoptions = 'i:n:t:';
$longoptions = array('id=', 'nickname=', 'category=');

$helptext = <<<END_OF_TRAINUSER_HELP
trainuser.php [options]
Train user activities against the spam filter

  -i --id       ID of user to export
  -n --nickname nickname of the user to export
  -t --category Category; one of "spam" or "ham"

END_OF_TRAINUSER_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

function trainUser($filter, $user, $category) {

    printfnq("Training user %s\n", $user->nickname);

    $profile = Profile::getKV('id', $user->id);

    $str = new ProfileNoticeStream($profile, $profile);

    $offset = 0;
    $limit  = 100;

    do {
        $notice = $str->getNotices($offset, $limit);
        while ($notice->fetch()) {
            try {
                printfv("Training notice %d...", $notice->id);
                $filter->trainOnError($notice, $category);
                $result = $filter->test($notice);
                $score = Spam_score::save($notice, $result);
                printfv("%s\n", ($result->isSpam) ? "SPAM" : "HAM");
            } catch (Exception $e) {
                printfnq("ERROR training notice %d\n: %s", $notice->id, $e->getMessage());
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
    $user = getUser();
    $category = get_option_value('t', 'category');
    if ($category !== SpamFilter::HAM &&
        $category !== SpamFilter::SPAM) {
        throw new Exception(_("No such category."));
    }
    trainUser($filter, $user, $category);
} catch (Exception $e) {
    print $e->getMessage()."\n";
    exit(1);
}
