<?php
namespace Codeception\PHPUnit;

use Codeception\TestCase\Interfaces\ScenarioDriven;

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
        $success = ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED);
        if ($success) {
            $this->successful++;
        }

        if ($test instanceof ScenarioDriven) {
            $steps = $test->getScenario()->getSteps();
        }

        $this->onTest($test->toString(), $success, $steps, $time);
    }

}
