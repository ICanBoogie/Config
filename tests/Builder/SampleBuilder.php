<?php

namespace Test\ICanBoogie\Builder;

use ICanBoogie\Config\Builder;
use Test\ICanBoogie\SampleConfig;

final class SampleBuilder implements Builder
{
    public static function get_fragment_filename(): string
    {
        return "builder";
    }

    /**
     * @var string[]
     */
    private array $strings = [];

    /**
     * @var int[]
     */
    private array $integers = [];

    public bool $bool = false;

    public function add_string(string $string): self
    {
        $this->strings[] = $string;

        return $this;
    }

    public function add_int(int $int): self
    {
        $this->integers[] = $int;

        return $this;
    }

    public function build(): SampleConfig
    {
        return new SampleConfig(
            $this->strings,
            $this->integers,
            $this->bool,
        );
    }
}
