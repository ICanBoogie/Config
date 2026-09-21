# Configuration builders

A configuration builder is a class that implements the [Builder][] interface. Builders use
configuration fragments to build a configuration object.

## The `Builder` interface

The interface defines two methods:

- `get_fragment_filename(): string` returns the filename of the configuration fragments used by the
  builder. The filename does not include the `.php` extension.
- `build(): object` builds and returns the configuration object.

```php
<?php

namespace ICanBoogie\Config;

interface Builder
{
    public static function get_fragment_filename(): string;

    public function build(): object;
}
```

## Declaring a builder

Builders are declared for each configuration. The following example declares a `MyBuilder` builder
for the `MyConfig` configuration:

```php
<?php

use ICanBoogie\ConfigProvider\BasicConfigProvider;

/* @var string[] $paths */

$builders = [
    MyConfig::class => MyBuilder::class,
];

$configs = new BasicConfigProvider($paths, $builders);
$my_config = $configs->config_for_class(MyConfig::class);
```

The [NoBuilderDefined][] exception is thrown if no builder is defined for a configuration class.

## Implementing a builder

A builder typically exposes one mutating method per setting. Fragments call these methods to
configure the builder, and `build()` assembles the final configuration object:

```php
<?php

use ICanBoogie\Config\Builder;

final class MyBuilder implements Builder
{
    public static function get_fragment_filename(): string
    {
        return "builder";
    }

    /** @var string[] */
    private array $strings = [];

    /** @var int[] */
    private array $integers = [];

    public bool $bool = false;

    public function add_string(string $string): self
    {
        $this->strings[] = $string;

        return $this;
    }

    public function add_int(int $int): self
    {
        $this->integers[] = $int;

        return $this;
    }

    public function build(): MyConfig
    {
        return new MyConfig($this->strings, $this->integers, $this->bool);
    }
}
```

The builder's methods are usually fluent, returning `$this`, so that fragments can chain calls:

```php
<?php

use MyBuilder;

return fn(MyBuilder $builder) => $builder
    ->add_string("two")
    ->add_int(2);
```

## A real-world example

The [ICanBoogie][] framework declares a builder for each of its components. For example,
[AppConfigBuilder][] builds the `AppConfig` object that configures the application, and its
`get_fragment_filename()` returns `app`. The matching fragments are the `app.php` files found in
the configuration paths:

```php
<?php

namespace ICanBoogie;

use ICanBoogie\Config\Builder;

final class AppConfigBuilder implements Builder
{
    public static function get_fragment_filename(): string
    {
        return 'app';
    }

    private bool $cache_configs = false;

    public function enable_config_caching(): self
    {
        $this->cache_configs = true;

        return $this;
    }

    public function build(): AppConfig
    {
        return new AppConfig(cache_configs: $this->cache_configs);
    }
}
```

[Builder]:           ../lib/Config/Builder.php
[NoBuilderDefined]:  ../lib/Config/NoBuilderDefined.php
[AppConfigBuilder]:  https://github.com/ICanBoogie/ICanBoogie
[ICanBoogie]:        https://icanboogie.org/
