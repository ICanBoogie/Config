<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\Builder;

use ICanBoogie\Config\Builder;
use Test\ICanBoogie\SampleConfig;

class SampleBuilder implements Builder
{
    private array $strings = [];
    private array $integers = [];
    public bool $bool;

    public function add_string(string $string)
    {
        $this->strings[] = $string;
    }

    public function add_int(string $int)
    {
        $this->integers[] = $int;
    }

    public function build(): mixed
    {
        return new SampleConfig(
            $this->strings,
            $this->integers,
            $this->bool,
        );
    }
}
