<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Config;

/**
 * A config builder provides an API to build a configuration.
 *
 * It's an alternative to the legacy configuration system built on arrays.
 */
interface Builder
{
    /**
     * Build the configuration.
     *
     * The configuration can be an array or an object, it doesn't matter, but it needs to be serializable.
     */
    public function build(): mixed;
}
