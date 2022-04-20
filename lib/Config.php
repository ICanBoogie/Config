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

use ArrayAccess;
use ICanBoogie\Config\NoFragmentDefined;
use ICanBoogie\Config\NoSynthesizerDefined;
use ICanBoogie\Storage\Storage;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

use function array_merge;
use function file_exists;
use function is_a;
use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Provides synthesized low-level configurations.
 */
class Config implements ArrayAccess
{
	static private array $require_cache = [];

	static private function isolated_require($__FILE__)
	{
		if (isset(self::$require_cache[$__FILE__])) {
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
	private array $paths = [];

	/**
	 * Synthesized configurations.
	 *
	 * @var array
	 */
	private array $synthesized = [];

	/**
	 * Initialize the {@link $paths}, {@link $synthesizers}, and {@link $cache} properties.
	 *
	 * @param array<string, int> $paths
	 *     An array of key/value pairs where _key_ is the path to a config directory and
	 *     _value_ is the weight of that path.
	 * @param array $synthesizers
	 * @param Storage|null $cache A cache for synthesized configurations.
	 */
	public function __construct(
		array $paths,
		private readonly array $synthesizers = [],
		public ?Storage $cache = null
	) {
		$this->add($paths);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws OffsetNotWritable in attempt to set a configuration.
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new OffsetNotWritable([ $offset, $this ]);
	}

	/**
	 * Checks if a config has been synthesized.
	 *
	 * @param string $offset A config identifier.
	 */
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->synthesized[$offset]);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws OffsetNotWritable in attempt to unset an offset.
	 */
	public function offsetUnset(mixed $offset): void
	{
		throw new OffsetNotWritable([ $offset, $this ]);
	}

	/**
	 * Returns the specified synthesized configuration.
	 *
	 * @param string $offset A config identifier.
	 *
	 * @throws InvalidArgumentException in attempt to obtain an undefined config.
	 */
	public function offsetGet(mixed $offset): mixed
	{
		if ($this->offsetExists($offset)) {
			return $this->synthesized[$offset];
		}

		$this->synthesizers[$offset] ?? throw new NoSynthesizerDefined($offset);

		[ $synthesizer, $from ] = $this->synthesizers[$offset] + [ 1 => $offset ];

		$started_at = microtime(true);

		$config = $this->synthesize($offset, $synthesizer, $from);

		ConfigProfiler::add($started_at, $offset, $synthesizer);

		return $config;
	}

	private ?string $cache_key = null;

	/**
	 * Build a cache key according to the current paths and the config name.
	 */
	private function get_cache_key(string $name): string
	{
		$this->cache_key ??= substr(sha1(implode('|', array_keys($this->paths))), 0, 8);

		return $this->cache_key . '_' . $name;
	}

	/**
	 * Revokes the synthesized configs and the cache key.
	 *
	 * The method is usually called after the config paths have been modified.
	 */
	private function revoke(): void
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
	 * @param array<string, int>|string $path
	 *     An array of key/value pairs where _key_ is the path to a config directory and
	 *     _value_ is the weight of that path.
	 * @param int $weight Weight of the path. The argument is discarded if `$path` is an array.
	 *
	 * @throws InvalidArgumentException if the path is empty.
	 */
	public function add(array|string $path, int $weight = 0)
	{
		if (!$path) {
			throw new InvalidArgumentException('$path is empty.');
		}

		$paths = $this->paths;

		if (is_array($path)) {
			$paths = array_merge($paths, $path);
		} else {
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
	public function get_fragments(string $name): array
	{
		$fragments = [];
		$filename = $name . '.php';

		foreach ($this->paths as $path => $weight) {
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$pathname = $path . $filename;

			if (!file_exists($pathname)) {
				continue;
			}

			$fragments[$path . $filename] = self::isolated_require($pathname);
		}

		return $fragments;
	}

	/**
	 * @param string $name Name of the configuration.
	 *
	 * @return iterable<string>
	 */
	private function path_iterator(string $name): iterable
	{
		$filename = $name . '.php';

		foreach (array_keys($this->paths) as $path) {
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$pathname = $path . $filename;

			if (!file_exists($pathname)) {
				continue;
			}

			yield $pathname;
		}
	}

	/**
	 * Synthesize a configuration.
	 *
	 * @param string $name Name of the configuration to synthesize.
	 * @param string|callable $synthesizer Callback for the synthesis.
	 * @param string|null $from If the configuration is a derivative $from is the name
	 * of the source configuration.
	 */
	public function synthesize(string $name, string|callable $synthesizer, string $from = null): mixed
	{
		if (array_key_exists($name, $this->synthesized)) {
			return $this->synthesized[$name];
		}

		$cache = $this->cache;
		$cache_key = $this->get_cache_key($name);

		if ($cache) {
			$config = $cache->retrieve($cache_key);

			if ($config !== null) {
				return $this->synthesized[$name] = $config;
			}
		}

		$config = $this->synthesize_for_real($from ?? $name, $synthesizer);

		$cache?->store($cache_key, $config);

		return $this->synthesized[$name] = $config;
	}

	private function synthesize_for_real(string $name, callable|string $synthesizer): mixed
	{
		if (is_a($synthesizer, ConfigBuilder::class, true)) {
			$builder = $this->resolve_config_builder($synthesizer);

			foreach ($this->path_iterator($name) as $path) {
				try {
					(function (ConfigBuilder $builder, string $__FRAGMENT_PATH__): void {
						(require $__FRAGMENT_PATH__)($builder);
					})(
						$builder,
						$path
					);
				} catch (Throwable $e) {
					throw new RuntimeException("Configuration failed with $path", previous: $e);
				}
			}

			return $builder->build();
		}

		$fragments = $this->get_fragments($name);

		if (!$fragments) {
			throw new NoFragmentDefined($name);
		}

		if ($synthesizer === 'merge') {
			return call_user_func_array('array_merge', array_values($fragments));
		}

		if ($synthesizer === 'recursive merge') {
			return call_user_func_array('ICanBoogie\array_merge_recursive', array_values($fragments));
		}

		return call_user_func($synthesizer, $fragments);
	}

	/**
	 * @param class-string<ConfigBuilder> $configurator
	 */
	private function resolve_config_builder(string $configurator): ConfigBuilder
	{
		return new $configurator();
	}
}
