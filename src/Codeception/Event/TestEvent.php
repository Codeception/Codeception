<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\Test as PHPUnitTest;
use Symfony\Contracts\EventDispatcher\Event;

class TestEvent extends Event
{
    /**
     * @var PHPUnitTest
     */
    protected $test;

    /**
     * @var float Time taken
     */
    protected $time;

    public function __construct(PHPUnitTest $test, ?float $time = 0)
    {
        $this->test = $test;
        $this->time = $time;
    }

    public function getTime(): float
    {
        return $this->time;
    }

    /**
     * @return \Codeception\TestInterface
     */
    public function getTest()
    {
        return $this->test;
    }
}
