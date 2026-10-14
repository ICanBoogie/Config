<?php

namespace Test\ICanBoogie\Builder;

use ICanBoogie\Config\Builder;
use Test\ICanBoogie\SampleConfig2;

/**
 * @implements Builder<SampleConfig2>
 */
final class SampleBuilder2 implements Builder
{
    #[\Override]
    public static function get_fragment_filename(): string
    {
        return "builder2";
    }

    #[\Override]
    public function build(): SampleConfig2
    {
        return new SampleConfig2();
    }
}
