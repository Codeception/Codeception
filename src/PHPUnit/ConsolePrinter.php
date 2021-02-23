<?php

namespace Codeception\PHPUnit;

use PHPUnit\Framework\TestResult;

/**
 * Printer implementing this interface prints output to console, thus should be marked as printer and not just a logger
 *
 * Interface ConsolePrinter
 * @package Codeception\PHPUnit
 */
interface ConsolePrinter
{
    public function printResult(TestResult $result): void;

    public function write(string $buffer): void;
}
