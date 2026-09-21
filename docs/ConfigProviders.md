# Configuration providers

Configurations are read through a [ConfigProvider][] instance. The interface defines a single
method that returns a configuration of a given class:

```php
<?php

namespace ICanBoogie;

interface ConfigProvider
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function config_for_class(string $class): object;
}
```

## `BasicConfigProvider`

[BasicConfigProvider][] is the standard implementation. Its constructor accepts two arguments:

- `array $paths` — the list of configuration directories, in the order the fragments should be
  required.
- `array $builders` — a map of configuration classes to builder classes.

```php
<?php

use ICanBoogie\ConfigProvider\BasicConfigProvider;

/* @var string[] $paths */

$builders = [
    MyConfig::class => MyBuilder::class,
];

$configs = new BasicConfigProvider($paths, $builders);
```

A configuration is then obtained with `config_for_class()`:

```php
<?php

/* @var \ICanBoogie\ConfigProvider\BasicConfigProvider $configs */

$my_config = $configs->config_for_class(MyConfig::class);
```

The first call builds the configuration; subsequent calls return the same instance, so a
configuration is only ever built once:

```php
<?php

$first = $configs->config_for_class(MyConfig::class);
$second = $configs->config_for_class(MyConfig::class);

var_dump($first === $second); // true
```

## `CachedConfigProvider`

[CachedConfigProvider][] wraps a `BasicConfigProvider` to cache configurations, cancelling the cost
of the builds. It accepts the provider to decorate, a cache implementing [Storage][], and the
configuration paths used to compute cache keys. See [Caching configurations](Caching.md).

```php
<?php

use ICanBoogie\ConfigProvider\BasicConfigProvider;
use ICanBoogie\ConfigProvider\CachedConfigProvider;

/* @var string[] $paths */
/* @var \ICanBoogie\Storage\Storage $cache */

$configs = new CachedConfigProvider(
    new BasicConfigProvider($paths, $builders),
    $cache,
    $paths,
);
```

## Exceptions

A [NoBuilderDefined][] exception is thrown when there is no builder defined for a configuration
class:

```php
<?php

use ICanBoogie\ConfigProvider\BasicConfigProvider;

$configs = new BasicConfigProvider($paths, []);

$configs->config_for_class(ArrayAccess::class); // NoBuilderDefined
```

The `NoBuilderDefined` exception implements the [Exception][] marker interface, so that the
exceptions of the package can be easily recognized:

```php
<?php

use ICanBoogie\Config\Exception;
use ICanBoogie\Config\NoBuilderDefined;

try {
    $configs->config_for_class(MyConfig::class);
} catch (Exception $e) {
    // NoBuilderDefined
}
```

## A real-world example

The [ICanBoogie][] framework implements the `ConfigProvider` interface in its `Application` class
and delegates to a `BasicConfigProvider`. Configurations are available through the application:

```php
<?php

use ICanBoogie\Application;
use ICanBoogie\AppConfig;

$app = Application::get();

$app_config = $app->config_for_class(AppConfig::class);
```

[ConfigProvider]:       ../lib/ConfigProvider.php
[BasicConfigProvider]:  ../lib/ConfigProvider/BasicConfigProvider.php
[CachedConfigProvider]: ../lib/ConfigProvider/CachedConfigProvider.php
[NoBuilderDefined]:     ../lib/Config/NoBuilderDefined.php
[Exception]:            ../lib/Config/Exception.php
[Storage]:              https://icanboogie.org/api/storage/2.0/class-ICanBoogie.Storage.Storage.html
[ICanBoogie]:           https://icanboogie.org/
