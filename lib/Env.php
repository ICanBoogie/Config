<?php

namespace ICanBoogie;

use ICanBoogie\Env\InvalidValue;
use ICanBoogie\Env\NotDefined;

use function filter_var;
use function getenv;
use function is_string;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

/**
 * Typed access to environment variables.
 *
 * This is meant to be used by configuration fragments, past the configuration the application should rely on the
 * values it was configured with. Values read from here are frozen in cached configurations.
 *
 * Variables are looked up in `$_SERVER`, then with `getenv()`. Unless the variable is a string, an empty value is
 * considered undefined.
 *
 * Use `REQUIRED`, the default value, to fail when the variable is not defined. Use the `nullable_*` variants to
 * obtain `null` instead.
 */
enum Env
{
    case REQUIRED;

    /**
     * @throws NotDefined
     */
    public static function string(string $name, string|self $default = self::REQUIRED): string
    {
        return self::lookup($name) ?? ($default instanceof self ? throw new NotDefined($name) : $default);
    }

    public static function nullable_string(string $name): ?string
    {
        return self::lookup($name);
    }

    /**
     * @throws NotDefined
     * @throws InvalidValue
     */
    public static function bool(string $name, bool|self $default = self::REQUIRED): bool
    {
        return self::nullable_bool($name) ?? ($default instanceof self ? throw new NotDefined($name) : $default);
    }

    /**
     * @throws InvalidValue
     */
    public static function nullable_bool(string $name): ?bool
    {
        $value = self::defined($name);

        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ?? throw new InvalidValue($name, 'a boolean');
    }

    /**
     * @throws NotDefined
     * @throws InvalidValue
     */
    public static function int(string $name, int|self $default = self::REQUIRED): int
    {
        return self::nullable_int($name) ?? ($default instanceof self ? throw new NotDefined($name) : $default);
    }

    /**
     * @throws InvalidValue
     */
    public static function nullable_int(string $name): ?int
    {
        $value = self::defined($name);

        if ($value === null) {
            return null;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int === false ? throw new InvalidValue($name, 'an integer') : $int;
    }

    /**
     * @throws NotDefined
     * @throws InvalidValue
     */
    public static function float(string $name, float|self $default = self::REQUIRED): float
    {
        return self::nullable_float($name) ?? ($default instanceof self ? throw new NotDefined($name) : $default);
    }

    /**
     * @throws InvalidValue
     */
    public static function nullable_float(string $name): ?float
    {
        $value = self::defined($name);

        if ($value === null) {
            return null;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $float === false ? throw new InvalidValue($name, 'a float') : $float;
    }

    /**
     * Returns the raw value of a variable, or `null` if it is not defined.
     */
    private static function lookup(string $name): ?string
    {
        $value = $_SERVER[$name] ?? getenv($name);

        return is_string($value) ? $value : null;
    }

    /**
     * Same as {@see lookup()}, but an empty value is considered undefined. A variable is commonly emptied rather
     * than unset (`APP_CACHE=`) to disable it, and an empty string is never a valid boolean, integer, or float.
     */
    private static function defined(string $name): ?string
    {
        $value = self::lookup($name);

        return $value === '' ? null : $value;
    }
}
