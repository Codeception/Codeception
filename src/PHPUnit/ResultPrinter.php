<?php
namespace Codeception\PHPUnit;

use Codeception\PHPUnit\Compatibility\PHPUnit9;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Logging\TestDox\ResultPrinter as TestDoxPrinter;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Util\TestDox\ResultPrinter as TestDoxResultPrinter;

if (!class_exists(TestDoxPrinter::class)) {
    class_alias(TestDoxResultPrinter::class, TestDoxPrinter::class);
}

class ResultPrinter extends TestDoxPrinter
{
    /**
     * Removed in PHPUnit 10
     * @var int
     */
    protected $successful = 0;

    /**
     * An error occurred.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     */
    public function addError(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $this->testStatus = BaseTestRunner::STATUS_ERROR;
        } else {
            $this->testStatus = TestStatus::error($e->getMessage());
        }
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
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $this->testStatus = BaseTestRunner::STATUS_FAILURE;
        } else {
            $this->testStatus = TestStatus::failure($e->getMessage());
        }
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
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $this->testStatus = BaseTestRunner::STATUS_WARNING;
        } else {
            $this->testStatus = TestStatus::warning($e->getMessage());
        }
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
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $this->testStatus = BaseTestRunner::STATUS_INCOMPLETE;
        } else {
            $this->testStatus = TestStatus::incomplete($e->getMessage());
        }
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
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $this->testStatus = BaseTestRunner::STATUS_RISKY;
        } else {
            $this->testStatus = TestStatus::risky($e->getMessage());
        }
        $this->risky++;
    }

    /**
     * Skipped test.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     */
    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $this->testStatus = BaseTestRunner::STATUS_SKIPPED;
        } else {
            $this->testStatus = TestStatus::skipped($e->getMessage());
        }
        $this->skipped++;
    }

    public function startTest(\PHPUnit\Framework\Test $test) : void
    {
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $this->testStatus = BaseTestRunner::STATUS_PASSED;
        } else {
            $this->testStatus = TestStatus::success();
        }
    }

    public function printResult(TestResult $result): void
    {
        // TODO: Implement printResult() method.
    }
}
