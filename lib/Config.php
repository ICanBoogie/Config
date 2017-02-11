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

use ICanBoogie\Config\NoFragmentDefined;
use ICanBoogie\Storage\Storage;

/**
 * Provides synthesized low-level configurations.
 */
class Config implements \ArrayAccess
{
	static private $require_cache = [];

	static private function isolated_require($__FILE__)
	{
		if (isset(self::$require_cache[$__FILE__]))
		{
			return self::$require_cache[$__FILE__];
		}

		return self::$require_cache[$__FILE__] = require $__FILE__;
	}

	/**
	 * An array of key/value where _key_ is a path to a config directory and _value_ is its weight.
	 * The array is sorted according to the weight of the paths.
	 *
	 * @var array
	 */
	private $paths = [];

	/**
	 * Callbacks to synthesize the configurations.
	 *
	 * @var array
	 */
	private $synthesizers = [];

	/**
	 * Synthesized configurations.
	 *
	 * @var array
	 */
	private $synthesized = [];

	/**
	 * A cache to store and retrieve the synthesized configurations.
	 *
	 * @var Storage
	 */
	public $cache;

	/**
	 * Initialize the {@link $paths}, {@link $synthesizers}, and {@link $cache} properties.
	 *
	 * @param array $paths An array of key/value pairs where _key_ is the path to a config
	 * directory and _value_ is the weight of that path.
	 * @param array $synthesizers
	 * @param Storage $cache A cache for synthesized configurations.
	 */
	public function __construct(array $paths, array $synthesizers = [], Storage $cache = null)
	{
		$this->synthesizers = $synthesizers;
		$this->cache = $cache;

		$this->add($paths);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws OffsetNotWritable in attempt to set a configuration.
	 */
	public function offsetSet($offset, $value)
	{
		throw new OffsetNotWritable([ $offset, $this ]);
	}

	/**
	 * Checks if a config has been synthesized.
	 *
	 * @param string $id The identifier of the config.
	 *
	 * @return bool `true` if the config has been synthesized, `false` otherwise.
	 */
	public function offsetExists($id)
	{
		return isset($this->synthesized[$id]);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws OffsetNotWritable in attempt to unset an offset.
	 */
	public function offsetUnset($offset)
	{
		throw new OffsetNotWritable([ $offset, $this ]);
	}

	/**
	 * Returns the specified synthesized configuration.
	 *
	 * @param string $id The identifier of the config.
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException in attempt to obtain an undefined config.
	 */
	public function offsetGet($id)
	{
		if ($this->offsetExists($id))
		{
			return $this->synthesized[$id];
		}

		if (empty($this->synthesizers[$id]))
		{
			throw new \InvalidArgumentException("There is no constructor defined to build the $id config.");
		}

		list($synthesizer, $from) = $this->synthesizers[$id] + [ 1 => $id ];

		$started_at = microtime(true);

		$config = $this->synthesize($id, $synthesizer, $from);

		ConfigProfiler::add($started_at, $id, $synthesizer);

		return $config;
	}

	/**
	 * @var string
	 */
	private $cache_key;

	/**
	 * Build a cache key according to the current paths and the config name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private function get_cache_key($name)
	{
		if (!$this->cache_key)
		{
			$this->cache_key = substr(sha1(implode('|', array_keys($this->paths))), 0, 8);
		}

		return $this->cache_key . '_' . $name;
	}

	/**
	 * Revokes the synthesized configs and the cache key.
	 *
	 * The method is usually called after the config paths have been modified.
	 */
	protected function revoke()
	{
		$this->synthesized = [];
		$this->cache_key = null;
	}

	/**
	 * Adds a path or several paths to the config.
	 *
	 * Paths are sorted according to their weight. The order in which they were defined is
	 * preserved for paths with the same weight.
	 *
	 * <pre>
	 * <?php
	 *
	 * $config->add('/path/to/config', 10);
	 * $config->add([
	 *
	 *     '/path1/to/config' => 10,
	 *     '/path2/to/config' => 10,
	 *     '/path2/to/config' => -10
	 *
	 * ]);
	 * </pre>
	 *
	 * @param string|array $path
	 * @param int $weight Weight of the path. The argument is discarded if `$path` is an array.
	 *
	 * @throws \InvalidArgumentException if the path is empty.
	 */
	public function add($path, $weight = 0)
	{
		if (!$path)
		{
			throw new \InvalidArgumentException('$path is empty.');
		}

		$paths = $this->paths;

		if (is_array($path))
		{
			$paths = array_merge($paths, $path);
		}
		else
		{
			$paths[$path] = $weight;
		}

		stable_sort($paths);

		$this->paths = $paths;
		$this->revoke();
	}

	/**
	 * Returns the fragments of a configuration.
	 *
	 * @param string $name Name of the configuration.
	 *
	 * @return array Where _key_ is the pathname to the fragment file and _value_ the value
	 * returned when the file was required.
	 */
	public function get_fragments($name)
	{
		$fragments = [];
		$filename = $name . '.php';

		foreach ($this->paths as $path => $weight)
		{
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$pathname = $path . $filename;

			if (!file_exists($pathname))
			{
				continue;
			}

			$fragments[$path . $filename] = self::isolated_require($pathname);
		}

		return $fragments;
	}

	/**
	 * Synthesize a configuration.
	 *
	 * @param string $name Name of the configuration to synthesize.
	 * @param string|array $synthesizer Callback for the synthesis.
	 * @param null|string $from If the configuration is a derivative $from is the name
	 * of the source configuration.
	 *
	 * @return mixed
	 */
	public function synthesize($name, $synthesizer, $from = null)
	{
		if (array_key_exists($name, $this->synthesized))
		{
			return $this->synthesized[$name];
		}

		$cache = $this->cache;
		$cache_key = $this->get_cache_key($name);

		if ($cache)
		{
			$config = $cache->retrieve($cache_key);

			if ($config !== null)
			{
				return $this->synthesized[$name] = $config;
			}
		}

		$config = $this->synthesize_for_real($from ?: $name, $synthesizer);

		if ($cache)
		{
			$cache->store($cache_key, $config);
		}

		return $this->synthesized[$name] = $config;
	}

	/**
	 * @param string $name
	 * @param callable $synthesizer
	 *
	 * @return mixed
	 */
	private function synthesize_for_real($name, $synthesizer)
	{
		$fragments = $this->get_fragments($name);

		if (!$fragments)
		{
			throw new NoFragmentDefined($name);
		}

		if ($synthesizer == 'merge')
		{
			return call_user_func_array('array_merge', $fragments);
		}

		if ($synthesizer == 'recursive merge')
		{
			return call_user_func_array('ICanBoogie\array_merge_recursive', $fragments);
		}

		return call_user_func($synthesizer, $fragments);
	}
}
