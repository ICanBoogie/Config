<?php

namespace ICanBoogie\ConfigProfiler;

use ICanBoogie\Config\Builder;

readonly class Record
{
    /**
     * @template T of object
     *
     * @param float $timestamp When the configuration started to build.
     * @param float $duration The duration of the build.
     * @param class-string<T> $config_class
     * @param class-string<Builder<T>> $builder_class
     */
    public function __construct(
        public float $timestamp,
        public float $duration,
        public string $config_class,
        public string $builder_class,
    ) {
    }
}
