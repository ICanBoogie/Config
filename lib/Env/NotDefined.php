<?php

namespace ICanBoogie\Env;

use RuntimeException;

/**
 * Exception thrown when a required environment variable is not defined.
 */
class NotDefined extends RuntimeException
{
    public function __construct(
        public readonly string $name,
    ) {
        parent::__construct("The environment variable `$name` is not defined.");
    }
}
