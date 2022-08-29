<?php

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Lib\Interfaces\ConsolePrinter;

class CustomReporter extends Extension implements ConsolePrinter
{
    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::TEST_START => 'startTest',
        Events::TEST_END   => 'endTest',
    ];

    public function startTest(TestEvent $event): void
    {
        $test = $event->getTest();
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nSTARTED: {$testName}\n");
    }

    public function endTest(TestEvent $event): void
    {
        $test = $event->getTest();
        $testName = \Codeception\Test\Descriptor::getTestAsString($test);
        $this->write("\nENDED: {$testName}\n");
    }
}
