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

use LogicException;
use Throwable;

/**
 * Exception throw when no fragment of a specified type is defined.
 */
class NoFragmentDefined extends LogicException implements Exception
{
    public function __construct(string $fragment, Throwable $previous = null)
    {
        parent::__construct($this->format_message($fragment), 500, $previous);
    }

    private function format_message(string $fragment): string
    {
        return "There is not `$fragment` fragment defined.";
    }
}
