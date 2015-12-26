# Config

[![Release](https://img.shields.io/packagist/v/ICanBoogie/config.svg)](https://packagist.org/packages/icanboogie/config)
[![Build Status](https://img.shields.io/travis/ICanBoogie/Config.svg)](http://travis-ci.org/ICanBoogie/Config)
[![HHVM](https://img.shields.io/hhvm/icanboogie/config.svg)](http://hhvm.h4cc.de/package/icanboogie/config)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Config.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Config)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Config.svg)](https://coveralls.io/r/ICanBoogie/Config)
[![Packagist](https://img.shields.io/packagist/dt/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)

An API to synthesize low-level configuration.

This package is used by the framework [ICanBoogie][] to configure its components.

Configurations are defined by a set of files that are called _fragments_. These fragments are
used to synthesize the configuration of one or more components. When the same fragments
are used to synthesize different configurations, these configurations are qualified
as _derived_. Fragments are synthesized using callback functions called _synthesizers_.
Configurations are managed by a [Config][] instance. Finally, synthesized configurations
can be cached, which cancel the cost of the synthesis.





## Configuration fragments

A configuration fragment is a PHP file returning an array. Multiple fragments are used to
synthesize a configuration, or _derived_ configuration. They are usually located in "config"
directories and are usually named after the config they are used to synthesize.





## Synthesizing a configuration

The `synthesize()` method of the [Config][] instance is used to synthesize a configuration.

The following example demonstrates how a closure can be used to synthesize multiple fragments:

```php
<?php

$config = $config->synthesize('core', function(array $fragments) {

	return call_user_func_array('ICanBoogie\array_merge_recursive', $fragments);
	
});
```





### Magic constructors

The `synthesize()` methods supports the _magic constructors_ `merge` and `recursive merge` which
respectively use the `array_merge()` and `ICanBoogie\array_merge_recursive()` functions. Thus,
the following code examples are equivalent:

```php
<?php

$core_config = $config->synthesize('core', function(array $fragments) {

	return call_user_func_array('ICanBoogie\array_merge_recursive', $fragments);
	
});
```

```php
<?php

$core_config = $config->synthesize('core', 'merge recursive');
```





## Configuration synthesizers

Synthesizers can be defined for each configuration, they are used when the config collection is
used as an array:
 
```
<?php

$synthesizers = [ 'core' => 'merge recursive' ];
$config = new Config($paths, $synthesizers);
$core_config = $configs['core'];
```





## _Derived_ configurations

It is possible to synthesize a configuration from the same fragments as another configuration,
such a configuration is qualified as _derived_.

For instance, events for the [icanboogie/event][] package are defined in "hooks" fragments, the
synthesizer for the `events` configuration filters the fragments data to extract what is
relevant.

The following example demonstrates how to obtain the `events` configuration using the
`synthesize()` method:

```php
<?php

$events_config = $config->synthesize('events', 'ICanBoogie\Event\Hooks::synthesize_config', 'hooks');
```

The following example demonstrates how to define the synthesizer of the `events` configuration:

```php
<?php

$config = new Config($paths, [

	'events' => [ 'ICanBoogie\Event\Hooks::synthesize_config', 'hooks' ]

]);
```




	
## Caching synthesized configurations

Caching synthesized configurations removes the cost of synthesizing configurations by reusing the
result of a previous synthesis. To enable caching, you just need to provide a cache implementing
[Storage][].

```php
<?php

$config = new Config($paths, $synthesizers, $cache);
```





----------





## Requirements

The package requires PHP 5.5 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/):

```
composer require icanboogie/config
```





### Cloning the repository

The package is [available on GitHub](https://github.com/ICanBoogie/Config), its repository can be
cloned with the following command line:

	$ git clone https://github.com/ICanBoogie/Config.git





## Documentation

The package is documented as part of the [ICanBoogie](http://icanboogie.org/) framework
[documentation](http://icanboogie.org/docs/). You can generate the documentation for the package
and its dependencies with the `make doc` command. The documentation is generated in the `docs`
directory. [ApiGen](http://apigen.org/) is required. You can later clean the directory with
the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all dependencies required to run the suite. You can later
clean the directory with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://img.shields.io/travis/ICanBoogie/Config.svg)](http://travis-ci.org/ICanBoogie/Config)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Config.svg)](https://coveralls.io/r/ICanBoogie/Config)





## License

**icanboogie/config** is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.





[icanboogie/event]: https://github.com/ICanBoogie/Event
[ICanBoogie]:       https://github.com/ICanBoogie

[Config]:     http://api.icanboogie.org/config/1.1/class-ICanBoogie.Config.html
[Storage]:    http://api.icanboogie.org/storage/2.0/class-ICanBoogie.Storage.Storage.html
