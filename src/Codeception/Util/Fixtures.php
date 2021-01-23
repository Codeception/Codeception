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
 *
 * ?>
 * ```
 *
 */
class Fixtures
{
    protected static $fixtures = [];

    public static function add($name, $data): void
    {
        self::$fixtures[$name] = $data;
    }

    public static function get($name)
    {
        if (!self::exists($name)) {
            throw new RuntimeException("{$name} not found in fixtures");
        }

        return self::$fixtures[$name];
    }

    public static function cleanup($name = null): void
    {
        if (self::exists($name)) {
            unset(self::$fixtures[$name]);
            return;
        }

        self::$fixtures = [];
    }

    public static function exists($name): bool
    {
        return isset(self::$fixtures[$name]);
    }
}
