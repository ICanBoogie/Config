<?php

namespace ICanBoogie;

use ICanBoogie\Config\Builder;
use ICanBoogie\ConfigProfiler\Record;

/**
 * Collects timing information about configuration builders.
 */
final class ConfigProfiler
{
    /**
     * @var Record[]
     */
    public static array $records;

    /**
     * @template T of object
     *
     * @param float $started_at When the configuration started to build.
     * @param class-string<T> $config_class
     * @param class-string<Builder<T>> $builder_class
     */
    public static function add(float $started_at, string $config_class, string $builder_class): void
    {
        self::$records[] = new Record($started_at, microtime(true) - $started_at, $config_class, $builder_class);
    }
}
