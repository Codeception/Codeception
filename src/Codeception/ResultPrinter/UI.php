<?php
namespace Codeception\ResultPrinter;

class UI extends \PHPUnit_TextUI_ResultPrinter {

	protected $output;
	protected $traceLength = 5;

	function __construct($out = NULL, $verbose = FALSE, $colors = FALSE, $debug = FALSE) {
		parent::__construct($out, $verbose, $colors, $debug);
		$this->output = new \Codeception\Output(!$verbose, $colors);
	}

	protected function printDefectHeader(\PHPUnit_Framework_TestFailure $defect, $count)
	{
	    $failedTest = $defect->failedTest();

		$feature = strtolower($failedTest->getScenario()->getFeature());
		$this->output->put("\n$count) ((Couldn't $feature)) ({$failedTest->getFilename()})\n");

	}

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!($test instanceof \Codeception\TestCase)) return parent::startTest($test);
    }

    /**
     * A test ended.
     *
     * @param  \PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if (!$this->lastTestFailed) {
            $this->writeProgress($this->verbose ? '- ok':'.');
        }

        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        }

        $this->lastTestFailed = FALSE;
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeProgress($this->verbose ? '- fail':'F');
        $this->lastTestFailed = TRUE;
    }



	protected function printDefectTrace(\PHPUnit_Framework_TestFailure $defect)
	{

		$failedTest = $defect->failedTest();
		$trace = array_reverse($failedTest->getTrace());
		$length = $i = count($trace);
		$last = array_shift($trace);
        if (!method_exists($last,'getHumanizedAction')) {
            $this->output->put("\n ".$defect->getExceptionAsString());
            return;
        }
		$action = $last->getHumanizedAction();
        if (strpos($action, "am")===0) {
            $action = 'become'.substr($action,2);
        }
		if (strpos($action, "don't")===0) {
			$action = substr($action, 6);
			$this->output->put("\nGuy unexpectedly managed to $action {$defect->getExceptionAsString()}");
		} else {
			$this->output->put("\nGuy coudn't $action {$defect->getExceptionAsString()}");
		}

		$this->output->put("\n  $i. (!$last!)");
		foreach ($trace as $step) {
			$i--;
		    $this->output->put("\n  $i. ".$step);
			if (($length - $i - 1) >= $this->traceLength) break;
		}
		$this->writeNewLine();
        if ($this->debug) {
            $this->printException($last->getAction(), $defect->thrownException());
        }
	}

    public function printException($action, \Exception $e) {
        $i = 0;

        $this->output->put("\n  ((Stack trace:))");
        foreach ($e->getTrace() as $step) {
            $i++;
            if (strpos($step['function'], $action) !== false) break;
            $this->output->put(sprintf("\n   #%s ((%s)) %s:%s",
                                       $i,
                                       isset($step['function']) ? $step['function'] : '',
                                       isset($step['file']) ? $step['file'] : '',
                                       isset($step['line']) ? $step['line'] : ''));
            if ($i == 1) {
                if (isset($step['arguments'])) {
                    if (count($step['arguments'])) {
                        $this->output->put("\n        ((Arguments:))");
                        foreach ($step['args'] as $arg) {
                            $this->output->put("\n            ".json_encode($arg).",");
                        }
                    }
                }
            }
        }
        $this->writeNewLine();
    }


}
