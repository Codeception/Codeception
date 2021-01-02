<?php

use Codeception\PHPUnit\ResultPrinter;

class CustomReporter extends ResultPrinter
{
    public function startTest(PHPUnit\Framework\Test $test): void
    {
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nSTARTED: $testName\n");
    }

    public function endTest(PHPUnit\Framework\Test $test, float $time): void
    {
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nENDED: $testName\n");
    }
}