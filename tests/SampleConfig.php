<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie;

final class SampleConfig
{
	public function __construct(
		public readonly array $strings,
		public readonly array $integers,
		public readonly bool $bool,
	) {
	}
}
