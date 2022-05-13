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
use ICanBoogie\Config\NoBuilderDefined;
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
        $this->expectException(NoBuilderDefined::class);
        $configs[$name];
    }

    public function test_build(): void
    {
        $configs = new Config(self::PATHS);
        $config = $configs->build('builder', SampleBuilder::class);

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
        $config1 = $configs->build('builder', SampleBuilder::class);
        $configs->add(__DIR__ . '/fixtures/config02');
        $config2 = $configs->build('builder', SampleBuilder::class);
        $config3 = $configs->build('builder', SampleBuilder::class);

        $this->assertNotSame($config1, $config2);
        $this->assertSame($config2, $config3);
    }

    public function test_states_with_cache(): void
    {
        $configs = new Config([ __DIR__ . '/fixtures/config01' => 0 ], [], new FileStorage(__DIR__ . '/cache'));
        $config1 = $configs->build('builder', SampleBuilder::class);
        $configs->add(__DIR__ . '/fixtures/config02');
        $config2 = $configs->build('builder', SampleBuilder::class);
        $config3 = $configs->build('builder', SampleBuilder::class);

        $this->assertNotSame($config1, $config2);
        $this->assertSame($config2, $config3);
        $this->assertNotSame($config3, $configs->build('other_builder', SampleBuilder::class));
    }
}
