<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Util\Log;

use Codeception\Test\Descriptor;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Printer;
use Symfony\Component\Yaml\Dumper as SymfonyYamlDumper;
use Throwable;
use function explode;
use function sprintf;
use function trim;

/**
 * A TestListener that generates a logfile of the
 * test execution using the Test Anything Protocol (TAP).
 */
class TAP extends Printer implements TestListener
{
    /**
     * @var int
     */
    protected $testNumber = 0;
    /**
     * @var int
     */
    protected $testSuiteLevel = 0;
    /**
     * @var bool
     */
    protected $testSuccessful = true;

    /**
     * TAP constructor.
     *
     * @param resource|string|null $out
     */
    public function __construct($out = null)
    {
        parent::__construct($out);
        $this->write("TAP version 13\n");
    }

    /**
     * An error occurred.
     *
     * @param PHPUnitTest $test
     * @param Throwable $t
     * @param float $time
     */
    public function addError(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->writeNotOk($test, 'Error');
    }

    /**
     * A warning occurred.
     *
     * @param PHPUnitTest $test
     * @param Warning $e
     * @param float $time
     */
    public function addWarning(PHPUnitTest $test, Warning $e, float $time): void
    {
        $this->writeNotOk($test, 'Warning');
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnitTest $test
     * @param AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(PHPUnitTest $test, AssertionFailedError $e, float $time): void
    {
        $this->writeNotOk($test, 'Failure');

        $message = explode(
            "\n",
            TestFailure::exceptionToString($e)
        );

        $diagnostic = [
            'message' => $message[0],
            'severity' => 'fail'
        ];

        if ($e instanceof ExpectationFailedException) {
            $cf = $e->getComparisonFailure();

            if ($cf !== null) {
                $diagnostic['data'] = [
                    'got' => $cf->getActual(),
                    'expected' => $cf->getExpected()
                ];
            }
        }

        $yamlDumper = new SymfonyYamlDumper;

        $this->write(
            sprintf(
                "  ---\n%s  ...\n",
                $yamlDumper->dump($diagnostic, 2, 2)
            )
        );
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnitTest $test
     * @param AssertionFailedError|Throwable $t
     * @param float $time
     */
    public function addIncompleteTest(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
    }

    /**
     * Risky test.
     *
     * @param PHPUnitTest $test
     * @param Throwable $t
     * @param float $time
     */
    public function addRiskyTest(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->write(
            sprintf(
                "ok %d - # RISKY%s\n",
                $this->testNumber,
                $t->getMessage() != '' ? ' ' . $t->getMessage() : ''
            )
        );

        $this->testSuccessful = false;
    }

    /**
     * Skipped test.
     *
     * @param PHPUnitTest $test
     * @param Throwable $t
     * @param float $time
     */
    public function addSkippedTest(PHPUnitTest $test, Throwable $t, float $time): void
    {
        $this->write(
            sprintf(
                "ok %d - # SKIP%s\n",
                $this->testNumber,
                $t->getMessage() != '' ? ' ' . $t->getMessage() : ''
            )
        );

        $this->testSuccessful = false;
    }

    /**
     * A testsuite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        ++$this->testSuiteLevel;
    }

    /**
     * A testsuite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        --$this->testSuiteLevel;
        if ($this->testSuiteLevel == 0) {
            $this->write(sprintf("1..%d\n", $this->testNumber));
        }
    }

    /**
     * A test started.
     *
     * @param PHPUnitTest $test
     */
    public function startTest(PHPUnitTest $test): void
    {
        ++$this->testNumber;
        $this->testSuccessful = true;
    }

    /**
     * A test ended.
     *
     * @param PHPUnitTest|TestCase $test
     * @param float $time
     */
    public function endTest(PHPUnitTest $test, float $time): void
    {
        if ($this->testSuccessful) {
            $this->write(
                sprintf(
                    "ok %d - %s\n",
                    $this->testNumber,
                    Descriptor::getTestSignature($test)
                )
            );
        }

        $this->writeDiagnostics($test);
    }

    protected function writeNotOk(PHPUnitTest $test, string $prefix = '', string $directive = ''): void
    {
        $this->write(
            sprintf(
                "not ok %d - %s%s%s\n",
                $this->testNumber,
                $prefix != '' ? $prefix . ': ' : '',
                \PHPUnit\Util\Test::describeAsString($test),
                $directive != '' ? ' # ' . $directive : ''
            )
        );

        $this->testSuccessful = false;
    }

    private function writeDiagnostics(PHPUnitTest $test): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        if (!$test->hasOutput()) {
            return;
        }

        foreach (explode("\n", trim($test->getActualOutput())) as $line) {
            $this->write(
                sprintf(
                    "# %s\n",
                    $line
                )
            );
        }
    }
}