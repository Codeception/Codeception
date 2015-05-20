<?php

namespace Codeception\Util;

/**
 * Really basic class to store data in global array and use it in Cests/Tests.
 *
 * ```php
 * <?php
 * Fixtures::add('user1', ['name' => 'davert']);
 * Fixtures::get('user1');
 *
 * ?>
 * ```
 *
 */
class Fixtures
{
    protected static $fixtures = array();

    public static function add($name, $data)
    {
        self::$fixtures[$name] = $data;
    }

    public static function get($name)
    {
        if (!isset(self::$fixtures[$name])) {
            throw new \RuntimeException("$name not found in fixtures");
        }

        return self::$fixtures[$name];
    }

    public static function cleanup()
    {
        self::$fixtures = array();
    }
}
