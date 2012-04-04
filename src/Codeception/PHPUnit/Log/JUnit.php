<?php
namespace Codeception\PHPUnit\Log;

class JUnit extends \PHPUnit_Util_Log_JUnit
{
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!($test instanceof \Codeception\TestCase)) return parent::startTest($test);


        $testCase = $this->document->createElement('testcase');

        if ($test instanceof \Codeception\TestCase\Cept) {
            $testCase->setAttribute('file', $test->getFileName());
        }

        if ($test instanceof \Codeception\TestCase\Cest) {
            $class = new \ReflectionClass($test->getTestClass());
            $methodName = $test->getTestMethod();

            if ($class->hasMethod($methodName)) {
                $method = $class->getMethod($methodName);

                $testCase->setAttribute('class', $class->getName());
                $testCase->setAttribute('file', $class->getFileName());
                $testCase->setAttribute('line', $method->getStartLine());
            }
        }

        $this->currentTestCase = $testCase;
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time) {

        if ($test instanceof \Codeception\TestCase) {
            $this->currentTestCase->setAttribute(
              'name', $test->toString()
            );
        }
        return parent::endTest($test, $time);
    }
}
