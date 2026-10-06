<?php

declare(strict_types=1);

namespace Codeception\Config;

use Codeception\Configuration;

/**
 * Reads a value from the loaded params (the PHP-config replacement for `%param%`).
 *
 * Works in suite and env config files. It cannot be used in `codeception.php` itself,
 * because params are declared there and are not loaded yet while it is being read —
 * use getenv() in the global file.
 */
final class Params
{
    public static function get(string $name, mixed $default = null): mixed
    {
        return Configuration::param($name, $default);
    }
}
