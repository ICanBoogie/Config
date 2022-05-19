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

use ArrayAccess;
use ICanBoogie\Config;
use ICanBoogie\Config\NoBuilderDefined;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Builder\SampleBuilder;
use Test\ICanBoogie\Builder\SampleBuilder2;

final class ConfigTest extends TestCase
{
    private const PATHS = [

        __DIR__ . '/fixtures/config01',
        __DIR__ . '/fixtures/config02',
        __DIR__ . '/fixtures/config03',

    ];

    public function test_should_throw_exception_on_undefined_synthesizer(): void
    {
        $configs = new Config(self::PATHS, []);
        $this->expectException(NoBuilderDefined::class);
        $configs->config_for_class(ArrayAccess::class);
    }

    public function test_build(): void
    {
        $expected = new SampleConfig(
            [ "one", "two" ],
            [ 2, 3 ],
            true
        );

        $configs = new Config(self::PATHS, [
            SampleConfig::class => SampleBuilder::class,
            SampleConfig2::class => SampleBuilder2::class,
        ]);

        $config = $configs->config_for_class(SampleConfig::class);

        $this->assertInstanceOf(SampleConfig::class, $config);
        $this->assertEquals($expected, $config);

        $this->assertNotEquals($expected, $configs->config_for_class(SampleConfig2::class));
    }
}
