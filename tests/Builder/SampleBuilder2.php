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
use Test\ICanBoogie\SampleConfig2;

final class SampleBuilder2 implements Builder
{
    static public function get_fragment_filename(): string
    {
        return "builder2";
    }

    public function build(): SampleConfig2
    {
        return new SampleConfig2();
    }
}
