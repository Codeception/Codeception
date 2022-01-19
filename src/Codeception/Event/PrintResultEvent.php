<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\TestResult;
use PHPUnit\Util\Printer;
use Symfony\Contracts\EventDispatcher\Event;

class PrintResultEvent extends Event
{
    public function __construct(
        protected TestResult $testResult,
        protected Printer $printer)
    {
    }

    public function getPrinter(): Printer
    {
        return $this->printer;
    }

    public function getResult(): TestResult
    {
        return $this->testResult;
    }
}
