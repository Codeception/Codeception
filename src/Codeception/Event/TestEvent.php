<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\Test as PHPUnitTest;
use Symfony\Contracts\EventDispatcher\Event;

class TestEvent extends Event
{
    /**
     * @param float|null $time Time taken
     */
    public function __construct(protected PHPUnitTest $test, protected ?float $time = 0)
    {
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function getTest(): PHPUnitTest
    {
        return $this->test;
    }
}
