<?php
namespace Codeception\PHPUnit;

class ResultPrinter extends \PHPUnit_Util_TestDox_ResultPrinter
{

    /**
     * A test ended.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  float $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $steps = [];
        if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
            $this->successful++;
            $success = true;
            if ($test instanceof \Codeception\TestCase) {
                $steps = $test->getScenario()->getSteps();
            }
        } else {
            $success = false;
            if ($test instanceof \Codeception\TestCase) {
                $steps = $test->getTrace();
            }
        }

        $this->onTest($test->toString(), $success, $steps, $time);
    }

}
