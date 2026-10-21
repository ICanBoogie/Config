<?php

namespace ICanBoogie\Env;

use RuntimeException;

/**
 * Exception thrown when the value of an environment variable cannot be converted to the requested type.
 *
 * The value is deliberately left out of the message, environment variables often hold secrets.
 */
class InvalidValue extends RuntimeException
{
    public function __construct(
        public readonly string $name,
        string $expected,
    ) {
        parent::__construct("The environment variable `$name` is not $expected.");
    }
}
