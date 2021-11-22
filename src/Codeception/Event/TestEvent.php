<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\Test as PHPUnitTest;
use Symfony\Contracts\EventDispatcher\Event;

class TestEvent extends Event
{
    protected PHPUnitTest $test;

    protected ?float $time; // Time taken

    public function __construct(PHPUnitTest $test, ?float $time = 0)
    {
        $this->test = $test;
        $this->time = $time;
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
