<?php

namespace Test\ICanBoogie;

use ICanBoogie\Env;
use ICanBoogie\Env\InvalidValue;
use ICanBoogie\Env\NotDefined;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function putenv;

final class EnvTest extends TestCase
{
    private const string NAME = 'ICANBOOGIE_CONFIG_TEST_VAR';

    protected function tearDown(): void
    {
        unset($_SERVER[self::NAME]);
        putenv(self::NAME);
    }

    public function test_reads_from_server(): void
    {
        $_SERVER[self::NAME] = 'from server';

        $actual = Env::string(self::NAME);

        $this->assertSame('from server', $actual);
    }

    public function test_reads_from_getenv(): void
    {
        putenv(self::NAME . '=from getenv');

        $actual = Env::string(self::NAME);

        $this->assertSame('from getenv', $actual);
    }

    public function test_server_takes_precedence_over_getenv(): void
    {
        $_SERVER[self::NAME] = 'from server';
        putenv(self::NAME . '=from getenv');

        $actual = Env::string(self::NAME);

        $this->assertSame('from server', $actual);
    }

    public function test_ignores_non_string_server_value(): void
    {
        $_SERVER[self::NAME] = [ 'not', 'a', 'string' ];

        $actual = Env::nullable_string(self::NAME);

        $this->assertNull($actual);
    }

    // string

    public function test_string_empty_is_a_value(): void
    {
        $_SERVER[self::NAME] = '';

        $actual = Env::string(self::NAME, 'default');

        $this->assertSame('', $actual);
    }

    public function test_string_default(): void
    {
        $actual = Env::string(self::NAME, 'default');

        $this->assertSame('default', $actual);
    }

    public function test_string_required(): void
    {
        $this->expectException(NotDefined::class);

        Env::string(self::NAME);
    }

    public function test_nullable_string(): void
    {
        $actual = Env::nullable_string(self::NAME);

        $this->assertNull($actual);
    }

    // bool

    #[DataProvider('provide_bool')]
    public function test_bool(string $value, bool $expected): void
    {
        $_SERVER[self::NAME] = $value;

        $actual = Env::bool(self::NAME);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{ string, bool }>
     */
    public static function provide_bool(): array
    {
        return [
            'one' => [ '1', true ],
            'true' => [ 'true', true ],
            'TRUE' => [ 'TRUE', true ],
            'on' => [ 'on', true ],
            'yes' => [ 'yes', true ],
            'zero' => [ '0', false ],
            'false' => [ 'false', false ],
            'Off' => [ 'Off', false ],
            'no' => [ 'no', false ],
        ];
    }

    public function test_bool_invalid(): void
    {
        $_SERVER[self::NAME] = 'maybe';

        $this->expectException(InvalidValue::class);
        $this->expectExceptionMessage('`' . self::NAME . '` is not a boolean');

        Env::bool(self::NAME);
    }

    public function test_bool_default(): void
    {
        $actual = Env::bool(self::NAME, false);

        $this->assertFalse($actual);
    }

    public function test_bool_empty_is_undefined(): void
    {
        $_SERVER[self::NAME] = '';

        $actual = Env::bool(self::NAME, true);

        $this->assertTrue($actual);
    }

    public function test_bool_required(): void
    {
        $this->expectException(NotDefined::class);
        $this->expectExceptionMessage('`' . self::NAME . '` is not defined');

        Env::bool(self::NAME);
    }

    public function test_bool_explicitly_required(): void
    {
        $this->expectException(NotDefined::class);

        Env::bool(self::NAME, Env::REQUIRED);
    }

    public function test_nullable_bool(): void
    {
        $actual = Env::nullable_bool(self::NAME);

        $this->assertNull($actual);
    }

    public function test_nullable_bool_defined(): void
    {
        $_SERVER[self::NAME] = 'false';

        $actual = Env::nullable_bool(self::NAME);

        $this->assertFalse($actual);
    }

    public function test_nullable_bool_invalid(): void
    {
        $_SERVER[self::NAME] = 'maybe';

        $this->expectException(InvalidValue::class);

        Env::nullable_bool(self::NAME);
    }

    // int

    public function test_int(): void
    {
        $_SERVER[self::NAME] = '-12';

        $actual = Env::int(self::NAME);

        $this->assertSame(-12, $actual);
    }

    public function test_int_zero(): void
    {
        $_SERVER[self::NAME] = '0';

        $actual = Env::int(self::NAME, 5);

        $this->assertSame(0, $actual);
    }

    #[DataProvider('provide_invalid_number')]
    public function test_int_invalid(string $value): void
    {
        $_SERVER[self::NAME] = $value;

        $this->expectException(InvalidValue::class);
        $this->expectExceptionMessage('`' . self::NAME . '` is not an integer');

        Env::int(self::NAME);
    }

    /**
     * @return array<string, array{ string }>
     */
    public static function provide_invalid_number(): array
    {
        return [
            'trailing garbage' => [ '12abc' ],
            'word' => [ 'abc' ],
            'float' => [ '1.5' ],
        ];
    }

    public function test_int_default(): void
    {
        $actual = Env::int(self::NAME, 5);

        $this->assertSame(5, $actual);
    }

    public function test_int_required(): void
    {
        $this->expectException(NotDefined::class);

        Env::int(self::NAME);
    }

    public function test_nullable_int(): void
    {
        $actual = Env::nullable_int(self::NAME);

        $this->assertNull($actual);
    }

    public function test_nullable_int_defined(): void
    {
        $_SERVER[self::NAME] = '42';

        $actual = Env::nullable_int(self::NAME);

        $this->assertSame(42, $actual);
    }

    // float

    public function test_float(): void
    {
        $_SERVER[self::NAME] = '1.5';

        $actual = Env::float(self::NAME);

        $this->assertSame(1.5, $actual);
    }

    public function test_float_invalid(): void
    {
        $_SERVER[self::NAME] = '1.5abc';

        $this->expectException(InvalidValue::class);
        $this->expectExceptionMessage('`' . self::NAME . '` is not a float');

        Env::float(self::NAME);
    }

    public function test_float_default(): void
    {
        $actual = Env::float(self::NAME, 2.5);

        $this->assertSame(2.5, $actual);
    }

    public function test_float_required(): void
    {
        $this->expectException(NotDefined::class);

        Env::float(self::NAME);
    }

    public function test_nullable_float(): void
    {
        $actual = Env::nullable_float(self::NAME);

        $this->assertNull($actual);
    }

    public function test_nullable_float_defined(): void
    {
        $_SERVER[self::NAME] = '0.25';

        $actual = Env::nullable_float(self::NAME);

        $this->assertSame(0.25, $actual);
    }

    public function test_invalid_value_does_not_leak_the_value(): void
    {
        $_SERVER[self::NAME] = 's3cr3t';

        try {
            Env::int(self::NAME);
            $this->fail('Expected InvalidValue');
        } catch (InvalidValue $e) {
            $this->assertStringNotContainsString('s3cr3t', $e->getMessage());
            $this->assertSame(self::NAME, $e->name);
        }
    }
}
