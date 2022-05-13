# Config

[![Packagist](https://img.shields.io/packagist/v/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Config.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Config)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Config.svg)](https://coveralls.io/r/ICanBoogie/Config)
[![Downloads](https://img.shields.io/packagist/dt/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)

An API to synthesize low-level configuration.

This package is used by the framework [ICanBoogie][] to configure its components.

Configurations are defined by a set of files that are called _fragments_. These fragments are
used to synthesize the configuration of one or more components. When the same fragments
are used to synthesize different configurations, these configurations are qualified
as _derived_. Fragments are synthesized using callback functions called _synthesizers_.
Configurations are managed by a [Config][] instance. Finally, synthesized configurations
can be cached, which cancel the cost of the synthesis.



#### Installation

```bash
composer require icanboogie/config
```





## Configuration fragments

A configuration fragment is a PHP file returning an array. Multiple fragments are used to
synthesize a configuration, or _derived_ configuration. They are usually located in "config"
directories and are usually named after the config they are used to synthesize.





## The configuration

The configuration is represented by a [Config][] instance, which is used as an array to access
specific configurations.

The following example demonstrates how to obtain the configuration `routes`:

```php
<?php

/* @var \ICanBoogie\Config $config */

$routing_config = $config['routes'];
```

A [NoSynthesizerDefined][] exception is thrown if there is no synthesizer defined for a
configuration. A [NoFragmentDefined][] exception is thrown if there is no fragment defined for a
configuration.





## Synthesizing a configuration

The `synthesize()` method of the [Config][] instance is used to synthesize a configuration.

The following example demonstrates how a closure can be used to synthesize multiple fragments:

```php
<?php

$config = $config->synthesize('core', function(array $fragments) {

	return call_user_func_array('ICanBoogie\array_merge_recursive', $fragments);

});
```

The exception [NoFragmentDefined][] is thrown when no fragment of a specified type is defined.





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
[NoSynthesizerDefined]: https://icanboogie.org/api/config/1.2/class-ICanBoogie.Config.NoSynhtesizerDefined.html
[Storage]:              https://icanboogie.org/api/storage/2.0/class-ICanBoogie.Storage.Storage.html
