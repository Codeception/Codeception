<?php

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Interfaces\ConsolePrinter;
use Codeception\Test\Descriptor;
use Codeception\Test\Test;

class MyReportPrinter extends \Codeception\Extension implements ConsolePrinter
{
    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::TEST_SUCCESS       => 'testSuccess',
        Events::TEST_FAIL          => 'testFailure',
        Events::TEST_ERROR         => 'testError',
        Events::TEST_WARNING       => 'testWarning',
        Events::TEST_INCOMPLETE    => 'testSkipped',
        Events::TEST_SKIPPED       => 'testIncomplete',
    ];

    public function testSuccess(TestEvent $event): void
    {
        $this->write('✔');
        $this->printTestName($event->getTest());
    }

    public function testError(FailEvent $event): void
    {
        $this->write('E');
        $this->printTestName($event->getTest());
    }

    public function testFailure(FailEvent $event): void
    {
        $this->write('×');
        $this->printTestName($event->getTest());
    }

    public function testWarning(FailEvent $event): void
    {
        $this->write('W');
        $this->printTestName($event->getTest());
    }

    public function testSkipped(FailEvent $event): void
    {
        $this->write('S');
        $this->printTestName($event->getTest());
    }

    public function testIncomplete(FailEvent $event): void
    {
        $this->write('I');
        $this->printTestName($event->getTest());
    }

    private function printTestName(Test $test)
    {
        $name = Descriptor::getTestAsString($test);
        if (strlen($name) > 75) {
            $name = substr($name, 0, 70);
        }

        $this->output->writeln(" {$name} ");
    }
}
