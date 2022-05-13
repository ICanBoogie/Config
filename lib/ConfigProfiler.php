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

/**
 * Collects timing information about configuration builders.
 */
final class ConfigProfiler
{
    public static array $entries;

    /**
     * @param float $started_at Start micro time.
     * @param string $name Fragment name
     */
    public static function add(float $started_at, string $name, string $builder_class)
    {
        self::$entries[] = [ $started_at, microtime(true), $name, $builder_class ];
    }
}
