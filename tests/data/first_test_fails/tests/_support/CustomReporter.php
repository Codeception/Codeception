<?php

class CustomReporter extends PHPUnit\TextUI\ResultPrinter
{
    public function startTest(PHPUnit\Framework\Test $test)
    {
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nSTARTED: $testName\n");
    }

    public function endTest(PHPUnit\Framework\Test $test, $time)
    {
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nENDED: $testName\n");
    }
}