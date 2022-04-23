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

use ICanBoogie\Config;
use ICanBoogie\Config\NoFragmentDefined;
use ICanBoogie\Config\NoSynthesizerDefined;
use ICanBoogie\Storage\FileStorage;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Builder\SampleBuilder;

final class ConfigTest extends TestCase
{
	private const PATHS = [

		__DIR__ . '/fixtures/config01' => 0,
		__DIR__ . '/fixtures/config02' => 0,
		__DIR__ . '/fixtures/config03' => 0,

	];

	public function test_should_throw_exception_on_undefined_synthesizer(): void
	{
		$name = 'container';
		$configs = new Config(self::PATHS);
		$this->expectException(NoSynthesizerDefined::class);
		$configs[$name];
	}

	public function test_should_throw_exception_on_undefined_fragment(): void
	{
		$configs = new Config(self::PATHS);
		$this->expectException(NoFragmentDefined::class);
		$configs->synthesize(uniqid(), 'merge');
	}

	public function test_synthesize_with_array_merge(): void
	{
		$configs = new Config(self::PATHS);

		$this->assertEquals([

			'cache config' => true,
			'session' => [

				'name' => "ICanBoogie"

			]

		], $configs->synthesize('app', 'recursive merge'));
	}

	public function test_with_builder(): void
	{
		$configs = new Config(self::PATHS);
		$config = $configs->synthesize('builder', SampleBuilder::class);

		$this->assertInstanceOf(SampleConfig::class, $config);
		$this->assertEquals(
			new SampleConfig(
				[ "one", "two" ],
				[ 2, 3 ],
				true
			),
			$config
		);
	}

	public function test_states(): void
	{
		$configs = new Config([ __DIR__ . '/fixtures/config01' => 0 ]);
		$app1 = $configs->synthesize('app', 'recursive merge');
		$configs->add(__DIR__ . '/fixtures/config02');
		$app2 = $configs->synthesize('app', 'recursive merge');
		$app3 = $configs->synthesize('app', 'recursive merge');

		$this->assertNotSame($app1, $app2);
		$this->assertSame($app2, $app3);
	}

	public function test_states_with_cache(): void
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
