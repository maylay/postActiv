Modules
================================================================================

postActiv modules are basically plugins that contain core functionality.  As our
predecessor GNU social was developed, many things that would come to be core
features, such as oStatus and oEmbed, would be developed originally as plugins,
and then expanded in the core programming as well, until they really weren't
optional

To distinguish something is optional versus something that is not, postActiv
houses plugins that are mandatory for proper functioning of the software, as
"modules", in this directory.

Installation
-------------------------------------------------------------------------------
It is not usually neccesary to activate these as it is with plugins, however,
should you find it neccesary to, you can do this via adding it to config.php
just as you would a normal plugin.  /modules/ is part of the plugin search path
and to the program, there is no distinction between a module and a plugin other
than its path.

```php
    addPlugin('Example', array('param1' => 'value1',
                               'param2' => 'value2'));
```

Documentation
-------------------------------------------------------------------------------
Modules are documented in their own directories.

Additional information on using and developing plugins can be found
at the following locations:

* [Plugin Development](doc/Plugin_development.md)
* [Community Plugins](http://www.skilledtests.com/wiki/GNU_social_plugins)

If you want your plugin to be considered as a core feature, please submit a
request with it as a plugin all the same, per [the Contribution Guide](CONTRIBUTING.md),
and if it is adopted by the community as a whole and would benefit from closer
implementation, we can evaluate it at that time.  New plugins submitted as a
module will probably be rejected.