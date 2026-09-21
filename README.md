# Config

[![Release](https://img.shields.io/packagist/v/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)
[![Code Coverage](https://coveralls.io/repos/github/ICanBoogie/Config/badge.svg?branch=6.0)](https://coveralls.io/r/ICanBoogie/Config?branch=6.0)
[![Downloads](https://img.shields.io/packagist/dm/icanboogie/config.svg)](https://packagist.org/packages/icanboogie/config)

The `icanboogie/config` package provides an API to build low-level configurations. It is used by
the [ICanBoogie][] framework to configure its components.

[See documentation](docs/README.md)



#### Usage

```<?php
/* @var \ICanBoogie\ConfigProvider\BasicConfigProvider $config */

$my_config = $config->config_for_class(MyConfig::class);
```



#### Installation

```shell
composer require icanboogie/config
```



----------



## Continuous Integration

The project is continuously tested by [GitHub Actions](https://github.com/ICanBoogie/Config/actions).

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
