<?php

declare(strict_types=1);

namespace Codeception\Util;

class MockAutoload extends Autoload
{
    /**
     * @var array
     */
    protected static array $files = [];

    /**
     * @param mixed[] $files
     */
    public static function setFiles(array $files)
    {
        self::$files = $files;
    }

    protected static function requireFile($file): bool
    {
        return in_array($file, self::$files);
    }
}
