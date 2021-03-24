<?php
namespace Codeception\PHPUnit\ResultPrinter;

use Codeception\PHPUnit\Compatibility\PHPUnit9;
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
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $success = $this->testStatus == BaseTestRunner::STATUS_PASSED;
        } else {
            $success = $this->testStatus->isSuccess();
        }
        if ($success) {
            $this->successful++;
        }

        if (PHPUnit9::baseTestRunnerClassExists()) {
            switch ($this->testStatus) {
                case BaseTestRunner::STATUS_ERROR:
                    $status = 'ERROR';
                    break;
                case BaseTestRunner::STATUS_FAILURE:
                    $status = "\033[41;37mFAIL\033[0m";
                    break;
                case BaseTestRunner::STATUS_SKIPPED:
                    $status = 'Skipped';
                    break;
                case BaseTestRunner::STATUS_INCOMPLETE:
                    $status = 'Incomplete';
                    break;
                default:
                    $status = 'Ok';
            }
        } else {
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
