<?php

namespace ICanBoogie\ConfigProvider;

use ICanBoogie\ConfigProvider;

interface ConfigProviderCache
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function get(string $class, ConfigProvider $provider): object;
}
