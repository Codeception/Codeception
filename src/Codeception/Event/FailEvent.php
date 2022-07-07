<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\Test\Test;
use Throwable;

class FailEvent extends TestEvent
{
    public function __construct(Test $test, private Throwable $fail, ?float $time)
    {
        parent::__construct($test, $time);
    }

    public function getFail(): Throwable
    {
        return $this->fail;
    }
}
