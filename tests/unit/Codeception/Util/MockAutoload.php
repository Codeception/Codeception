<?php

namespace Codeception\Util;

class MockAutoload extends Autoload
{
    protected static $files = [];

    public static function setFiles(array $files)
    {
        self::$files = $files;
    }

    protected static function requireFile($file)
    {
        return in_array($file, self::$files);
    }
}
