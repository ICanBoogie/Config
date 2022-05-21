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

/**
 * Collects timing information about configuration builders.
 */
final class ConfigProfiler
{
    /**
     * @var array{ 0: float, 1: float, 2: string, 3: string }
     */
    public static array $entries;

    /**
     * @template T of object
     *
     * @param float $started_at When the configuration started to build.
     * @param class-string<T> $config_class
     * @param class-string<Builder<T>> $builder_class
     */
    public static function add(float $started_at, string $config_class, string $builder_class): void
    {
        self::$entries[] = [ $started_at, microtime(true), $config_class, $builder_class ];
    }
}
