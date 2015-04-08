<?php 
use Codeception\PHPUnit\ConsolePrinter;
use Codeception\PHPUnit\ResultPrinter;

class MyReportPrinter extends ResultPrinter implements ConsolePrinter
{
    protected function onTest($name, $success = true, array $steps = [], $time = 0)
    {
        if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
            $this->write('×');
        } else {
            if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED) {
                $this->write('S');
            } else {
                if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
                    $this->write('I');
                } else {
                    if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR) {
                        $this->write('E');
                    } else {
                        $this->write('✔');
                    }
                }
            }
        }

        if (strlen($name) > 75) {
            $name = substr($name, 0, 70);
        }
        $this->write(" $name \n");
    }

    public function printResult(\PHPUnit_Framework_TestResult $result)
    {
        
    }

}