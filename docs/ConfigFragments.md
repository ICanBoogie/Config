# Configuration fragments

A configuration fragment is a PHP file returning a callable. Multiple fragments are used to build a
configuration. They are usually located in `config` directories and are usually named after the
configuration they are used to build.

## Anatomy of a fragment

A fragment returns a callable that receives the configuration _builder_ as its argument and
configures it:

```php
<?php

use MyBuilder;

return function (MyBuilder $builder) {
    $builder->add_string("two");
    $builder->add_int(2);
};
```

The callable may be an arrow function, which reads nicely when the builder's methods are fluent:

```php
<?php

use MyBuilder;

return fn(MyBuilder $builder) => $builder
    ->add_string("two")
    ->add_int(2);
```

The return value of the callable is ignored: the fragment configures the builder, and the builder
builds the configuration.

## Naming

A fragment is matched to its builder by its filename: the filename returned by
`Builder::get_fragment_filename()`. For a builder whose `get_fragment_filename()` returns `builder`,
the fragment must be named `builder.php`:

```
config/
├── builder.php
└── other.php
```

The filename must not include the `.php` extension; it is appended by the configuration provider.

## Multiple fragments and ordering

Several fragments, each located in a distinct configuration directory, may contribute to the same
configuration. The fragments are required in the order of the paths they are found in, and later
fragments may override the settings of earlier ones.

For example, given the following paths, in order:

```php
$paths = [
    'app/all/config',
    'app/dev/config',
];
```

…a fragment in `app/dev/config/builder.php` is applied after the one in
`app/all/config/builder.php`, and may adjust its settings.

## A real-world example

The [Octopath 2][octopath2] application stores its fragments in the `app/all/config` and
`app/dev/config` directories. Each fragment configures a component of the framework:

```php
<?php
// app/all/config/activerecord.php

namespace App;

use App\Domain\Person;
use ICanBoogie\Binding\ActiveRecord\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->use_attributes()
    ->add_connection('primary', 'sqlite:var/db.sqlite')
    ->add_record(Person::class);
```

The `app/all/config` directory holds the fragments shared by every environment, while
`app/dev/config` holds fragments specific to the development environment. For example,
`app/all/config/app.php` enables configuration caching, and `app/dev/config/app.php` disables it
again for development:

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

[octopath2]: https://github.com/olvlvl/com.olvlvl.octopath2
