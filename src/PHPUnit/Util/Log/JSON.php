<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Util\Log;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Printer;
use Throwable;
use function array_walk_recursive;
use function count;
use function is_string;
use function json_encode;
use function mb_convert_encoding;
use function method_exists;

/**
 * A TestListener that generates JSON messages.
 */
class JSON extends Printer implements TestListener
{
    /**
     * @var string
     */
    protected $currentTestSuiteName = '';
    /**
     * @var string
     */
    protected $currentTestName = '';
    /**
     * @var bool
     */
    protected $currentTestPass = true;
    /**
     * @var array
     */
    protected $logEvents = [];

    /**
     * An error occurred.
     *
     * @param TestCase|PHPUnitTest $test
     * @param Throwable $t
     * @param float $time
     */
    public function addError(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            Filter::getFilteredStacktrace($t, false),
            TestFailure::exceptionToString($t),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * A warning occurred.
     *
     * @param TestCase|PHPUnitTest $test
     * @param Warning $e
     * @param float $time
     */
    public function addWarning(PHPUnitTest $test, Warning $e, float $time): void
    {
        $this->writeCase(
            'warning',
            $time,
            Filter::getFilteredStacktrace($e, false),
            TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * A failure occurred.
     *
     * @param TestCase|PHPUnitTest $test
     * @param AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(PHPUnitTest $test, AssertionFailedError $e, float $time): void
    {
        $this->writeCase(
            'fail',
            $time,
            Filter::getFilteredStacktrace($e, false),
            TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * Incomplete test.
     *
     * @param TestCase|PHPUnitTest $test
     * @param AssertionFailedError|Throwable $t
     * @param float $time
     */
    public function addIncompleteTest(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            Filter::getFilteredStacktrace($t, false),
            'Incomplete Test: ' . $t->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * Risky test.
     *
     * @param TestCase|PHPUnitTest $test
     * @param Throwable $t
     * @param float $time
     */
    public function addRiskyTest(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            Filter::getFilteredStacktrace($t, false),
            'Risky Test: ' . $t->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * Skipped test.
     *
     * @param TestCase|PHPUnitTest $test
     * @param Throwable $t
     * @param float $time
     */
    public function addSkippedTest(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            Filter::getFilteredStacktrace($t, false),
            'Skipped Test: ' . $t->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * A testsuite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $this->currentTestSuiteName = $suite->getName();
        $this->currentTestName      = '';

        $this->addLogEvent(
            [
                'event' => 'suiteStart',
                'suite' => $this->currentTestSuiteName,
                'tests' => count($suite)
            ]
        );
    }

    /**
     * A testsuite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        $this->currentTestSuiteName = '';
        $this->currentTestName      = '';
        $this->writeArray($this->logEvents);
    }

    /**
     * A test started.
     *
     * @param PHPUnitTest $test
     */
    public function startTest(PHPUnitTest $test): void
    {
        $this->currentTestName = \PHPUnit\Util\Test::describe($test);
        $this->currentTestPass = true;

        $this->addLogEvent(
            [
                'event' => 'testStart',
                'suite' => $this->currentTestSuiteName,
                'test'  => $this->currentTestName
            ]
        );
    }

    /**
     * A test ended.
     *
     * @param PHPUnitTest|TestCase $test
     * @param float $time
     */
    public function endTest(PHPUnitTest $test, float $time): void
    {
        if ($this->currentTestPass) {
            $this->writeCase('pass', $time, [], '', $test);
        }
    }

    protected function writeCase(string $status, float $time, array $trace = [], string $message = '', ?TestCase $test = null): void
    {
        $output = '';
        // take care of TestSuite producing error (e.g. by running into exception) as TestSuite doesn't have hasOutput
        if ($test !== null && method_exists($test, 'hasOutput') && $test->hasOutput()) {
            $output = $test->getActualOutput();
        }
        $this->addLogEvent(
            [
                'event'   => 'test',
                'suite'   => $this->currentTestSuiteName,
                'test'    => $this->currentTestName,
                'status'  => $status,
                'time'    => $time,
                'trace'   => $trace,
                'message' => $this->convertToUtf8($message),
                'output'  => $output,
            ]
        );
    }

    protected function addLogEvent(array $eventData = []): void
    {
        if (count($eventData) > 0) {
            $this->logEvents[] = $eventData;
        }
    }

    public function writeArray(array $buffer): void
    {
        array_walk_recursive(
            $buffer, function (&$input) {
            if (is_string($input)) {
                $input = $this->convertToUtf8($input);
            }
        }
        );

        $this->write(json_encode($buffer, JSON_PRETTY_PRINT));
    }

    private function convertToUtf8(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8');
    }
}