# Config

The `icanboogie/config` package provides an API to build low-level configurations. It is used by
the [ICanBoogie][] framework to configure its components.

Configurations are defined by a set of files called _fragments_. _Builders_ use these fragments to
build configuration objects, which are managed by a _configuration provider_. Configurations can be
cached, which cancels the cost of the builds.

- [Configuration fragments](ConfigFragments.md)
- [Configuration builders](ConfigBuilders.md)
- [Configuration providers](ConfigProviders.md)
- [Caching configurations](Caching.md)
- [Profiling](Profiling.md)

## Installation

```shell
composer require icanboogie/config
```

## Quick overview

A configuration is a plain PHP object built from one or more fragments. The following example
declares a `MyConfig` configuration and its `MyBuilder` builder, and retrieves the resulting
configuration through a [BasicConfigProvider][] instance:

```php
<?php

use ICanBoogie\ConfigProvider\BasicConfigProvider;

/* @var string[] $paths */

$configs = new BasicConfigProvider($paths, [
    MyConfig::class => MyBuilder::class,
]);

$my_config = $configs->config_for_class(MyConfig::class);
```

A [NoBuilderDefined][] exception is thrown if there is no builder defined for a configuration class.

[ICanBoogie]:           https://icanboogie.org/
[BasicConfigProvider]:  ConfigProviders.md
[NoBuilderDefined]:     ConfigBuilders.md
