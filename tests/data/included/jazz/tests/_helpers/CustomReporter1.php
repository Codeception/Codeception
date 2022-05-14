<?php

namespace Jazz;

use Codeception\PHPUnit\ResultPrinter\Report;

final class CustomReporter1 extends Report
{
    public function endTest(\PHPUnit\Framework\Test $test, float $time) :void
    {
        $this->write('CustomReporter1');
    }
    
}