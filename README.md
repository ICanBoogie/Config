# Config

[![Packagist](https://img.shields.io/packagist/v/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Config.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Config)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Config.svg)](https://coveralls.io/r/ICanBoogie/Config)
[![Downloads](https://img.shields.io/packagist/dt/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)

An API to build low-level configuration.

This package is used by the framework [ICanBoogie][] to configure its components.

Configurations are defined by a set of files that are called _fragments_. These fragments are used
to build the configuration of one or more components. Configurations are managed by a [Config][]
instance. Configurations can be cached, which cancels the cost of the builds.



#### Installation

```bash
composer require icanboogie/config
```





## Configuration fragments

A configuration fragment is a PHP file returning a callable. Multiple fragments are used to build a
configuration. They are usually located in "config" directories and are usually named after the
config they are used to build.





## The configuration

The configuration is represented by a [Config][] instance, which is used as an array to access
specific configurations.

The following example demonstrates how to obtain the configuration `routes`:

```php
<?php

/* @var \ICanBoogie\Config $config */

$routing_config = $config['routes'];
```

A [NoBuilderDefined][] exception is thrown if there is no builder defined for a configuration.




## Building a configuration

The `build()` method of the [Config][] instance is used to build a configuration.

The following example demonstrates how a builder can be used to build multiple fragments. The
builder must implement the `Builder` interface.

```php
<?php

$config = $config->build('myconfig', MyBuilder::class);
```





## Configuration builders

Builders can be defined for each configuration, they are used when the config collection is used as
an array:

```php
<?php

$builders = [ 'myconfig' => MyBuilder::class ];
$config = new Config($paths, builders);
$my_config = $configs['myconfig'];
```





## Caching configurations

Caching configurations removes the cost of building by reusing the result of a previous build. To
enable caching, you just need to provide a cache implementing [Storage][].

```php
<?php

$config = new Config($paths, $builders, $cache);
```





----------



## Continuous Integration

The project is continuously tested by [GitHub actions](https://github.com/ICanBoogie/Config/actions).

[![Tests](https://github.com/ICanBoogie/Config/workflows/test/badge.svg?branch=master)](https://github.com/ICanBoogie/Config/actions?query=workflow%3Atest)
[![Static Analysis](https://github.com/ICanBoogie/Config/workflows/static-analysis/badge.svg?branch=master)](https://github.com/ICanBoogie/Config/actions?query=workflow%3Astatic-analysis)
[![Code Style](https://github.com/ICanBoogie/Config/workflows/code-style/badge.svg?branch=master)](https://github.com/ICanBoogie/Config/actions?query=workflow%3Acode-style)



## Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in
this project and its community, you are expected to uphold this code.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.



## License

**icanboogie/config** is released under the [BSD-3-Clause](LICENSE).



[ICanBoogie]: https://icanboogie.org/
[icanboogie/event]: https://github.com/ICanBoogie/Event

[Config]:               https://icanboogie.org/api/config/1.2/class-ICanBoogie.Config.html
[NoFragmentDefined]:    https://icanboogie.org/api/config/1.2/class-ICanBoogie.Config.NoFragmentDefined.html
[NoBuilderDefined]: https://icanboogie.org/api/config/1.2/class-ICanBoogie.Config.NoSynhtesizerDefined.html
[Storage]:              https://icanboogie.org/api/storage/2.0/class-ICanBoogie.Storage.Storage.html
