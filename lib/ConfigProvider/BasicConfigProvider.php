<?php

namespace ICanBoogie\ConfigProvider;

use ICanBoogie\Config\Builder;
use ICanBoogie\Config\NoBuilderDefined;
use ICanBoogie\ConfigProfiler;
use ICanBoogie\ConfigProvider;
use RuntimeException;
use Throwable;

use function array_map;
use function file_exists;
use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Provides low-level configurations.
 */
final class BasicConfigProvider implements ConfigProvider
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
     */
    public function __construct(
        array $paths,
        private readonly array $builders,
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
     * @return T
     *
     * @throws NoBuilderDefined in an attempt to make an undefined config.
     */
    private function make_config(string $class): object
    {
        $builder_class = $this->builders[$class]
            ?? throw new NoBuilderDefined($class);

        $started_at = microtime(true);

        $config = $this->make_config_with_builder($builder_class);

        ConfigProfiler::add($started_at, $class, $builder_class);

        return $config; // @phpstan-ignore-line
    }

    /**
     * @template T of object
     *
     * @param class-string<Builder<T>> $builder_class
     *
     * @return T
     */
    private function make_config_with_builder(string $builder_class): object
    {
        $builder = new $builder_class();

        foreach ($this->path_iterator($builder_class::get_fragment_filename()) as $path) {
            try {
                (function (Builder $builder, string $__FRAGMENT_PATH__): void {
                    $configurer = (require $__FRAGMENT_PATH__);

                    assert(is_callable($configurer));

                    $configurer($builder);
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

            if (file_exists($pathname)) {
                yield $pathname;
            }
        }
    }
}
