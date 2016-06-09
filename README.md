# postActiv 1.0
(c) 2016 Maiyannah Bishop

Derived from code copyright various sources:
 * GNU Social (C) 2013-2016, Free Software Foundation, Inc
 * StatusNet (C) 2008-2011, StatusNet, Inc

This is the README file for postActiv, a fork of the free software networking
platform GNU social, which refactors the code base and adds several patches and
features. It includes general information about the software and the project.

Some other files to review:

- INSTALL: instructions on how to install the software.
- UPGRADE: upgrading from earlier versions
- CONFIGURE: configuration options in gruesome detail.
- PLUGINS.txt: how to install and configure plugins.
- EVENTS.txt: events supported by the plugin system
- COPYING: full text of the software license

Information on using GNU social can be found in the "doc" subdirectory or in
the "help" section on-line, or you can contact maiyannah on the fediverse at
@maiyannah@community.highlandarrow.com or her email at 
<maiyannah.bishop@postactiv.com>

## About

postActiv is a free software project to develop a social networking platform.
It is derived from GNU Social, which is itself derived from StatusNet and
Laconica.  It helps people in a community, company, or group to exchange short
status updates, do polls, annouce events, or other social activites.  Users can
choose which people to "follow" and receive only their friends' or collegues'
status messages.  They can also view the public timeline of the site to see
everyone, or the "whole known network" timeline, to see all the people in sites
that have connected with this one, by someone following them.  It provides a
similar service to proprietary social network sites, but is decentralized, and
with many additional features and an open, free software codebase that you can
develop your own plugins for, it is much more awesome.

Using plugins available to postActiv, status messages can be sent to mobile
phones or pages, instant messenger clients that implement XMPP, and desktop
clients with support for the Twitter API.  It is also compatible with plugins
developed for GNU Social.

postActiv supports an open standard called OStatus
<https://www.w3.org/community/ostatus/> that lets users in different networks
follow each other. It enables a distributed social network spread all across
the Web.

postActive is derived from GNU Social, and GNU Social was originally developed
as "StatusNet" by StatusNet, Inc. with Evan Prodromou as lead developer.
It is forked form commit bd306bdb9fb43e80f9092784602a9508a7d52031 in the Nightly
branch of GNU Social, available here:
<https://git.gnu.io/gnu/gnu-social/commit/bd306bdb9fb43e80f9092784602a9508a7d52031>

It is shared with you in hope that you too make an service available to your
users. To learn more, please see the Open Software Service Definition 1.1:
<http://www.opendefinition.org/ossd>

You can read more about postActiv at the official website:
<http://postactiv.com>

### License

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU Affero General Public License as published by the Free
Software Foundation, either version 3 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along
with this program, in the file "COPYING".  If not, see <http://www.gnu.org/
licenses/>.

    IMPORTANT NOTE: The GNU Affero General Public License (AGPL) has
    *different requirements* from the "regular" GPL. In particular, if
    you make modifications to the GNU social source code on your server,
    you *MUST MAKE AVAILABLE* the modified version of the source code
    to your users under the same license. This is a legal requirement
    of using the software, and if you do not wish to share your
    modifications, *YOU MAY NOT INSTALL GNU SOCIAL*.

Documentation in the /doc-src/ directory is available under the
Creative Commons Attribution 3.0 Unported license, with attribution to
"GNU social". See <http://creativecommons.org/licenses/by/3.0/> for details.

CSS and images in the /theme/ directory are available under the
Creative Commons Attribution 3.0 Unported license, with attribution to
"GNU social". See <http://creativecommons.org/licenses/by/3.0/> for details.

Our understanding and intention is that if you add your own theme that uses
only CSS and images, those files are not subject to the copyleft requirements
of the Affero General Public License 3.0.
See <http://wordpress.org/news/2009/07/themes-are-gpl-too/>.
This is not legal advice; consult your lawyer.

Additional library software has been made available in the 'extlib' directory.
All of it is Free Software and can be distributed under liberal terms, but
those terms may differ in detail from the AGPL's particulars. See each package's
license file in the extlib directory for additional terms.

## Requirements
The minimum requirements to run postActiv are the following:

* PHP: PHP 5 or higher is neccesary.  We recommend 5.4.  PHP 7 should work but
  is untested so there's probably some stuff that won't work properly.
* MariaDB: You need either a MariaDB or MySQL database available for postActiv
  to store information in.
* Web server: You must have either an Apache, nginx, or Litespeed web server
  configured with PHP support to serve up postActiv.  We recommend Apache, but
  nginx is also well-supported.
  
