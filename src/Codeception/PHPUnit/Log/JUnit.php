<?php
namespace Codeception\PHPUnit\Log;

class JUnit extends \PHPUnit_Util_Log_JUnit
{
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!($test instanceof \Codeception\TestCase\Cept)) return parent::startTest($test);


        $this->currentTestCase = $this->document->createElement('testcase');

        if ($test instanceof \Codeception\TestCase) {
            $class = new \ReflectionClass($test->getTestClass());
            $methodName = $test->getTestMethod();

            if ($class->hasMethod($methodName)) {
                $method = $class->getMethod($methodName);

                $this->currentTestCase->setAttribute('class', $class->getName());
                $this->currentTestCase->setAttribute('file', $class->getFileName());
                $this->currentTestCase->setAttribute('line', $method->getStartLine());
            }
        }
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time) {

        if ($test instanceof \Codeception\TestCase\Cept) {
            $this->currentTestCase->setAttribute(
              'name', htmlspecialchars($test->toString())
            );
        }
        return parent::endTest($test, $time);
    }
}
