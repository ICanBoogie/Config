<?php

namespace ICanBoogie\Config;

/**
 * A configuration builder.
 *
 * @template T of object
 */
interface Builder
{
    /**
     * Returns the filename of the configuration fragments used by this builder.
     */
    public static function get_fragment_filename(): string;

    /**
     * Builds the configuration object.
     *
     * @return T
     */
    public function build(): object;
}
