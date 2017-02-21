INSTALLING POSTACTIV
=================
* Prerequisites
    - PHP modules
    - Better performance
* Installation
    - Getting it up and running
    - Fancy URLs
    - Themes
    - Private
* Extra features
    - Sphinx
    - SMS
    - Translation
    - Queues and daemons
* After installation
    - Backups
    - Upgrading

Prerequisites
=============

PHP modules
-----------

The following software packages are *required* for this software to
run correctly.

- PHP 5.5+      For newer versions, some functions that are used may be
                disabled by default, such as the pcntl_* family. See the
                section on 'Queues and daemons' for more information.
- MySQL 5+      postActiv supports MySQL 5.5+ by default.  MariaDB 10+
                should also work in theory but can run into some obscure
                errors. postgreSQL support is currently in development
- Web server    Apache, lighttpd and nginx will all work. CGI mode is
                recommended and also some variant of 'suexec' (or a
                proper setup php-fpm pool)
                NOTE: mod_rewrite or its equivalent is extremely useful.

Your PHP installation must include the following PHP extensions for a
functional setup of postActiv:

- openssl       (compiled in for Debian, enabled manually in Arch Linux)
- php5-curl     Fetching files by HTTP.
- php5-gd       Image manipulation (scaling).
- php5-gmp      For Salmon signatures (part of OStatus).
- php5-intl     Internationalization support (transliteration et al).
- php5-json     For WebFinger lookups and more.
- php5-mysqlnd  The native driver for PHP5 MariaDB connections. If you
                  use MySQL, 'php5-mysql' or 'php5-mysqli' may be enough.

Or, for PHP7, some or all of these will be necessary. PHP7 support is still
experimental and not necessarily working:
    php7.0-bcmath
    php7.0-curl
    php7.0-exif
    php7.0-gd
    php7.0-intl
    php7.0-mbstring
    php7.0-mysqlnd
    php7.0-opcache
    php7.0-readline
    php7.0-xmlwriter

The above package names are for Debian based systems. In the case of
Arch Linux, PHP is compiled with support for most extensions but they
require manual enabling in the relevant php.ini file (mostly php5-gmp).

Debian install
--------------

Here's how to set the system up on a new installation of Debian stable. You will need a domain name capable of getting a LetsEncrypt certificate and also to forward ports 80 and 443 to your server machine. Then run:

``` bash
su
apt-get -yq install git
git clone https://git.postactiv.com/postActiv/postActiv.git /var/www/postactiv
cd /var/www/postactiv
./scripts/debian_install.sh [mariadb password] [username] [password] [domain] [email address]
```

This installs everything needed, including the web server, TLS certificate and database.

Better performance
------------------

For some functionality, you will also need the following extensions:

- opcache       Improves performance a _lot_. Included in PHP, must be
                enabled manually in php.ini for most distributions. Find
                and set at least:  opcache.enable=1
- mailparse     Efficient parsing of email requires this extension.
                Submission by email or SMS-over-email uses this.
- sphinx        A client for the sphinx server, an alternative to MySQL
                or Postgresql fulltext search. You will also need a
                Sphinx server to serve the search queries.
- gettext       For multiple languages. Default on many PHP installs;
                will be emulated if not present.
- exif          For thumbnails to be properly oriented.

You may also experience better performance from your site if you configure
a PHP cache/accelerator. Most distributions come with "opcache" support.
Enable it in your php.ini where it is documented together with its settings.

Installation
============

Getting it up and running
-------------------------

Installing the basic postActiv web component is relatively easy,
especially if you've previously installed PHP/MySQL packages.

There's two methods to installing the software on your server:
using the archive bundles, or using git-scm version control.

Installing from the Archive Bundle
----------------------------------
1. Unpack the tarball you downloaded on your Web server. Usually a
   command like this will work:

       tar zxf archive.tar.gz?ref=master

   ...which will make a postactiv-x.y.z subdirectory in your current
   directory. (If you don't have shell access on your Web server, you
   may have to unpack the tarball on your local computer and FTP the
   files to the server.)

2. Move the tarball to a directory of your choosing in your Web root
   directory. Usually something like this will work:

       mv postactiv-x.y.z /var/www/postactiv

   This will often make your postActiv instance available in the postactiv
   path of your server, like "http://example.net/gnusocial". "social" or
   "blog" might also be good path names. If you know how to configure
   virtual hosts on your web server, you can try setting up
   "http://social.example.net/" or the like.

   If you have "rewrite" support on your webserver, and you should,
   then please enable this in order to make full use of your site. This
   will enable "Fancy URL" support, which you can read more about if you
   scroll down a bit in this document.

