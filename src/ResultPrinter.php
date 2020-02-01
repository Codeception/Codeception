<?php
namespace Codeception\PHPUnit;

use \PHPUnit\Framework\AssertionFailedError;
use \PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use \PHPUnit\Runner\BaseTestRunner;

class ResultPrinter extends \PHPUnit\Util\TestDox\ResultPrinter
{
    /**
     * An error occurred.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     */
    public function addError(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->testStatus = \PHPUnit\Runner\BaseTestRunner::STATUS_ERROR;
        $this->failed++;
    }

    /**
     * A failure occurred.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \PHPUnit\Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, float $time) : void
    {
        $this->testStatus = \PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE;
        $this->failed++;
    }

    /**
     * A warning occurred.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \PHPUnit\Framework\Warning $e
     * @param float $time
     */
    public function addWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time): void
    {
        $this->testStatus = \PHPUnit\Runner\BaseTestRunner::STATUS_WARNING;
        $this->warned++;
    }

    /**
     * Incomplete test.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     */
    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->testStatus = \PHPUnit\Runner\BaseTestRunner::STATUS_INCOMPLETE;
        $this->incomplete++;
    }

    /**
     * Risky test.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     *
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->testStatus = \PHPUnit\Runner\BaseTestRunner::STATUS_RISKY;
        $this->risky++;
    }

    /**
     * Skipped test.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     *
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->testStatus = \PHPUnit\Runner\BaseTestRunner::STATUS_SKIPPED;
        $this->skipped++;
    }

    public function startTest(\PHPUnit\Framework\Test $test) : void
    {
        $this->testStatus = \PHPUnit\Runner\BaseTestRunner::STATUS_PASSED;
    }

    public function printResult(TestResult $result): void
    {
        // TODO: Implement printResult() method.
    }
}
