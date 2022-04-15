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
 * Exception thrown in attempt to build a configuration without synthesizer defined.
 */
class NoSynthesizerDefined extends LogicException
{
	public function __construct(string $id, Throwable $previous = null)
	{
		parent::__construct($this->format_message($id), 500, $previous);
	}

	private function format_message(string $id): string
	{
		return "There is no synthesizer defined to build configuration `$id`."
			. " (https://icanboogie.org/docs/4.0/config#declaring-synthesizers)";
	}
}
