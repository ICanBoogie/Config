# Migration

## v5.x to v6.x

### New features

- Config builders replace config synthesizers. Config builders implements the `Builder` interface
  and return an object.
- `Config::synthesize()` has been renamed as `Config::build()`.

### Backward Incompatible Changes

N/A

### Deprecated Features

- Support for config synthesizers have been removed. Only Config builders are supported now.
- `NoFragmentDefined` has been removed.
- The concept of "derived config" (building different config from same fragments) has been dropped.

### Other Changes

N/A
