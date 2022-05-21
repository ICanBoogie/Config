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

use ICanBoogie\Config\Builder;
use ICanBoogie\Config\NoBuilderDefined;
use ICanBoogie\Storage\Storage;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

use function array_map;
use function file_exists;
use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Provides low-level configurations.
 */
final class Config implements ConfigProvider
{
    /**
     * @var string[]
     *     Where _value_ is a path to a config directory.
     */
    private readonly array $paths;

    /**
     * Built configurations.
     *
     * @var array<class-string, object>
     *     Where _key_ is a config class and _value_ an instance of that class.
     */
    private array $built = [];

    /**
     * @param string[] $paths
     *     Where _value_ is a path to a config directory.
     *
     * @param array<class-string, class-string<Builder<object>>> $builders
     *     Where _key_ is a config class and _value_ a builder class.
     *
     * @param Storage|null $cache
     *     A cache for configurations.
     */
    public function __construct(
        array $paths,
        private readonly array $builders,
        public ?Storage $cache = null
    ) {
        $this->paths = array_map(
            fn(string $path) => rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            $paths
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function config_for_class(string $class): object
    {
        return $this->built[$class] ??= $this->make_config($class); // @phpstan-ignore-line
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class A config class.
     *
     * @throws NoBuilderDefined in attempt to obtain an undefined config.
     *
     * @return T
     */
    private function make_config(string $class): object
    {
        $builder_class = $this->builders[$class]
            ?? throw new NoBuilderDefined($class);

        $started_at = microtime(true);

        $config = $this->build($class, $builder_class);

        ConfigProfiler::add($started_at, $class, $builder_class);

        return $config; // @phpstan-ignore-line
    }

    private ?string $cache_key = null;

    /**
     * Build a cache key according to the current paths and the config name.
     *
     * @param class-string $config_class
     */
    private function get_cache_key(string $config_class): string
    {
        $this->cache_key ??= substr(sha1(implode('|', $this->paths)), 0, 8);

        return $this->cache_key . '_' . $config_class;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $config_class
     * @param class-string<Builder<T>> $builder_class
     *
     * @return T
     *
     * @throws InvalidArgumentException in attempt to obtain an undefined config.
     */
    private function build(string $config_class, string $builder_class): object
    {
        if (array_key_exists($config_class, $this->built)) {
            return $this->built[$config_class]; // @phpstan-ignore-line
        }

        $cache = $this->cache;
        $cache_key = $this->get_cache_key($config_class);
        $config = $cache?->retrieve($cache_key);

        if ($config !== null) {
            return $this->built[$config_class] = $config; // @phpstan-ignore-line
        }

        $config = $this->build_for_real($builder_class);

        $cache?->store($cache_key, $config);

        return $this->built[$config_class] = $config;
    }

    /**
     * @template T of object
     *
     * @param class-string<Builder<T>> $builder_class
     *
     * @return T
     */
    private function build_for_real(string $builder_class): object
    {
        $builder = new $builder_class();

        assert($builder instanceof Builder);

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
     * @return iterable<string>
     */
    private function path_iterator(string $filename): iterable
    {
        foreach ($this->paths as $path) {
            $pathname = $path . $filename . '.php';

            if (!file_exists($pathname)) {
                continue;
            }

            yield $pathname;
        }
    }
}
