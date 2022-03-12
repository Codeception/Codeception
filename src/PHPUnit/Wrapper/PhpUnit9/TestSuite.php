<?php

namespace Codeception\PHPUnit\Wrapper;

use PHPUnit\Framework\TestResult;

abstract class TestSuite extends \PHPUnit\Framework\TestSuite
{
    public function run(?TestResult $result = null): TestResult
    {
        if ($result === null) {
            $result = new TestResult();
        }
        $this->realRun($result);
        return $result;
    }

    abstract protected function realRun(TestResult $result): void;
}
