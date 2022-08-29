<?php

namespace Codeception\PHPUnit\Wrapper;

use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestResult;

abstract class Test implements PHPUnitTest
{
    public function run(): void
    {
        // does nothing
    }
}
