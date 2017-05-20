<?php

class CustomReporter extends PHPUnit_TextUI_ResultPrinter
{
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nSTARTED: $testName\n");
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nENDED: $testName\n");
    }
}