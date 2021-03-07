<?php
namespace Codeception\PHPUnit\ResultPrinter;

use Codeception\PHPUnit\ConsolePrinter;
use Codeception\PHPUnit\ResultPrinter;
use Codeception\Test\Descriptor;
use PHPUnit\Runner\BaseTestRunner;

class Report extends ResultPrinter implements ConsolePrinter
{
    /**
     * @param \PHPUnit\Framework\Test $test
     * @param float $time
     */
    public function endTest(\PHPUnit\Framework\Test $test, float $time) : void
    {
        $name = Descriptor::getTestAsString($test);
        if (class_exists(BaseTestRunner::class)) {
            // PHPUnit 9
            $success = $this->testStatus == BaseTestRunner::STATUS_PASSED;
        } else {
            // PHPUnit 10
            $success = $this->testStatus->isSuccess();
        }
        if ($success) {
            $this->successful++;
        }

        if (class_exists(BaseTestRunner::class)) {
            // PHPUnit 9
            if ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE) {
                $status = "\033[41;37mFAIL\033[0m";
            } elseif ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_SKIPPED) {
                $status = 'Skipped';
            } elseif ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_INCOMPLETE) {
                $status = 'Incomplete';
            } elseif ($this->testStatus == \PHPUnit\Runner\BaseTestRunner::STATUS_ERROR) {
                $status = 'ERROR';
            } else {
                $status = 'Ok';
            }
        } else {
            // PHPUnit 10
            if ($this->testStatus->isFailure()) {
                $status = "\033[41;37mFAIL\033[0m";
            } elseif ($this->testStatus->isSkipped()) {
                $status = 'Skipped';
            } elseif ($this->testStatus->isIncomplete()) {
                $status = 'Incomplete';
            } elseif ($this->testStatus->isError()) {
                $status = 'ERROR';
            } else {
                $status = 'Ok';
            }
        }

        if (strlen($name) > 75) {
            $name = substr($name, 0, 70);
        }
        $line = $name . str_repeat('.', 75 - strlen($name));
        $line .= $status;

        $this->write($line . "\n");
    }

    protected function endRun() : void
    {
        $this->write("\nCodeception Results\n");
        $this->write(sprintf(
            "Successful: %s. Failed: %s. Incomplete: %s. Skipped: %s",
            $this->successful,
            $this->failed,
            $this->skipped,
            $this->incomplete
        ) . "\n");
    }

    public function printResult(\PHPUnit\Framework\TestResult $result): void
    {
    }

    public function write(string $buffer) : void
    {
        parent::write($buffer);
    }
}
