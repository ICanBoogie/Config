# CHANGELOG

## v7.0

### New Requirements

PHP 8.4+

### New features

None

### Deprecated Features

None

### Backward Incompatible Changes

None

### Other changes

None



## v6.0

### New requirements

Requires PHP 8.2+

### New features

- Config builders replace config synthesizers. Config builders implements the `Builder` interface
  and return an object. The filename of the configuration fragments is provided by the builder.

- `Config::synthesize()` has been renamed as `Config::build()`.

- Add `ConfigProvider` interface.

### Backward Incompatible Changes

- The `Config` class no longer implements `ArrayAccess`, it implements `ConfigProvider` instead.

    ```php
    <?php

    /* @var \ICanBoogie\ConfigProvider\Config $configs */

    $app_config = $configs['app'];
    ```
    ```php
    <?php

    /* @var \ICanBoogie\ConfigProvider\Config $configs */

    $app_config = $configs->config_for_class(AppConfig::class);
    ```

- Configuration paths must be defined no longer have a weight, the _value_ is the path. Hence, the
  paths must be provided in the desired order.

### Deprecated Features

- Support for config synthesizers has been removed. Only Config builders are supported now.

- `NoFragmentDefined` has been removed.

- The concept of "derived config" (building different config from the same fragment) has been dropped.

- Removed `Config::add()`, the all configuration paths must be provided during construct.

### Other Changes

N/A
