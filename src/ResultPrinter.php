<?php
namespace Codeception\PHPUnit;

use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Runner_BaseTestRunner;

class ResultPrinter extends \PHPUnit_Util_TestDox_ResultPrinter
{
    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param \Exception $e
     * @param float $time
     */
    public function addError(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
        $this->failed++;
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
        $this->failed++;
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param \Exception $e
     * @param float $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
        $this->incomplete++;
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param \Exception $e
     * @param float $time
     *
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_RISKY;
        $this->risky++;
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param \Exception $e
     * @param float $time
     *
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
        $this->skipped++;
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
    }
}
