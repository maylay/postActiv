Several example plugins are included in the plugins/ directory. You
can enable a plugin with the following line in config.php:

```php
    addPlugin('Example', array('param1' => 'value1',
                               'param2' => 'value2'));
```

This will look for and load files named 'ExamplePlugin.php' or
'Example/ExamplePlugin.php' either in the plugins/ directory (for
plugins that ship with StatusNet) or in the local/ directory (for
plugins you write yourself or that you get from somewhere else) or
local/plugins/.

Plugins are documented in their own directories.

Additional information on using and developing plugins can be found
at the following locations:

* [Plugin Development](doc/Plugin_development.md)
* [Community Plugins](http://www.skilledtests.com/wiki/GNU_social_plugins)

You can find postActiv's own repository of user plugins at:
https://git.postactiv.com/explore/projects?group=&scope=&sort=&tag=postactiv-plugin&visibility_level=

If you wish access to the gitlab install for purpose of creating your own
plugin project, we'd love that!  Just email <users@postactiv.com> to ask and
one of the administrators will create an account for you.