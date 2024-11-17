<?php

namespace Test\ICanBoogie;

final readonly class SampleConfig
{
    /**
     * @param string[] $strings
     * @param int[] $integers
     */
    public function __construct(
        public array $strings,
        public array $integers,
        public bool $bool,
    ) {
    }
}
