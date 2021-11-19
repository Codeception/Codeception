<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\Test as PHPUnitTest;
use Throwable;

class FailEvent extends TestEvent
{
    protected Throwable $fail;

    protected int $count;

    public function __construct(PHPUnitTest $test, ?float $time, Throwable $e, int $count = 0)
    {
        parent::__construct($test, $time);
        $this->fail = $e;
        $this->count = $count;
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
