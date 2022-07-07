<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\Test\Test;
use Symfony\Contracts\EventDispatcher\Event;

class TestEvent extends Event
{
    /**
     * @param float|null $time Time taken
     */
    public function __construct(protected Test $test, protected ?float $time = 0)
    {
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function getTest(): Test
    {
        return $this->test;
    }
}
