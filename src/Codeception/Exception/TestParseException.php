<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

class TestParseException extends Exception
{
    public function __construct(string $fileName, string $errors = null, int $line = null, string $testFile = null)
    {
        $fileName = self::normalizePathSeparators($fileName);
        $this->message = "Couldn't parse test '{$fileName}'";
        if ($line !== null) {
            $this->message .= " on line {$line}";
        }
        if ($errors) {
            $this->message .= ":" . PHP_EOL . $errors;
        }
        if (($testFile = self::normalizePathSeparators($testFile)) && realpath($fileName) !== realpath($testFile)) {
            $this->message .= PHP_EOL . "(Error occurred while parsing Test '{$testFile}')";
        }
    }

    public static function normalizePathSeparators(?string $testFile): ?string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $testFile ?? '');
    }
}
