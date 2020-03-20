<?php 
use Codeception\PHPUnit\ConsolePrinter;
use Codeception\PHPUnit\ResultPrinter;

if (\PHPUnit\Runner\Version::series() < 7) {
    class MyReportPrinter extends ResultPrinter implements ConsolePrinter
    {
        public function endTest(\PHPUnit\Framework\Test $test, $time)
        {
            $name = \Codeception\Test\Descriptor::getTestAsString($test);
            if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE) {
                $this->write('×');
            } else {
                if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_SKIPPED) {
                    $this->write('S');
                } else {
                    if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_INCOMPLETE) {
                        $this->write('I');
                    } else {
                        if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_ERROR) {
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

        public function printResult(\PHPUnit\Framework\TestResult $result)
        {

        }

    }
} else if (\PHPUnit\Runner\Version::series() < 9) {
    class MyReportPrinter extends ResultPrinter implements ConsolePrinter
    {
        public function endTest(\PHPUnit\Framework\Test $test, float $time) : void
        {
            $name = \Codeception\Test\Descriptor::getTestAsString($test);
            if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE) {
                $this->write('×');
            } else {
                if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_SKIPPED) {
                    $this->write('S');
                } else {
                    if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_INCOMPLETE) {
                        $this->write('I');
                    } else {
                        if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_ERROR) {
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

        public function printResult(\PHPUnit\Framework\TestResult $result)
        {

        }

    }
} else {
    class MyReportPrinter extends ResultPrinter implements ConsolePrinter
    {
        public function endTest(\PHPUnit\Framework\Test $test, float $time) : void
        {
            $name = \Codeception\Test\Descriptor::getTestAsString($test);
            if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE) {
                $this->write('×');
            } else {
                if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_SKIPPED) {
                    $this->write('S');
                } else {
                    if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_INCOMPLETE) {
                        $this->write('I');
                    } else {
                        if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_ERROR) {
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

        public function printResult(\PHPUnit\Framework\TestResult $result): void
        {

        }

    }
}
