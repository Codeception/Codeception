<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Throwable;

class ThrowableWrapper extends Error
{
    public function __construct(Throwable $throwable)
    {
        parent::__construct(
            $throwable::class . ': ' . $throwable->getMessage(),
            $throwable->getCode(),
            $throwable->getFile(),
            $throwable->getLine()
        );
    }
}
