<?php

namespace Codeception\PHPUnit\Wrapper;

use PHPUnit\Framework\TestResult;

abstract class TestSuite extends \PHPUnit\Framework\TestSuite
{
    public function run(TestResult $result): void
    {
        $this->realRun($result);
    }

    abstract protected function realRun(TestResult $result): void;
}
