<?php
namespace Codeception\PHPUnit\Log;

use Codeception\Configuration;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class PhpUnit extends \PHPUnit\Util\Log\JUnit
{
    const SUITE_LEVEL = 1;
    const FILE_LEVEL  = 2;

    protected $strictAttributes = ['file', 'name', 'class'];

    private $currentFile;
    private $currentFileSuite;

    public function startTest(\PHPUnit\Framework\Test $test):void
    {
        if (method_exists($test, 'getFileName') ) {
            $filename = $test->getFileName();
        } else {
            $reflector = new \ReflectionClass($test);
            $filename = $reflector->getFileName();
        }

        if ($filename !== $this->currentFile) {
            if ($this->currentFile !== null) {
                parent::endTestSuite(new TestSuite());
            }

            //initialize all values to avoid warnings
            $this->testSuiteAssertions[self::FILE_LEVEL] = 0;
            $this->testSuiteTests[self::FILE_LEVEL]      = 0;
            $this->testSuiteTimes[self::FILE_LEVEL]      = 0;
            $this->testSuiteErrors[self::FILE_LEVEL]     = 0;
            $this->testSuiteFailures[self::FILE_LEVEL]   = 0;
            $this->testSuiteSkipped[self::FILE_LEVEL]    = 0;

            $this->testSuiteLevel = self::FILE_LEVEL;

            $this->currentFile = $filename;
            $this->currentFileSuite = $this->document->createElement('testsuite');

            if ($test instanceof Reported) {
                $reportFields = $test->getReportFields();
                $class = isset($reportFields['class']) ? $reportFields['class'] : $reportFields['name'];
                $this->currentFileSuite->setAttribute('name', $class);
            } else {
                $this->currentFileSuite->setAttribute('name', get_class($test));
            }

            $this->currentFileSuite->setAttribute('file', $filename);

            $this->testSuites[self::SUITE_LEVEL]->appendChild($this->currentFileSuite);
            $this->testSuites[self::FILE_LEVEL] = $this->currentFileSuite;
        }

        if (!$test instanceof Reported) {
            parent::startTest($test);
            return;
        }

        $this->currentTestCase = $this->document->createElement('testcase');

        $isStrict = Configuration::config()['settings']['strict_xml'];

        foreach ($test->getReportFields() as $attr => $value) {
            if ($isStrict and !in_array($attr, $this->strictAttributes)) {
                continue;
            }
            $this->currentTestCase->setAttribute($attr, $value);
        }
    }

    public function endTest(\PHPUnit\Framework\Test $test, float $time):void
    {
        if ($this->currentTestCase !== null && $test instanceof Test) {
            $numAssertions = $test->getNumAssertions();
            $this->testSuiteAssertions[$this->testSuiteLevel] += $numAssertions;

            $this->currentTestCase->setAttribute(
                'assertions',
                $numAssertions
            );
        }

        if ($test instanceof TestCase) {
            parent::endTest($test, $time);
            return;
        }

        // In PhpUnit 7.4.*, parent::endTest ignores tests that aren't instances of TestCase
        // so I copied this code from PhpUnit 7.3.5

        $this->currentTestCase->setAttribute(
            'time',
            \sprintf('%F', $time)
        );
        $this->testSuites[$this->testSuiteLevel]->appendChild(
            $this->currentTestCase
        );
        $this->testSuiteTests[$this->testSuiteLevel]++;
        $this->testSuiteTimes[$this->testSuiteLevel] += $time;
        $this->currentTestCase = null;
    }

    /**
     * Cleans the mess caused by test suite manipulation in startTest
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if ($suite->getName()) {
            if ($this->currentFile) {
                //close last file in the test suite
                parent::endTestSuite(new TestSuite());
                $this->currentFile = null;
            }
            $this->testSuiteLevel = self::SUITE_LEVEL;
        }
        parent::endTestSuite($suite);
    }
}
