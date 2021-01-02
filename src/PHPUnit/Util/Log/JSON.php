<?php

namespace Codeception\PHPUnit\Util\Log;

/**
 * A TestListener that generates JSON messages.
 */
class JSON extends \PHPUnit\Util\Printer implements \PHPUnit\Framework\TestListener
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
     * @param \PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     */
    public function addError(\PHPUnit\Framework\Test $test, \Throwable $e, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            \PHPUnit\Util\Filter::getFilteredStacktrace($e, false),
            \PHPUnit\Framework\TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
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
        $this->writeCase(
            'warning',
            $time,
            \PHPUnit\Util\Filter::getFilteredStacktrace($e, false),
            \PHPUnit\Framework\TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * A failure occurred.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \PHPUnit\Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(
        \PHPUnit\Framework\Test $test,
        \PHPUnit\Framework\AssertionFailedError $e,
        float $time
    ): void
    {
        $this->writeCase(
            'fail',
            $time,
            \PHPUnit\Util\Filter::getFilteredStacktrace($e, false),
            \PHPUnit\Framework\TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * Incomplete test.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param Throwable $e
     * @param float $time
     */
    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            \PHPUnit\Util\Filter::getFilteredStacktrace($e, false),
            'Incomplete Test: ' . $e->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * Risky test.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param Throwable $e
     * @param float $time
     */
    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            \PHPUnit\Util\Filter::getFilteredStacktrace($e, false),
            'Risky Test: ' . $e->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * Skipped test.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param Throwable $e
     * @param float $time
     */
    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time): void
    {
        $this->writeCase(
            'error',
            $time,
            \PHPUnit\Util\Filter::getFilteredStacktrace($e, false),
            'Skipped Test: ' . $e->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    /**
     * A testsuite started.
     *
     * @param \PHPUnit\Framework\TestSuite $suite
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
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
     * @param \PHPUnit\Framework\TestSuite $suite
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        $this->currentTestSuiteName = '';
        $this->currentTestName      = '';

        $this->writeArray($this->logEvents);
    }

    /**
     * A test started.
     *
     * @param \PHPUnit\Framework\Test $test
     */
    public function startTest(\PHPUnit\Framework\Test $test): void
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
     * @param \PHPUnit\Framework\Test $test
     * @param float $time
     */
    public function endTest(\PHPUnit\Framework\Test $test, float $time): void
    {
        if ($this->currentTestPass) {
            $this->writeCase('pass', $time, [], '', $test);
        }
    }

    /**
     * @param string $status
     * @param float $time
     * @param array $trace
     * @param string $message
     * @param \PHPUnit\Framework\TestCase|null $test
     */
    protected function writeCase($status, float $time, array $trace = [], $message = '', $test = null): void
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

    /**
     * @param array $event_data
     */
    protected function addLogEvent($event_data = []): void
    {
        if (count($event_data)) {
            array_push($this->logEvents, $event_data);
        }
    }

    /**
     * @param array $buffer
     */
    public function writeArray($buffer)
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

    private function convertToUtf8($string)
    {
        return mb_convert_encoding($string, 'UTF-8');
    }
}