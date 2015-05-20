<?php
namespace Codeception\PhpUnit\ResultPrinter;

class Report extends \Codeception\PHPUnit\ResultPrinter
{

    /**
     * Handler for 'on test' event.
     *
     * @param  string  $name
     * @param  boolean $success
     * @param  array   $steps
     */
    protected function onTest($name, $success = TRUE, array $steps = array(), $time = 0)
    {
        if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
            $status = "\033[41;37mFAIL\033[0m";
        }

        else if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED) {
            $status = 'Skipped';
        }

        else if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
            $status = 'Incomplete';
        } else if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR) {
            $status = 'ERROR';
        } else {
			$status = 'Ok';
        }

	    if (strlen($name) > 75) $name = substr($name, 0, 70);
	    $line = $name . str_repeat('.', 75 - strlen($name));
	    $line .= $status;

	    $this->write($line."\n");
    }

	protected function endRun()
	{
		$this->write("\nCodeception Results\n");
		$this->write(sprintf("Successful: %s. Failed: %s. Incomplete: %s. Skipped: %s", $this->successful, $this->failed, $this->skipped, $this->incomplete)."\n");
	}

    public function printResult($res) {

    }

}