3. Make your target directory writeable by the Web server, please note
   however that 'a+w' will give _all_ users write access and securing the
   webserver is not within the scope of this document.

       chmod a+w /var/www/postactiv/

   On some systems, this will work as a more secure alternative:

       chgrp www-data /var/www/postactiv/
       chmod g+w /var/www/postactiv/

   If your Web server runs as another user besides "www-data", try
   that user's default group instead. As a last resort, you can create
   a new group like "postactiv" and add the Web server's user to the group.

4. You will need to create a directory to store avatars, and one to store
   file attachments.  By default, these are "avatar" and "file", so you
   would want to do something like:

       mkdir /var/www/postactiv/avatar
       mkdir /var/www/postactiv/file

5. You should also take this moment to make your 'avatar' and 'file' sub-
   directories writeable by the Web server. The _insecure_ way to do
   this is:

       chmod a+w /var/www/postactiv/avatar
       chmod a+w /var/www/postactiv/file

   You can also make the avatar, and file directories just writable by
   the Web server group, as noted above.

6. Create a database to hold your site data. Something like this
   should work (you will be prompted for your database password):

       mysqladmin -u "root" -p create social

   Note that postActiv should have its own database; you should not share
   the database with another program. You can name it whatever you want,
   though.

   (If you don't have shell access to your server, you may need to use
   a tool like phpMyAdmin to create a database. Check your hosting
   service's documentation for how to create a new MySQL database.)

7. Create a new database account that postActiv will use to access the
   database. If you have shell access, this will probably work from the
   MySQL shell:

       GRANT ALL on social.*
       TO 'social'@'localhost'
       IDENTIFIED BY 'anexcellentpassword';

   You should change the user identifier 'social' and 'agoodpassword'
   to your preferred new database username and password. You may want to
   test logging in to MySQL as this new user.

8. In a browser, navigate to the postActiv install script; something like:

       https://social.example.net/install.php

   Enter the database connection information and your site name. The
   install program will configure your site and install the initial,
   almost-empty database.

9. You should now be able to navigate to your social site's main directory
   and see the "Public Timeline", which will probably be empty. You can
   now register new user, post some notices, edit your profile, etc.

Installing using git-scm
------------------------
Using git-scm to install the software will allow you to keep much more easily
up to date with the latest version, since you can just use git to retrieve
the most recent version of the branch you want to run and it will handle the
rest.

It should go without saying, this method requires git installed to use.

1. Download the public access key from the repository, which will allow
   git read-only access to the repository.  This allows you to download the
   repository but not push or commit changes to it.  You can find it here:

   https://git.postactiv.com/postActiv/postActiv/raw/master/pa-public-access.ppk

2. Add the public access key to your SSH keyring.  How to do so will depend
   on your SSH client.  In Windows using PuTTY, just add the key to pageant.
   In Linux using openssh-client, copy the sections labelled public and
   private into the appropriate sections in your SSH configuration.

3. With the key loaded, make switch to the directory that you want to install
   postActiv into.  For example, /var/www/postactiv

4. Clone the git repository into this directory with the following command:

   git clone git@git.postactiv.com:postActiv/postActiv.git

   This will take a moment and download all the files that comprise the
   postActiv installation.  If you get an error that you're not authenticated,
   it likely means you do not have the public access key in your keyring.
   Ensure you do and check again.

5. Make your target directory writeable by the Web server, please note
   however that 'a+w' will give _all_ users write access and securing the
   webserver is not within the scope of this document.

       chmod a+w /var/www/postactiv/

   On some systems, this will work as a more secure alternative:

       chgrp www-data /var/www/postactiv/
       chmod g+w /var/www/postactiv/

   If your Web server runs as another user besides "www-data", try
   that user's default group instead. As a last resort, you can create
   a new group like "postactiv" and add the Web server's user to the group.

6. You should also take this moment to make your 'avatar' and 'file' sub-
   directories writeable by the Web server. The _insecure_ way to do
   this is:

       chmod a+w /var/www/postactiv/avatar
       chmod a+w /var/www/postactiv/file

   You can also make the avatar, and file directories just writable by
   the Web server group, as noted above.

7. Create a database to hold your site data. Something like this
   should work (you will be prompted for your database password):

       mysqladmin -u "root" -p create social

   Note that postActiv should have its own database; you should not share
   the database with another program. You can name it whatever you want,
   though.

   (If you don't have shell access to your server, you may need to use
   a tool like phpMyAdmin to create a database. Check your hosting
   service's documentation for how to create a new MariaDB database.)

8. Create a new database account that postActiv will use to access the
   database. If you have shell access, this will probably work from the
   MySQL shell:

       GRANT ALL on social.*
       TO 'social'@'localhost'
       IDENTIFIED BY 'anexcellentpassword';

   You should change the user identifier 'social' and 'agoodpassword'
   to your preferred new database username and password. You may want to
   test logging in to MySQL as this new user.

9. In a browser, navigate to the postActiv install script; something like:

       https://social.example.net/install.php

   Enter the database connection information and your site name. The
   install program will configure your site and install the initial,
   almost-empty database.

10. You should now be able to navigate to your social site's main directory
   and see the "Public Timeline", which will probably be empty. You can
   now register new user, post some notices, edit your profile, etc.

Note for Running on Shared Webhosts
------------------------------------
If you're running postActiv on a shared webhost without shell access, 
you'll want to uncomment the following line in config.php:

```php
//$config['db']['schemacheck'] = 'runtime';
```

**This will degrade performance however, since it runs a database integrity
check on every page load.  If you have shell access, this setting 
should be disabled.**

Log Filtering
-------------
By default, all of the various log messages are enabled in your postActiv log
location.  This allows you to identify any install problems very easily, since
the output is quite verbose by default to aid in troubleshooting installs gone
wrong.  Once you know your site is running properly however, most of this
information is probably superfluous.  You can use the LogFilter module to filter
out the LOG_DEBUG and LOG_INFO level messages so you only see errors in the
postActiv log by adding the following near the bottom of config.php:

addPlugin('LogFilter', array(
    'priority' => array(LOG_DEBUG => false,LOG_INFO==>false)
));

More information about this module is in its README, if you wish to customise
this filtering more closely, such as using RegEx patterns.


Fancy URLs
----------

By default, postActiv will use URLs that include the main PHP program's
name in them. For example, a user's home profile might be found at either
of these URLS depending on the webserver's configuration and capabilities:

    https://social.example.net/index.php/fred
    https://social.example.net/index.php?p=fred

It's possible to configure the software to use fancy URLs so it looks like
this instead:

    https://social.example.net/fred

These "fancy URLs" are more readable and memorable for users. To use
fancy URLs, you must either have Apache 2.x with .htaccess enabled and
mod_rewrite enabled, -OR- know how to configure "url redirection" in
your server (like lighttpd or nginx).

1. See the instructions for each respective webserver software:
    * For Apache, inspect the "htaccess.sample" file and save it as
        ".htaccess" after making any necessary modifications. Our sample
        file is well commented.
    * For lighttpd, inspect the lighttpd.conf.example file and apply the
        appropriate changes in your virtualhost configuration for lighttpd.
    * For nginx, inspect the nginx.conf.sample file and apply the appropriate
        changes.
    * For other webservers, we gladly accept contributions of
        server configuration examples.

2. Assuming your webserver is properly configured and have its settings
    applied (remember to reload/restart it), you can add this to your
    postActiv install's config.php file:
       $config['site']['fancy'] = true;

You should now be able to navigate to a "fancy" URL on your server,
like:

    https://social.example.net/main/register

Themes
------

As of right now, your ability change the theme is limited to CSS
stylesheets and some image files; you can't change the HTML output,
like adding or removing menu items, without the help of a plugin.

You can choose a theme using the $config['site']['theme'] element in
the config.php file. See below for details.

You can add your own theme by making a sub-directory of the 'theme'
subdirectory with the name of your theme. Each theme can have the
following files:

display.css: a CSS2 file for "default" styling for all browsers.
logo.png: a logo image for the site.
default-avatar-profile.png: a 96x96 pixel image to use as the avatar for
    users who don't upload their own.
default-avatar-stream.png: Ditto, but 48x48. For streams of notices.
default-avatar-mini.png: Ditto ditto, but 24x24. For subscriptions
    listing on profile pages.

You may want to start by copying the files from the default theme to
your own directory.

Private
-------

A postActiv node can be configured as "private", which means it will not
federate with other nodes in the network. It is not a recommended method
of using postActiv and we cannot at the current state of development
guarantee that there are no leaks (what a public network sees as features,
private sites will likely see as bugs).

Private nodes are however an easy way to easily setup collaboration and
image sharing within a workgroup or a smaller community where federation
is not a desired feature. Also, it is possible to change this setting and
instantly gain full federation features.

Access to file attachments can also be restricted to logged-in users only:

1. Add a directory outside the web root where your file uploads will be
   stored. Use this command as an initial guideline to create it:

       mkdir /var/www/postactiv-files

2. Make the file uploads directory writeable by the web server. An
   insecure way to do this is (to do it properly, read up on UNIX file
   permissions and configure your webserver accordingly):

       chmod a+x /var/www/postactiv-files

3. Tell GNU social to use this directory for file uploads. Add a line
   like this to your config.php:

       $config['attachments']['dir'] = '/var/www/postactiv-files';

Extra features
==============

Sphinx
------

To use a Sphinx server to search users and notices, you'll need to
enable the SphinxSearch plugin. Add to your config.php:

    addPlugin('SphinxSearch');
    $config['sphinx']['server'] = 'searchhost.local';

You also need to install, compile and enable the sphinx pecl extension for
php on the client side, which itself depends on the sphinx development files.

See plugins/SphinxSearch/README for more details and server setup.

SMS
---

StatusNet supports a cheap-and-dirty system for sending update messages
to mobile phones and for receiving updates from the mobile. Instead of
sending through the SMS network itself, which is costly and requires
buy-in from the wireless carriers, it simply piggybacks on the email
gateways that many carriers provide to their customers. So, SMS
configuration is essentially email configuration.

Each user sends to a made-up email address, which they keep a secret.
Incoming email that is "From" the user's SMS email address, and "To"
the users' secret email address on the site's domain, will be
converted to a notice and stored in the DB.

For this to work, there *must* be a domain or sub-domain for which all
(or most) incoming email can pass through the incoming mail filter.

1. Run the SQL script carrier.sql in your StatusNet database. This will
   usually work:

       mysql -u "statusnetuser" --password="statusnetpassword" statusnet < db/carrier.sql

   This will populate your database with a list of wireless carriers
   that support email SMS gateways.

2. Make sure the maildaemon.php file is executable:

       chmod +x scripts/maildaemon.php

   Note that "daemon" is kind of a misnomer here; the script is more
   of a filter than a daemon.

2. Edit /etc/aliases on your mail server and add the following line:

       *: /path/to/statusnet/scripts/maildaemon.php

3. Run whatever code you need to to update your aliases database. For
   many mail servers (Postfix, Exim, Sendmail), this should work:

       newaliases

   You may need to restart your mail server for the new database to
   take effect.

4. Set the following in your config.php file:

       $config['mail']['domain'] = 'yourdomain.example.net';

Translations
------------

For info on helping with translations, see the platform currently in use
for translations: https://www.transifex.com/projects/p/gnu-social/

Translations use the gettext system <http://www.gnu.org/software/gettext/>.
If you for some reason do not wish to sign up to the Transifex service,
you can review the files in the "locale/" sub-directory of postActiv.
Each plugin also has its own translation files.

To get your own site to use all the translated languages, and you are
tracking the git repo, you will need to install at least 'gettext' on
your system and then run:
    $ make translations

Queues and daemons
------------------

Some activities that postActiv needs to do, like broadcast OStatus, SMS,
XMPP messages and TwitterBridge operations, can be 'queued' and done by
off-line bots instead.

Two mechanisms are available to achieve offline operations:

* Embedded OpportunisticQM plugin, which is enabled by default
* Redis-backed queue manager, which is the recommended option, but
  requires a Redis server set up.
* Legacy queuedaemon script, which can be enabled via config file.

### OpportunisticQM plugin

This plugin is enabled by default. It tries its best to do background
jobs during regular HTTP requests, like API or HTML pages calls.

Since queueing system is enabled by default, notices to be broadcasted
will be stored, by default, into DB (table queue_item).

Whenever it has time, OpportunisticQM will try to handle some of them.

This is a good solution whether you:

* have no access to command line (shared hosting)
* do not want to deal with long-running PHP processes
* run a low traffic postActiv instance

In other case, you really should consider enabling the Redis queue manager or
queuedaemon for performance reasons.  OpprotunisticQM is essentially the slower
option that is compatible with the most environments, but if you can run Redis
queue manager, or the queue daemons, then it is best to do so.

### Redis queue manager

If you have a Redis server available, you can use our brand-spanking-new Redis
queue manager.  This uses the in-memory storage capabilities of Redis to keep the
queue in memory as much as possible and thus reduces a lot of strain on the
database which is introduced by using OpprotunisticQM or the legacy queuedaemon.

You can get Redis from https://redis.io/ and there is a Quick Install guide at
https://redis.io/topics/quickstart that can help you get it going.

In most systems, you will need three packagaes to make this work.  For example,
in CentOS, you can install the required environment with:

    yum install redis php-pecl-redis php-gmp

For other distributions, the package names may change.

Once Redis is set up and confirmed to be working, you will need to set the 
following in your config.php:

To enable redis queue:

* queue subsystem - redis

If you have Redis running on a UNIX socket:

* redis_socket_location - the location of the UNIX socket

If you have Redis running as a web service:

* redis_host - the URL to the host

* redis_port - the TCP port Redis is operating on

There are also some optional things you can set up to tweak your queue:

* redis_namespace - you can use this to set a namespace for your queue items to
  seperate them for multiple sites, or if you use Redis for somehing else as well

* redis_retries - how many times to deliver a remote message before it fails and is
  dropped (default 10)

* redis_expiration - how long to hold a remote message in Redis before it is dropped
  (default 86400, which is 1 day)


### queuedaemon

If you want to use legacy queuedaemon, you must be able to run
long-running offline processes, either on your main Web server or on
another server you control. (Your other server will still need all the
above prerequisites, with the exception of Apache.) Installing on a
separate server is probably a good idea for high-volume sites.

1. You'll need the "CLI" (command-line interface) version of PHP
   installed on whatever server you use.

   Modern PHP versions in some operating systems have disabled functions
   related to forking, which is required for daemons to operate. To make
   this work, make sure that your php-cli config (/etc/php5/cli/php.ini)
   does NOT have these functions listed under 'disable_functions':

       * pcntl_fork, pcntl_wait, pcntl_wifexited, pcntl_wexitstatus,
         pcntl_wifsignaled, pcntl_wtermsig

   Other recommended settings for optimal performance are:
       * mysqli.allow_persistent = On
       * mysqli.reconnect = On

2. If you're using a separate server for queues, install StatusNet
   somewhere on the server. You don't need to worry about the
   .htaccess file, but make sure that your config.php file is close
   to, or identical to, your Web server's version.

3. In your config.php files (on the server where you run the queue
    daemon), set the following variable:

       $config['queue']['daemon'] = true;

   You may also want to look at the 'Queues and Daemons' section in
   this file for more background processing options.

4. On the queues server, run the command scripts/startdaemons.sh.

This will run the queue handlers:

* queuedaemon.php - polls for queued items for inbox processing and
  pushing out to OStatus, SMS, XMPP, etc.
* imdaemon.php - if an IM plugin is enabled (like XMPP)
* other daemons, like TwitterBridge ones, that you may have enabled

These daemons will automatically restart in most cases of failure
including memory leaks (if a memory_limit is set), but may still die
or behave oddly if they lose connections to the XMPP or queue servers.

It may be a good idea to use a daemon-monitoring service, like 'monit',
to check their status and keep them running.

All the daemons write their process IDs (pids) to /var/run/ by
default. This can be useful for starting, stopping, and monitoring the
daemons. If you are running multiple sites on the same machine, it will
be necessary to avoid collisions of these PID files by setting a site-
specific directory in config.php:

       $config['daemon']['piddir'] = __DIR__ . '/../run/';

It is also possible to use a STOMP server instead of our kind of hacky
home-grown DB-based queue solution. This is strongly recommended for
best response time, especially when using XMPP.

After installation
==================

Backups
-------

There is no built-in system for doing backups in postActiv. You can make
backups of a working StatusNet system by backing up the database and
the Web directory. To backup the database use mysqldump <https://mariadb.com/kb/en/mariadb/mysqldump/>
and to backup the Web directory, try tar.

Upgrading
---------

Upgrading is strongly recommended to stay up to date with security fixes
and new features. For instructions on how to upgrade postActiv code,
please see the UPGRADE file.
