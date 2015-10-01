<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Test as CodeceptionTest;

trait ErrorLogger
{
    /**
     * @return \PHPUnit_Framework_TestResult
     */
    abstract public function getTestResultObject();

    public function errorLoggerEnd($status, $time, $exception = null)
    {
        if (!$exception) {
            return;
        }

        if ($status === CodeceptionTest::STATUS_ERROR) {
             $this->getTestResultObject()->addError($this, $exception, $time);
        }
        if ($status === CodeceptionTest::STATUS_FAIL) {
            $this->getTestResultObject()->addFailure($this, $exception, $time);
        }
    }
}