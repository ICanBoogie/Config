<?php

namespace ICanBoogie\ConfigProvider;

use ICanBoogie\ConfigProvider;
use ICanBoogie\Storage\Storage;

use function array_map;
use function implode;
use function rtrim;
use function sha1;
use function str_replace;
use function strtolower;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * Decorates a configuration provider with caching capabilities.
 */
final class CachedConfigProvider implements ConfigProvider
{
    /**
     * @var string[]
     *     Where _value_ is a path to a config directory.
     */
    private readonly array $paths;

    /**
     * @param BasicConfigProvider $provider The provider to decorate.
     * @param Storage $cache A cache for configurations.
     * @param string[] $paths
     *     Where _value_ is a path to a config directory.
     */
    public function __construct(
        private readonly BasicConfigProvider $provider,
        private readonly Storage $cache,
        array $paths,
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
        $cache_key = $this->get_cache_key($class);
        $config = $this->cache->retrieve($cache_key);

        if ($config !== null) {
            return $config; // @phpstan-ignore-line
        }

        $config = $this->provider->config_for_class($class);
        $this->cache->store($cache_key, $config);

        return $config;
    }

    private ?string $cache_key_base = null;

    /**
     * Build a cache key according to the current paths and the config class.
     *
     * @param class-string $config_class
     */
    private function get_cache_key(string $config_class): string
    {
        $this->cache_key_base ??= substr(sha1(implode('|', $this->paths)), 0, 8);

        return $this->cache_key_base . '_' . str_replace('\\', '_', strtolower($config_class));
    }
}
