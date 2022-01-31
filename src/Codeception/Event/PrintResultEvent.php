<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\TestResult;
use Symfony\Contracts\EventDispatcher\Event;

class PrintResultEvent extends Event
{
    public function __construct(protected TestResult $testResult)
    {
    }

    public function getResult(): TestResult
    {
        return $this->testResult;
    }
}
