#!/usr/bin/env php
<?php
/*
 * GNU Social - a federating social network
 * Copyright (C) 2017, Free Software Foundation, Inc.
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

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'd::y';
$longoptions = array('date=','yes');

$helptext = <<<END_OF_HELP
delete_many_notices.php [options]
deletes notices (but not related File objects) older than date from the database

  -d --date     Delete notices older than date - format YYYY-MM-DD
  -y --yes      Actually delete notices else just fetch

END_OF_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

try {
    if (have_option('d', 'date')) {
        $year = get_option_value('d', 'date');

        print "DATE = {$year}\n";
        //Read file
        $notices = new Notice();
        $whereClause = 'is_local = \'0\' and created < \'' . $year . '\'';
        print "Where clause : {$whereClause} \n";
        $notices->whereAdd($whereClause);
        $myresult = $notices->find();

        print "Number of notices : {$myresult} \n";

        if (have_option('y', 'yes')) {

            $counter = 0;
            $totalCount = 0;
            while($notices->fetch()) {
                $counter++;
                $totalCount++;
                //print "The number is: {$notices->id} created : {$notices->created} \n";
                if($counter>999){
                    print "Deleted {$totalCount} notices.\n";
                    $counter = 0;
                }

            $notices->delete();
        }
    }


    } else {
        print $helptext;
        throw new ClientException('You must provide a date - YYYY-MM-DD e.g. 2015-12-01');
    }

} catch (Exception $e) {
    print "ERROR: {$e->getMessage()}\n";
    exit(1);
}

print "DONE.\n";
