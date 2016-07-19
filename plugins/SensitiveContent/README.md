# "Sensitive" Content Plugin for GNU Social

## About

Create user option to allow a user to hide #NSFW-hashtagged notices behind a blocker image until clicked.

Works for both vanilla GNUSocial and with the Qvitter plugin.

## Install

- Move the project directory to ${GNU_SOCIAL}/plugins
- Add addPlugin('SensitiveContent'); to your config.php

if you want to customize the blocker image, add a line to your config.php:

  $config['site']['sensitivecontent']['blockerimage'] = "/path/to/image.jpg";

## License

GNU Affero License

## Thanks

Thanks in particular to Hannes and Qvitter because looking at his code helped me a lot.

A tiny bit of content was taken from Qvitter to enhance Qvitter with this funcitonality.
