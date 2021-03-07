<?php

namespace Codeception\Test;

use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestResult;

/**
 * Implements PHPUnit\Framework\Test of PHPUnit 10
 */
abstract class TestWrapper implements PHPUnitTest
{
    /**
     * Runs a test and collects its result in a TestResult instance.
     * Executes before/after hooks coming from traits.
     */
    final public function run(TestResult $result): void
    {
        $this->realRun($result);
    }

    abstract protected function realRun(TestResult $result);
}
