<?php
namespace Codeception\PHPUnit\Log;

use Codeception\Configuration;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Test;
use PHPUnit\Framework\TestCase;

class JUnit extends \Codeception\PHPUnit\NonFinal\JUnit
{
    protected $strictAttributes = ['file', 'name', 'class'];

    public function startTest(\PHPUnit\Framework\Test $test):void
    {
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
        if ($this->currentTestCase !== null and $test instanceof Test) {
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

        // since PhpUnit 7.4.0, parent::endTest ignores tests that aren't instances of TestCase
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
}
