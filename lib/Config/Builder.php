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
