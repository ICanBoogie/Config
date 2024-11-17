# Config

[![Release](https://img.shields.io/packagist/v/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)
[![Code Coverage](https://coveralls.io/repos/github/ICanBoogie/Config/badge.svg?branch=6.0)](https://coveralls.io/r/ICanBoogie/Config?branch=6.0)
[![Downloads](https://img.shields.io/packagist/dm/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)

An API to build low-level configuration.

This package is used by the framework [ICanBoogie][] to configure its components.

Configurations are defined by a set of files called _fragments_. Builders use these fragments to
build configurations. Configurations are managed by a [Config][] instance. Configurations can be
cached, which cancels the cost of the builds.



#### Installation

```shell
composer require icanboogie/config
```





## Configuration fragments

A configuration fragment is a PHP file returning a callable. Multiple fragments are used to build a
configuration. They're usually located in "config" directories and are usually named after the
config they are used to build.





## Configuration provider

Configurations are read through a [ConfigProvider][].

The following example demonstrates how to get a configuration of class `MyConfig`:

```php
<?php

/* @var \ICanBoogie\ConfigProvider\BasicConfigProvider $config */

$my_config = $config->config_for_class(MyConfig::class);
```

A [NoBuilderDefined][] exception is thrown if there is no builder defined for a configuration class.




## Configuration builders

Builders are defined for each configuration.

```php
<?php

use ICanBoogie\ConfigProvider\BasicConfigProvider;

/* @var string[] $paths */

$builders = [ MyConfig::class => MyBuilder::class ];
$config = new BasicConfigProvider($paths, builders);
$my_config = $config->config_for_class(MyConfig::class);
```





## Caching configurations

Caching configurations removes the cost of building by reusing the result of a previous build. To
enable caching, you just need to provide a cache implementing [Storage][].

```php
<?php

namespace ICanBoogie;

use ICanBoogie\ConfigProvider\BasicConfigProvider;

$config = new BasicConfigProvider($paths, $builders, $cache);
```



----------



## Continuous Integration

The project is continuously tested by [GitHub actions](https://github.com/ICanBoogie/Config/actions).

[![Tests](https://github.com/ICanBoogie/Config/actions/workflows/test.yml/badge.svg?branch=6.0)](https://github.com/ICanBoogie/Config/actions/workflows/test.yml)
[![Static Analysis](https://github.com/ICanBoogie/Config/actions/workflows/static-analysis.yml/badge.svg?branch=6.0)](https://github.com/ICanBoogie/Config/actions/workflows/static-analysis.yml)
[![Code Style](https://github.com/ICanBoogie/Config/actions/workflows/code-style.yml/badge.svg?branch=6.0)](https://github.com/ICanBoogie/Config/actions/workflows/code-style.yml)



## Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in
this project and its community, you're expected to uphold this code.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.



[ICanBoogie]:           https://icanboogie.org/
[icanboogie/event]:     https://github.com/ICanBoogie/Event

[ConfigProvider]:       lib/ConfigProvider.php
[NoBuilderDefined]:     lib/Config/NoBuilderDefined.php
[Storage]:              https://icanboogie.org/api/storage/2.0/class-ICanBoogie.Storage.Storage.html
