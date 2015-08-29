<?php

namespace ICanBoogie;

final class ConfigProfiler
{
	static public $entries;

	static public function add($started_at, $name, $synthesizer)
	{
		self::$entries[] = [ $started_at, microtime(true), $name, $synthesizer ];
	}
}
