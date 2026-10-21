# Environment variables

The `Env` enum provides typed access to environment variables. It is meant to be used by
[configuration fragments](ConfigFragments.md), to adapt a configuration to its environment without
maintaining a set of fragments per profile.

```php
<?php

use ICanBoogie\Env;

return [

    'cache' => Env::bool('APP_CACHE', false),
    'dsn' => Env::string('DATABASE_DSN'),
    'sentry_dsn' => Env::nullable_string('SENTRY_DSN'),

];
```

> **Warning**
> Read the environment from configuration only. Past the configuration, components should rely on
> the values they were configured with. Also remember that cached configurations are built once:
> the values read from the environment are frozen in them, until the cache is cleared.

## Reading values

There is one method per type: `string()`, `bool()`, `int()`, and `float()`. Each takes the name of
the variable and a default value, and returns a value of its type.

Variables are looked up in `$_SERVER`, then with `getenv()`.

```php
<?php

use ICanBoogie\Env;

$workers = Env::int('WORKERS', 4);
$ratio = Env::float('SAMPLING_RATIO', 0.1);
$debug = Env::bool('APP_DEBUG', false);
```

When the default value is omitted, or `Env::REQUIRED`, the variable is required and
`ICanBoogie\Env\NotDefined` is thrown if it is not defined:

```php
<?php

use ICanBoogie\Env;

$dsn = Env::string('DATABASE_DSN');
$dsn = Env::string('DATABASE_DSN', Env::REQUIRED); // same
```

Use the `nullable_*` variants to obtain `null` when the variable is not defined. They don't take a
default value.

```php
<?php

use ICanBoogie\Env;

$workers = Env::nullable_int('WORKERS');
```

## Conversions

An empty value, `APP_CACHE=`, is considered undefined by `bool()`, `int()`, `float()`, and their
`nullable_*` variants. It is a valid value for `string()` and `nullable_string()`.

Booleans accept `1`, `true`, `on`, and `yes`, or `0`, `false`, `off`, and `no`, in any case.

A value that cannot be converted throws `ICanBoogie\Env\InvalidValue`, rather than silently
becoming `false` or `0`. The value is not part of the message, environment variables often hold
secrets.
