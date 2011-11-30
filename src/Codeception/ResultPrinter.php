<?php
namespace Codeception;

class ResultPrinter extends \PHPUnit_Util_TestDox_ResultPrinter
{

	protected $testTypeOfInterest = '\Codeception\TestCase';

    /**
     * A test ended.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {

	    if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
            $this->successful++;
            $success = TRUE;
		    $steps = $test->getScenario()->getSteps();
        } else {
            $success = FALSE;
		    $steps = $test->getTrace();
        }

        $this->onTest(
            $test->getScenario()->getFeature(),
            $success,
            $steps,
	        $time
        );
    }



}
