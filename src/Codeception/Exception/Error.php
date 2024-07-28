<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

class Error extends Exception
{
    public function __construct(string $message, int $code, string $file, int $line, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->file = $file;
        $this->line = $line;
    }
}
