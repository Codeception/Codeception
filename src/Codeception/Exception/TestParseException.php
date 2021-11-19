<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

class TestParseException extends Exception
{
    public function __construct(string $fileName, string $errors = null, int $line = null)
    {
        $this->message = "Couldn't parse test '{$fileName}'";
        if ($line !== null) {
            $this->message .= " on line {$line}";
        }
        if ($errors) {
            $this->message .= PHP_EOL . $errors;
        }
    }
}
