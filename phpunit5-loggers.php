<?php
// @codingStandardsIgnoreStart
namespace {
    /*
     * This file is part of PHPUnit.
     *
     * (c) Sebastian Bergmann <sebastian@phpunit.de>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    if (!class_exists('PHPUnit_Util_String')) {
        /**
         * String helpers.
         */
        class PHPUnit_Util_String
        {
            /**
             * Converts a string to UTF-8 encoding.
             *
             * @param string $string
             *
             * @return string
             */
            public static function convertToUtf8($string)
            {
                return mb_convert_encoding($string, 'UTF-8');
            }

            /**
             * Checks a string for UTF-8 encoding.
             *
             * @param string $string
             *
             * @return bool
             */
            protected static function isUtf8($string)
            {
                $length = strlen($string);

                for ($i = 0; $i < $length; $i++) {
                    if (ord($string[$i]) < 0x80) {
                        $n = 0;
                    } elseif ((ord($string[$i]) & 0xE0) == 0xC0) {
                        $n = 1;
                    } elseif ((ord($string[$i]) & 0xF0) == 0xE0) {
                        $n = 2;
                    } elseif ((ord($string[$i]) & 0xF0) == 0xF0) {
                        $n = 3;
                    } else {
                        return false;
                    }

                    for ($j = 0; $j < $n; $j++) {
                        if ((++$i == $length) || ((ord($string[$i]) & 0xC0) != 0x80)) {
                            return false;
                        }
                    }
                }

                return true;
            }
        }
    }

    /*
     * This file is part of PHPUnit.
     *
     * (c) Sebastian Bergmann <sebastian@phpunit.de>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    /**
     * A TestListener that generates JSON messages.
     */
    if (!class_exists('PHPUnit_Util_Log_JSON')) {
        class PHPUnit_Util_Log_JSON extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
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
             * An error occurred.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->writeCase(
                    'error',
                    $time,
                    PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
                    PHPUnit_Framework_TestFailure::exceptionToString($e),
                    $test
                );

                $this->currentTestPass = false;
            }

            /**
             * A warning occurred.
             *
             * @param PHPUnit_Framework_Test $test
             * @param PHPUnit_Framework_Warning $e
             * @param float $time
             */
            public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
            {
                $this->writeCase(
                    'warning',
                    $time,
                    PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
                    PHPUnit_Framework_TestFailure::exceptionToString($e),
                    $test
                );

                $this->currentTestPass = false;
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
                $this->writeCase(
                    'fail',
                    $time,
                    PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
                    PHPUnit_Framework_TestFailure::exceptionToString($e),
                    $test
                );

                $this->currentTestPass = false;
            }

            /**
             * Incomplete test.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->writeCase(
                    'error',
                    $time,
                    PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
                    'Incomplete Test: ' . $e->getMessage(),
                    $test
                );

                $this->currentTestPass = false;
            }

            /**
             * Risky test.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->writeCase(
                    'error',
                    $time,
                    PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
                    'Risky Test: ' . $e->getMessage(),
                    $test
                );

                $this->currentTestPass = false;
            }

            /**
             * Skipped test.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->writeCase(
                    'error',
                    $time,
                    PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
                    'Skipped Test: ' . $e->getMessage(),
                    $test
                );

                $this->currentTestPass = false;
            }

            /**
             * A testsuite started.
             *
             * @param PHPUnit_Framework_TestSuite $suite
             */
            public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
            {
                $this->currentTestSuiteName = $suite->getName();
                $this->currentTestName = '';

                $this->write(
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
             * @param PHPUnit_Framework_TestSuite $suite
             */
            public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
            {
                $this->currentTestSuiteName = '';
                $this->currentTestName = '';
            }

            /**
             * A test started.
             *
             * @param PHPUnit_Framework_Test $test
             */
            public function startTest(PHPUnit_Framework_Test $test)
            {
                $this->currentTestName = PHPUnit_Util_Test::describe($test);
                $this->currentTestPass = true;

                $this->write(
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
             * @param PHPUnit_Framework_Test $test
             * @param float $time
             */
            public function endTest(PHPUnit_Framework_Test $test, $time)
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
             * @param PHPUnit_Framework_TestCase|null $test
             */
            protected function writeCase($status, $time, array $trace = [], $message = '', $test = null)
            {
                $output = '';
                // take care of TestSuite producing error (e.g. by running into exception) as TestSuite doesn't have hasOutput
                if ($test !== null && method_exists($test, 'hasOutput') && $test->hasOutput()) {
                    $output = $test->getActualOutput();
                }
                $this->write(
                    [
                        'event'   => 'test',
                        'suite'   => $this->currentTestSuiteName,
                        'test'    => $this->currentTestName,
                        'status'  => $status,
                        'time'    => $time,
                        'trace'   => $trace,
                        'message' => PHPUnit_Util_String::convertToUtf8($message),
                        'output'  => $output,
                    ]
                );
            }

            /**
             * @param string $buffer
             */
            public function write($buffer)
            {
                array_walk_recursive(
                    $buffer, function (&$input) {
                    if (is_string($input)) {
                        $input = PHPUnit_Util_String::convertToUtf8($input);
                    }
                }
                );

                parent::write(json_encode($buffer, JSON_PRETTY_PRINT));
            }
        }
    }

    /*
     * This file is part of PHPUnit.
     *
     * (c) Sebastian Bergmann <sebastian@phpunit.de>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    if (!class_exists('PHPUnit_Util_Log_TAP')) {

        /**
         * A TestListener that generates a logfile of the
         * test execution using the Test Anything Protocol (TAP).
         */
        class PHPUnit_Util_Log_TAP extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
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
             * Constructor.
             *
             * @param mixed $out
             *
             * @throws PHPUnit_Framework_Exception
             */
            public function __construct($out = null)
            {
                parent::__construct($out);
                $this->write("TAP version 13\n");
            }

            /**
             * An error occurred.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->writeNotOk($test, 'Error');
            }

            /**
             * A warning occurred.
             *
             * @param PHPUnit_Framework_Test $test
             * @param PHPUnit_Framework_Warning $e
             * @param float $time
             */
            public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
            {
                $this->writeNotOk($test, 'Warning');
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
                $this->writeNotOk($test, 'Failure');

                $message = explode(
                    "\n",
                    PHPUnit_Framework_TestFailure::exceptionToString($e)
                );

                $diagnostic = [
                    'message'  => $message[0],
                    'severity' => 'fail'
                ];

                if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
                    $cf = $e->getComparisonFailure();

                    if ($cf !== null) {
                        $diagnostic['data'] = [
                            'got'      => $cf->getActual(),
                            'expected' => $cf->getExpected()
                        ];
                    }
                }

                $yaml = new Symfony\Component\Yaml\Dumper;

                $this->write(
                    sprintf(
                        "  ---\n%s  ...\n",
                        $yaml->dump($diagnostic, 2, 2)
                    )
                );
            }

            /**
             * Incomplete test.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->writeNotOk($test, '', 'TODO Incomplete Test');
            }

            /**
             * Risky test.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->write(
                    sprintf(
                        "ok %d - # RISKY%s\n",
                        $this->testNumber,
                        $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
                    )
                );

                $this->testSuccessful = false;
            }

            /**
             * Skipped test.
             *
             * @param PHPUnit_Framework_Test $test
             * @param Exception $e
             * @param float $time
             */
            public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
            {
                $this->write(
                    sprintf(
                        "ok %d - # SKIP%s\n",
                        $this->testNumber,
                        $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
                    )
                );

                $this->testSuccessful = false;
            }

            /**
             * A testsuite started.
             *
             * @param PHPUnit_Framework_TestSuite $suite
             */
            public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
            {
                $this->testSuiteLevel++;
            }

            /**
             * A testsuite ended.
             *
             * @param PHPUnit_Framework_TestSuite $suite
             */
            public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
            {
                $this->testSuiteLevel--;

                if ($this->testSuiteLevel == 0) {
                    $this->write(sprintf("1..%d\n", $this->testNumber));
                }
            }

            /**
             * A test started.
             *
             * @param PHPUnit_Framework_Test $test
             */
            public function startTest(PHPUnit_Framework_Test $test)
            {
                $this->testNumber++;
                $this->testSuccessful = true;
            }

            /**
             * A test ended.
             *
             * @param PHPUnit_Framework_Test $test
             * @param float $time
             */
            public function endTest(PHPUnit_Framework_Test $test, $time)
            {
                if ($this->testSuccessful === true) {
                    $this->write(
                        sprintf(
                            "ok %d - %s\n",
                            $this->testNumber,
                            PHPUnit_Util_Test::describe($test)
                        )
                    );
                }

                $this->writeDiagnostics($test);
            }

            /**
             * @param PHPUnit_Framework_Test $test
             * @param string $prefix
             * @param string $directive
             */
            protected function writeNotOk(PHPUnit_Framework_Test $test, $prefix = '', $directive = '')
            {
                $this->write(
                    sprintf(
                        "not ok %d - %s%s%s\n",
                        $this->testNumber,
                        $prefix != '' ? $prefix . ': ' : '',
                        PHPUnit_Util_Test::describe($test),
                        $directive != '' ? ' # ' . $directive : ''
                    )
                );

                $this->testSuccessful = false;
            }

            /**
             * @param PHPUnit_Framework_Test $test
             */
            private function writeDiagnostics(PHPUnit_Framework_Test $test)
            {
                if (!$test instanceof PHPUnit_Framework_TestCase) {
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
    }
}
// @codingStandardsIgnoreEnd
