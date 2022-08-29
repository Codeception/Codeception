<?php

declare(strict_types=1);

namespace Codeception\Util;

use RuntimeException;

/**
 * Really basic class to store data in global array and use it in Cests/Tests.
 *
 * ```php
 * <?php
 * Fixtures::add('user1', ['name' => 'davert']);
 * Fixtures::get('user1');
 * Fixtures::exists('user1');
 * ```
 */
class Fixtures
{
    protected static array $fixtures = [];

    public static function add(string $name, $data): void
    {
        self::$fixtures[$name] = $data;
    }

    public static function get(string $name)
    {
        if (!self::exists($name)) {
            throw new RuntimeException("{$name} not found in fixtures");
        }

        return self::$fixtures[$name];
    }

    public static function cleanup(string $name = ''): void
    {
        if (self::exists($name)) {
            unset(self::$fixtures[$name]);
            return;
        }

        self::$fixtures = [];
    }

    public static function exists(string $name): bool
    {
        return isset(self::$fixtures[$name]);
    }
}
