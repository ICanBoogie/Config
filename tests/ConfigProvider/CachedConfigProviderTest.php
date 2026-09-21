<?php

namespace Test\ICanBoogie\ConfigProvider;

use ICanBoogie\ConfigProvider\BasicConfigProvider;
use ICanBoogie\ConfigProvider\CachedConfigProvider;
use ICanBoogie\Storage\RunTimeStorage;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Builder\SampleBuilder;
use Test\ICanBoogie\SampleConfig;

use function array_map;
use function implode;
use function rtrim;
use function sha1;
use function str_replace;
use function strtolower;
use function substr;

use const DIRECTORY_SEPARATOR;

final class CachedConfigProviderTest extends TestCase
{
    private const array PATHS = [

        __DIR__ . '/fixtures/config01',
        __DIR__ . '/fixtures/config02',
        __DIR__ . '/fixtures/config03',

    ];

    private const array BUILDERS = [

        SampleConfig::class => SampleBuilder::class,

    ];

    public function test_config_is_built_and_cached(): void
    {
        $cache = new RunTimeStorage();
        $configs = new CachedConfigProvider(
            new BasicConfigProvider(self::PATHS, self::BUILDERS),
            $cache,
            self::PATHS,
        );

        $config = $configs->config_for_class(SampleConfig::class);

        $this->assertInstanceOf(SampleConfig::class, $config);
        $this->assertTrue($cache->exists(self::cache_key(SampleConfig::class)));
    }

    public function test_cached_config_is_reused(): void
    {
        $cache = new RunTimeStorage();
        $expected = new SampleConfig([ "cached" ], [], false);
        $cache->store(self::cache_key(SampleConfig::class), $expected);

        $configs = new CachedConfigProvider(
            new BasicConfigProvider(self::PATHS, self::BUILDERS),
            $cache,
            self::PATHS,
        );

        $this->assertSame($expected, $configs->config_for_class(SampleConfig::class));
    }

    /**
     * @param class-string $class
     */
    private static function cache_key(string $class): string
    {
        $paths = array_map(
            fn(string $path) => rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            self::PATHS
        );
        $base = substr(sha1(implode('|', $paths)), 0, 8);

        return $base . '_' . str_replace('\\', '_', strtolower($class));
    }
}
