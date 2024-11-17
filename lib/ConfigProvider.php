<?php

namespace ICanBoogie;

interface ConfigProvider
{
    /**
     * Returns a configuration of a specified class.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function config_for_class(string $class): object;
}
