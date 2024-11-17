<?php

namespace ICanBoogie\ConfigProvider;

use ICanBoogie\ConfigProvider;
use ICanBoogie\Storage\Storage;

final class CacheDecorator implements ConfigProvider
{
    public function __construct(
        private readonly ConfigProvider $inner,
        private readonly Storage $storage,
    ) {
    }

    /**
     * Returns a configuration of a specified class.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function config_for_class(string $class): object
    {
        /** @var ?T $config */
        $config = $this->storage->retrieve($class);

        if ($config) {
            return $config;
        }

        $config = $this->inner->config_for_class($class);
        $this->storage->store($class, $config);

        return $config;
    }
}