## Installation
Detailed installation information is in INSTALLING, but in basic, you will want
to download the branch archive of your choice, unzip it to a web-accessible
directory, and then run the Install.php file, which will guide you through
further setup.  You will want to have a database available for postActiv, of
course.

## Compatibility
Being a fork of GNU social, most plugins that work with GNU social 1.2.0-beta4,
the version of GNU social it was forked from, should also work with postActiv.
However, if you run into porting issues with a GNU social plugin that you know
works with GNU social but does not with postActiv, please raise an issue in the
issue tracker and we can look into this.

## Troubleshooting
The primary output for postActiv is syslog, unless you configured a separate
logfile. This is probably the first place to look if you're getting weird
behaviour from postActiv.

If you wish the postActiv log file to be in another location, specify this
with the following in the config.php file:

    $config['site']['logfile'] = '/path/to/postactiv.log';

If you're tracking the unstable version of postActiv in the git repository (see
below), and you get a compilation error ("unexpected T_STRING") in the browser,
check to see that you don't have any conflicts in your code.

In the event you run into problems you can't fix yourself, you can ask for
assistance on the users mailing list, at <users@postactiv.com>

## Unstable version

If you're adventurous or impatient, you may want to install the development
version of GNU social. To get it, use the git version control tool
<http://git-scm.com/> like so:

    git clone git@git.gnu.io:maiyannah/postActiv.git

In the current phase of development it is probably recommended to use git as a
means to stay up to date with the source code. You can choose between these
branches:

- release   "stable", few updates, well tested code
- master    "testing", more updates, usually working well
- nightly   "unstable", most updates, not always working

To keep it up-to-date, use 'git pull'. Watch for conflicts!

## Further information

There are several ways to get more information about GNU social.

* Following us on the Fediverse -- 
<https://community.highlandarrow.com/postActiv>

* psotActiv has a bug tracker for any defects you may find, or ideas for
  making things better. <https://git.gnu.io/maiyannah/postActiv/issues/>
* Patches are welcome, preferrably to our repository on git.gnu.io. 
  <https://git.gnu.io/maiyannah/postActiv>
* There is a users mailing list at <users@postactiv.com>

Credits
=======

The following is a list of developers who've contributed to postActiv:

## Lead Maintainer / Developer

* Maiyannah Bishop - <maiyannah.bishop@postactiv.com>

## Contributors
* Verius - <verius@postactiv.com>
* Neil E Hodges

Acknowledgements
================

The following is an incomplete list of developers who've worked on GNU social,
or its predecessors StatusNet and Free Social, which postActiv is derived from
and acknowledges the hard work and efforts of developers

## Project Founders

* Matt Lee (GNU social)
* Evan Prodromou (StatusNet)
* Mikael Nordfeldth (Free Social)

Thanks to all of the StatusNet developers:

* Zach Copley, StatusNet, Inc.
* Earle Martin, StatusNet, Inc.
* Marie-Claude Doyon, designer, StatusNet, Inc.
* Sarven Capadisli, StatusNet, Inc.
* Robin Millette, StatusNet, Inc.
* Ciaran Gultnieks
* Michael Landers
* Ori Avtalion
* Garret Buell
* Mike Cochrane
* Matthew Gregg
* Florian Biree
* Erik Stambaugh
* 'drry'
* Gina Haeussge
* Tryggvi Björgvinsson
* Adrian Lang
* Ori Avtalion
* Meitar Moscovitz
* Ken Sheppardson (Trac server, man-about-town)
* Tiago 'gouki' Faria (i18n manager)
* Sean Murphy
* Leslie Michael Orchard
* Eric Helgeson
* Ken Sedgwick
* Brian Hendrickson
* Tobias Diekershoff
* Dan Moore
* Fil
* Jeff Mitchell
* Brenda Wallace
* Jeffery To
* Federico Marani
* mEDI
* Brett Taylor
* Brigitte Schuster
* Siebrand Mazeland and the amazing volunteer translators at translatewiki.net
* Brion Vibber, StatusNet, Inc.
* James Walker, StatusNet, Inc.
* Samantha Doherty, designer, StatusNet, Inc.
* Simon Waters, Surevine
* Joshua Judson Rosen (rozzin)

### Extra special thanks to the GNU socialites

* Craig Andrews
* Donald Robertson
* Deb Nicholson
* Ian Denhart
* Steven DuBois
* Blaine Cook
* Henry Story
* Melvin Carvalho

Thanks also to the developers of our upstream library code and to the thousands 
of people who have tried out GNU social, told their friends, and built the 
fediverse network to what it is today.

### License help from

* Bradley M. Kuhn
