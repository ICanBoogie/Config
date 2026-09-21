# Profiling

The [ConfigProfiler][] class collects timing information about configuration builds. Every time a
configuration is built through a [BasicConfigProvider][], a [Record][] is added to
`ConfigProfiler::$records`:

```php
<?php

use ICanBoogie\ConfigProfiler;

foreach (ConfigProfiler::$records as $record) {
    echo $record->config_class;  // The class of the configuration.
    echo $record->builder_class; // The class of the builder.
    echo $record->duration;      // The duration of the build, in seconds.
    echo $record->timestamp;     // When the build started, as a Unix timestamp
}
```

A record exposes the following properties:

- `timestamp` — when the configuration started to build.
- `duration` — the duration of the build, in seconds.
- `config_class` — the class of the configuration.
- `builder_class` — the class of the builder.

The profiler is a lightweight tool to spot expensive configurations during development.

[ConfigProfiler]:      ../lib/ConfigProfiler.php
[Record]:              ../lib/ConfigProfiler/Record.php
[BasicConfigProvider]: ConfigProviders.md
