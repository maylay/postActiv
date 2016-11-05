<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
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
 * Manifest for the Aeternum template.  Essentially, this file tells postActiv
 * which files to load for which router paths.
 *
 * @category  Templates
 * @package   postActiv
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      https://www.postactiv.com
 */

if (!defined('POSTACTIV')) { exit(1); }

define('AETERNUM_PATH', dirname(__FILE__));

$aeternum = new SmartyTheme;
$aeternum->mapCompileDir(AETERNUM_PATH . "/templates_c/");

// For each stylesheet, we add them to an internal array with addStylesheet,
// which we can then iterate over in general_header to get all the styles we need.
// This allows us to add specialty stylesheets in different pages.
// Only add ones in the manifest which you want to be always present.
$aeternum->addStylesheet(AETERNUM_PATH . "/media/css/styles.css");

// Much like stylesheets, and for the same reason, we add scripts to an array.
$aeternum->addScript(AETERNUM_PATH . "/media/js/scripts.js");

// Basic postActiv battery of templates
// If you don't specify one of these system templates, the system will substitute
// a fallback template.  They're ugly as hell though, so you probably want to just
// copy Aeternum's into your own theme if you don't care to change it.
$aeternum->mapTemplate("user_profile", AETERNUM_PATH . "./templates/profile.tpl");
$aeternum->mapTemplate("single_notice", AETERNUM_PATH . "/templates/notice.tpl");
$aeternum->mapTemplate("user_settings", AETERNUM_PATH, "/templates/user_settings.tpl");
$aeternum->mapTemplate("admin_settings", AETERNUM_PATH . "/templates/admin_settings.tpl");
$aeternum->mapTemplate("login_logout", AETERNUM_PATH . "/templates/login.tpl");
$aeternum->mapTemplate("webconfig", AETERNUM_PATH . "/templates/webconfig.tpl");
$aeternum->mapTemplate("post_notice", AETERNUM_PATH . "/templates/post_notice.tpl");
$aeternum->mapTemplate("search", AETERNUM_PATH . "/templates/search.tpl");
$aeternum->mapTemplate("search_results", AETERNUM_PATH . "/templates/search_results.tpl")l

// Timelines uses a single template page, the type of template is passed to the template
// and it can make specific timeline thematic changes using a conditional block in the
// template, or given that Smarty allows nesting, you can even
$aeternum->mapTemplate("timeline", AETERNUM_PATH . "/templates/timeline.tpl");

// Like the timeline page we use a general header/footer file, and then use that template
// to determine specific page stuff on an as-needed basis.  There's a variety of stuff
// we pass here, you can read the template for information.
$aeternum->mapTemplate("general_header", AETERNUM_PATH . "/templates/header.tpl");
$aeternum->mapTemplate("general_footer", AETERNUM_PATH . "/templates/footer.tpl");

// It's important to note that plugins can add additional templates to the battery,
// so if you have plugins, check to ensure that you have them here too.  Any good plugin
// should have the reference implementation template, which is also a fallback, but
// they tend to look ugly, so you'll probably want to override them.

// Entries follow the previous format just the same, you just have to look up what
// it's registered the template page to.  Incidentally, you can call the template
// file for it anything, as long as you have the map name correct.  It's just clearest
// to use the mapping name as the template file name to keep things obvious.
// This can also be stored theoretically anywheres the user that postActiv's web server
// runs under has access to.  It does need to be on the same web server as the software
// itself, at the present time, however.
//
// $aeternum->mapTemplate("plugin_page_1", AETERNUM_PATH . "/templates/plugin_page_1.tpl");
// $aeternum->mapTemplate("plugin_page_2", AETERNUM_PATH . "/templates/plugin_page_2.tpl");
// etc.


// We can register loose templates, such as those used as children to the parent
// templates above, such as having had a specific template for each timeline which
// we use conditionals to break down.  Right now this does nothing, but when the
// admin area has the ability to modify templates implemented, this will also add
// these child templates as available to edit in the appropriate admin area.

// $aeternum->registerLooseTemplate("short_alias_with_no_spaces", "Proper english description/title", AETERNUM_PATH . "/templates/name_of_template.tpl");
// $aeternum->registerLooseTemplate("friend_timeline", "Friends Timeline", AETERNUM_PATH . "/templates/friends_timeline.tpl");
?>