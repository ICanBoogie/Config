<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

/**
 * Collects timing information about configuration synthesizes.
 */
final class ConfigProfiler
{
	static public $entries;

	/**
	 * @param float $started_at Start micro time.
	 * @param string $name Fragment name
	 * @param callable $synthesizer
	 */
	static public function add($started_at, $name, $synthesizer)
	{
		self::$entries[] = [ $started_at, microtime(true), $name, $synthesizer ];
	}
}
