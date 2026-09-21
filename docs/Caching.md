# Caching configurations

Caching configurations removes the cost of building by reusing the result of a previous build. To
enable caching, wrap a [BasicConfigProvider][] in a [CachedConfigProvider][] that is given a cache
implementing [Storage][]:

```php
<?php

use ICanBoogie\ConfigProvider\BasicConfigProvider;
use ICanBoogie\ConfigProvider\CachedConfigProvider;

/* @var string[] $paths */
/* @var array<class-string, class-string> $builders */
/* @var \ICanBoogie\Storage\Storage $cache */

$configs = new CachedConfigProvider(
    new BasicConfigProvider($paths, $builders),
    $cache,
    $paths,
);
```

When a cache is provided, the configuration provider:

1. computes a cache key for the configuration class,
2. retrieves the cached configuration; if found, it is returned directly,
3. otherwise, builds the configuration, stores it, and returns it.

## Cache keys

The cache key is derived from the configuration paths and the configuration class, so a change to
the paths or to a fragment invalidates the cache. The key has the following shape:

```
<paths-hash>_<lowercased-config-class>
```

For example, for the `App\MyConfig` configuration, the key looks like
`63a864bd_app\myconfig`.

## A real-world example

The [ICanBoogie][] framework enables configuration caching through the `AppConfig` configuration.
The `app/all/config/app.php` fragment enables it, while `app/dev/config/app.php` disables it for
development:

```php
<?php
// app/all/config/app.php

use ICanBoogie\AppConfigBuilder;

return fn(AppConfigBuilder $config) => $config
    ->enable_config_caching();
```

```php
<?php
// app/dev/config/app.php

use ICanBoogie\AppConfigBuilder;

return fn(AppConfigBuilder $config) => $config
    ->disable_config_caching();
```

[Storage]:              https://icanboogie.org/api/storage/2.0/class-ICanBoogie.Storage.Storage.html
[ICanBoogie]:           https://icanboogie.org/
[BasicConfigProvider]:  ConfigProviders.md
[CachedConfigProvider]: ConfigProviders.md
