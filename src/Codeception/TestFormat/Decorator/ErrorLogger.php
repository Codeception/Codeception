<?php
namespace Codeception\TestFormat\Decorator;

use Codeception\Test as CodeceptionTest;

trait ErrorLogger
{
    /**
     * @return \PHPUnit_Framework_TestResult
     */
    abstract public function getTestResult();

    public function errorLoggerEnd($status, $time, $exception = null)
    {
        if (!$exception) {
            return;
        }

        if ($status === CodeceptionTest::STATUS_ERROR) {
             $this->getTestResult()->addError($this, $exception, $time);
        }
        if ($status === CodeceptionTest::STATUS_FAIL) {
            $this->getTestResult()->addFailure($this, $exception, $time);
        }
    }
}