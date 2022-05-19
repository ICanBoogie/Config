# Migration

## v5.x to v6.x

### New features

- Config builders replace config synthesizers. Config builders implements the `Builder` interface
  and return an object. The filename of the configuration fragments is provided by the builder.

- `Config::synthesize()` has been renamed as `Config::build()`.

- Add `ConfigProvider` interface.

### Backward Incompatible Changes

- The `Config` class no longer implements `ArrayAccess`, it implements `ConfigProvider` instead.

    ```php
    <?php

    /* @var ICanBoogie\Config $configs */

    $app_config = $configs['app'];
    ```
    ```php
    <?php

    /* @var ICanBoogie\Config $configs */

    $app_config = $configs->config_for_class(AppConfig::class);
    ```

### Deprecated Features

- Support for config synthesizers have been removed. Only Config builders are supported now.

- `NoFragmentDefined` has been removed.

- The concept of "derived config" (building different config from same fragments) has been dropped.

### Other Changes

N/A
