<?php
use Codeception\PHPUnit\ConsolePrinter;
use Codeception\PHPUnit\ResultPrinter;
use PHPUnit\Runner\BaseTestRunner;

class MyReportPrinter extends ResultPrinter implements ConsolePrinter
{
    public function endTest(\PHPUnit\Framework\Test $test, float $time): void
    {
        $name = \Codeception\Test\Descriptor::getTestAsString($test);
        if ($this->testStatus == BaseTestRunner::STATUS_FAILURE) {
            $this->write('×');
        } elseif ($this->testStatus == BaseTestRunner::STATUS_SKIPPED) {
            $this->write('S');
        } elseif ($this->testStatus == BaseTestRunner::STATUS_INCOMPLETE) {
            $this->write('I');
        } elseif ($this->testStatus == BaseTestRunner::STATUS_ERROR) {
            $this->write('E');
        } else {
            $this->write('✔');
        }

        if (strlen($name) > 75) {
            $name = substr($name, 0, 70);
        }

        $this->write(" {$name} \n");
    }

    public function printResult(\PHPUnit\Framework\TestResult $result): void
    {

    }

}
