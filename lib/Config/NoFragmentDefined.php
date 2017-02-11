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
 * Exception throw when no fragment of a specified type is defined.
 */
class NoFragmentDefined extends \LogicException implements Exception
{
	/**
	 * @param string $fragment
	 * @param \Exception|null $previous
	 */
	public function __construct($fragment, \Exception $previous = null)
	{
		parent::__construct($this->format_message($fragment), 500, $previous);
	}

	/**
	 * @param $fragment
	 *
	 * @return string
	 */
	protected function format_message($fragment)
	{
		return "There is not `$fragment` fragment defined.";
	}
}
