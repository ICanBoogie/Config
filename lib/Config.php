<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

use ArrayAccess;
use ICanBoogie\Config\Builder;
use ICanBoogie\Config\NoBuilderDefined;
use ICanBoogie\Storage\Storage;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Throwable;

use function array_keys;
use function array_merge;
use function file_exists;
use function is_a;
use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Provides low-level configurations.
 */
class Config implements ArrayAccess
{
    /**
     * An array of key/value where _key_ is a path to a config directory and _value_ is its weight.
     * The array is sorted according to the weight of the paths.
     *
     * @var array
     */
    private array $paths = [];

    /**
     * Built configurations.
     *
     * @var array<string, object>
     *     Where _key_ is a config identifier and _value_ a configuration.
     */
    private array $built = [];

    /**
     * @param array<string, int> $paths
     *     An array of key/value pairs where _key_ is the path to a config directory and
     *     _value_ is the weight of that path.
     * @param array<string, class-string<Builder>> $builders
     * @param Storage|null $cache A cache for configurations.
     */
    public function __construct(
        array $paths,
        private readonly array $builders = [],
        public ?Storage $cache = null
    ) {
        $this->add($paths);
    }

    /**
     * @inheritdoc
     *
     * @throws OffsetNotWritable in attempt to set a configuration.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new OffsetNotWritable([ $offset, $this ]);
    }

    /**
     * Checks if a config has been built.
     *
     * @param string $offset A config identifier.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->built[$offset]);
    }

    /**
     * @inheritdoc
     *
     * @throws OffsetNotWritable in attempt to unset an offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new OffsetNotWritable([ $offset, $this ]);
    }

    /**
     * Returns a configuration.
     *
     * @param string $offset A config identifier.
     *
     * @throws InvalidArgumentException in attempt to obtain an undefined config.
     */
    public function offsetGet(mixed $offset): mixed
    {
        if ($this->offsetExists($offset)) {
            return $this->built[$offset];
        }

        $builder_class = $this->builders[$offset]
            ?? throw new NoBuilderDefined($offset);

        $started_at = microtime(true);

        $config = $this->build($offset, $builder_class);

        ConfigProfiler::add($started_at, $offset, $builder_class);

        return $config;
    }

    private ?string $cache_key = null;

    /**
     * Build a cache key according to the current paths and the config name.
     */
    private function get_cache_key(string $name): string
    {
        $this->cache_key ??= substr(sha1(implode('|', array_keys($this->paths))), 0, 8);

        return $this->cache_key . '_' . $name;
    }

    /**
     * Revokes built configs and the cache key.
     *
     * The method is usually called after the config paths have been modified.
     */
    private function revoke(): void
    {
        $this->built = [];
        $this->cache_key = null;
    }

    /**
     * Adds a path or several paths to the config.
     *
     * Paths are sorted according to their weight. The order in which they were defined is
     * preserved for paths with the same weight.
     *
     * <pre>
     * <?php
     *
     * $config->add('/path/to/config', 10);
     * $config->add([
     *
     *     '/path1/to/config' => 10,
     *     '/path2/to/config' => 10,
     *     '/path2/to/config' => -10
     *
     * ]);
     * </pre>
     *
     * @param array<string, int>|string $path
     *     An array of key/value pairs where _key_ is the path to a config directory and
     *     _value_ is the weight of that path.
     * @param int $weight Weight of the path. The argument is discarded if `$path` is an array.
     *
     * @throws InvalidArgumentException if the path is empty.
     */
    public function add(array|string $path, int $weight = 0)
    {
        if (!$path) {
            throw new InvalidArgumentException('$path is empty.');
        }

        $paths = $this->paths;

        if (is_array($path)) {
            $paths = array_merge($paths, $path);
        } else {
            $paths[$path] = $weight;
        }

        stable_sort($paths);

        $this->paths = $paths;
        $this->revoke();
    }

    /**
     * Builds a configuration.
     *
     * @param string $name Name of the configuration to build.
     * @param class-string<Builder> $builder_class
     */
    public function build(string $name, string $builder_class): mixed
    {
        if (array_key_exists($name, $this->built)) {
            return $this->built[$name];
        }

        $cache = $this->cache;
        $cache_key = $this->get_cache_key($name);
        $config = $cache?->retrieve($cache_key);

        if ($config !== null) {
            return $this->built[$name] = $config;
        }

        $config = $this->build_for_real($name, $builder_class);

        $cache?->store($cache_key, $config);

        return $this->built[$name] = $config;
    }

    /**
     * @param string $name
     * @param class-string<Builder> $builder_class
     *
     * @return object
     */
    private function build_for_real(string $name, string $builder_class): object
    {
        if (!is_a($builder_class, Builder::class, true)) {
            throw new LogicException(
                "Invalid builder for configuration `$name`, builders must implement " . Builder::class
            );
        }

        $builder = $this->resolve_config_builder($builder_class);

        foreach ($this->path_iterator($builder_class::get_fragment_filename()) as $path) {
            try {
                (function (Builder $builder, string $__FRAGMENT_PATH__): void {
                    (require $__FRAGMENT_PATH__)($builder);
                })(
                    $builder,
                    $path
                );
            } catch (Throwable $e) {
                throw new RuntimeException("Configuration failed with $path", previous: $e);
            }
        }

        return $builder->build();
    }

    /**
     * @param class-string<Builder> $configurator
     */
    private function resolve_config_builder(string $configurator): Builder
    {
        return new $configurator();
    }

    /**
     * @param string $name Name of the configuration.
     *
     * @return iterable<string>
     */
    private function path_iterator(string $name): iterable
    {
        $filename = $name . '.php';

        foreach (array_keys($this->paths) as $path) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $pathname = $path . $filename;

            if (!file_exists($pathname)) {
                continue;
            }

            yield $pathname;
        }
    }
}
