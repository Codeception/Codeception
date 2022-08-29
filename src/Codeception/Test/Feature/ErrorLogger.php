<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Test\Test as CodeceptTest;
use PHPUnit\Framework\TestResult;
use Throwable;

trait ErrorLogger
{
    abstract public function getTestResultObject(): TestResult;

    public function errorLoggerEnd(string $status, float $time, Throwable $exception = null): void
    {
        if ($exception === null) {
            return;
        }

        if ($status === CodeceptTest::STATUS_ERROR) {
             $this->getTestResultObject()->addError($this, $exception, $time);
        }
        if ($status === CodeceptTest::STATUS_FAIL) {
            $this->getTestResultObject()->addFailure($this, $exception, $time);
        }
    }
}
