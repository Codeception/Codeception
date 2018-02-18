<?php
namespace Codeception\PHPUnit\Log;

use Codeception\Configuration;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Test;

class JUnit extends \PHPUnit\Util\Log\JUnit
{
    protected $strictAttributes = ['file', 'name', 'class'];

    public function startTest(\PHPUnit\Framework\Test $test)
    {
        if (!$test instanceof Reported) {
            return parent::startTest($test);
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

    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
        if ($this->currentTestCase !== null and $test instanceof Test) {
            $numAssertions = $test->getNumAssertions();
            $this->testSuiteAssertions[$this->testSuiteLevel] += $numAssertions;

            $this->currentTestCase->setAttribute(
                'assertions',
                $numAssertions
            );
        }
        parent::endTest($test, $time);
    }
}
