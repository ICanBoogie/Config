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

use ICanBoogie\Storage\FileStorage;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	static private $paths;

	static public function setupBeforeClass()
	{
		self::$paths = [

			__DIR__ . '/fixtures/config01' => 0,
			__DIR__ . '/fixtures/config02' => 0

		];
	}

	/**
	 * @expectedException \ICanBoogie\Config\NoSynthesizerDefined
	 */
	public function test_should_throw_exception_on_undefined_synthesizer()
	{
		$name = 'container';
		$configs = new Config(self::$paths);
		$configs[$name];
	}

	/**
	 * @expectedException \ICanBoogie\Config\NoFragmentDefined
	 */
	public function test_should_throw_exception_on_undefined_fragment()
	{
		$configs = new Config(self::$paths);
		$configs->synthesize(uniqid(), 'merge');
	}

	public function test_synthesize_with_array_merge()
	{
		$configs = new Config(self::$paths);

		$this->assertEquals([

			'cache config' => true,
			'session' => [

				'name' => "ICanBoogie"

			]

		], $configs->synthesize('app', 'recursive merge'));
	}

	public function test_states()
	{
		$configs = new Config([ __DIR__ . '/fixtures/config01' => 0 ]);
		$app1 = $configs->synthesize('app', 'recursive merge');
		$configs->add(__DIR__ . '/fixtures/config02');
		$app2 = $configs->synthesize('app', 'recursive merge');
		$app3 = $configs->synthesize('app', 'recursive merge');

		$this->assertNotSame($app1, $app2);
		$this->assertSame($app2, $app3);
	}

	public function test_states_with_cache()
	{
		$configs = new Config([ __DIR__ . '/fixtures/config01' => 0 ], [], new FileStorage(__DIR__ . '/cache'));
		$app1 = $configs->synthesize('app', 'recursive merge');
		$configs->add(__DIR__ . '/fixtures/config02');
		$app2 = $configs->synthesize('app', 'recursive merge');
		$app3 = $configs->synthesize('app', 'recursive merge');

		$this->assertNotSame($app1, $app2);
		$this->assertSame($app2, $app3);
		$this->assertNotSame($app3, $configs->synthesize('event', 'recursive merge'));
	}
}
