<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\Test as PHPUnitTest;
use Throwable;

class FailEvent extends TestEvent
{
    public function __construct(PHPUnitTest $test, ?float $time, protected Throwable $fail, protected int $count = 0)
    {
        parent::__construct($test, $time);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getFail(): Throwable
    {
        return $this->fail;
    }
}
