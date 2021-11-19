<?php

declare(strict_types=1);

namespace Codeception\Event;

use PHPUnit\Framework\TestResult;
use PHPUnit\Util\Printer;
use Symfony\Contracts\EventDispatcher\Event;

class PrintResultEvent extends Event
{
    protected TestResult $result;

    protected Printer $printer;

    public function __construct(TestResult $testResult, Printer $printer)
    {
        $this->result = $testResult;
        $this->printer = $printer;
    }

    public function getPrinter(): Printer
    {
        return $this->printer;
    }

    public function getResult(): TestResult
    {
        return $this->result;
    }
}
