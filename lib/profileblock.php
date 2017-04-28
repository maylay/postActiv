<?php
/**
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Superclass for profile blocks
 *
 * PHP version 5
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
 * @category  Widget
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * Class comment
 */

abstract class ProfileBlock extends Widget
{
    protected $avatarSize = AVATAR_PROFILE_SIZE;

    abstract function name();
    abstract function url();
    abstract function location();
    abstract function homepage();
    abstract function description();
    abstract function xmpp();
    abstract function gpgpubkey();
    abstract function toxid();
    abstract function matrix();

    function show()
    {
        $this->showActions();
        $this->showAvatar($this->profile);
        $this->showName();
        $this->showLocation();
        $this->showHomepage();
        $this->showOtherProfiles();
        $this->showProfileIcons();
        $this->showDescription();
        $this->showTags();
    }

    function showName()
    {
        $name = $this->name();

        if (!empty($name)) {
            $this->out->elementStart('p', 'profile_block_name');
            $url = $this->url();
            if (!empty($url)) {
                $this->out->element('a', array('href' => $url),
                                    $name);
            } else {
                $this->out->text($name);
            }
            $this->out->elementEnd('p');
        }
    }

    function showDescription()
    {
        $description = $this->description();

        if (!empty($description)) {
            $this->out->element(
                'p',
                'profile_block_description',
                $description
            );
        }
    }

    function showLocation()
    {
        $location = $this->location();

        if (!empty($location)) {
            $this->out->element('p', 'profile_block_location', $location);
        }
    }

    function showProfileIcons()
    {
        $xmpp = $this->xmpp();
        $gpgpubkey = $this->gpgpubkey();
        $toxid = $this->toxid();
        $matrix = $this->matrix();

        $this->out->elementStart('p');

        if (!empty($xmpp)) {
            $this->out->elementStart('a',
                                     array('href' => "xmpp:$xmpp",
                                           'rel' => '',
                                           'class' => 'profile_block_homepage'));

            $this->out->element('img', array('src' => Avatar::url("../plugins/Qvitter/img/xmppbutton.png"),
                                'width' => 40,
                                'height' => 40,
                                'alt' => $xmpp));

            $this->out->elementEnd('a');
        }

        if (!empty($gpgpubkey)) {
            $this->out->elementStart('a',
                                     array('href' => "pgp:$gpgpubkey",
                                           'rel' => '',
                                           'class' => 'profile_block_homepage'));

            $this->out->element('img', array('src' => Avatar::url("../plugins/Qvitter/img/gpgbutton.jpg"),
                                'width' => 40,
                                'height' => 40,
                                'alt' => $gpgpubkey));

            $this->out->elementEnd('a');
        }

        if (!empty($toxid)) {
            $this->out->elementStart('a',
                                     array('href' => "tox:$toxid",
                                           'rel' => '',
                                           'class' => 'profile_block_homepage'));

            $this->out->element('img', array('src' => Avatar::url("../plugins/Qvitter/img/toxbutton.png"),
                                'width' => 40,
                                'height' => 40,
                                'alt' => $toxid));

            $this->out->elementEnd('a');
        }

        if (!empty($matrix)) {
            $this->out->elementStart('a',
                                     array('href' => "matrix:$matrix",
                                           'rel' => '',
                                           'class' => 'profile_block_homepage'));

            $this->out->element('img', array('src' => Avatar::url("../plugins/Qvitter/img/matrixbutton.png"),
                                'width' => 40,
                                'height' => 40,
                                'alt' => $matrix));

            $this->out->elementEnd('a');
        }

        $this->out->elementEnd('p');
    }

    function showHomepage()
    {
        $homepage = $this->homepage();

        if (!empty($homepage)) {
            $this->out->element('a',
                                array('href' => $homepage,
                                      'rel' => 'me',
                                      'class' => 'profile_block_homepage'),
                                $homepage);
        }
    }

    function showOtherProfiles()
    {
        $otherProfiles = $this->otherProfiles();

        if (!empty($otherProfiles)) {

            $this->out->elementStart('ul',
                                     array('class' => 'profile_block_otherprofile_list'));

            foreach ($otherProfiles as $otherProfile) {
                $this->out->elementStart('li');
                $this->out->elementStart('a',
                                         array('href' => $otherProfile['href'],
                                               'rel' => 'me',
                                               'class' => 'profile_block_otherprofile',
                                               'title' => $otherProfile['text']));
                $this->out->element('img',
                                    array('src' => $otherProfile['image'],
                                          'class' => 'profile_block_otherprofile_icon'));
                $this->out->elementEnd('a');
                $this->out->elementEnd('li');
            }

            $this->out->elementEnd('ul');
        }
    }

    function showTags()
    {
    }

    function showActions()
    {
    }
}
?>
