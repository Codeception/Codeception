<?php

namespace Codeception\Reporter;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Console\Output;
use Codeception\Lib\Interfaces\ConsolePrinter;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Codeception\Test\Descriptor;
use Codeception\Test\Test;

class ReportPrinter implements ConsolePrinter
{
    use StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::TEST_SUCCESS       => 'testSuccess',
        Events::TEST_FAIL          => 'testFailure',
        Events::TEST_ERROR         => 'testError',
        Events::TEST_INCOMPLETE    => 'testIncomplete',
        Events::TEST_SKIPPED       => 'testSkipped',
        Events::TEST_WARNING       => 'testWarning',
        Events::TEST_USELESS       => 'testUseless',
        Events::RESULT_PRINT_AFTER => 'afterResult',
    ];

    private Output $output;
    private int $successfulCount = 0;
    private int $errorCount = 0;
    private int $failureCount = 0;
    private int $warningCount = 0;
    private int $skippedCount = 0;
    private int $incompleteCount = 0;
    private int $uselessCount = 0;

    public function __construct(array $options)
    {
        $this->output = new Output($options);
    }

    private function message(string $string = ''): Message
    {
        return $this->output->message($string);
    }

    public function testSuccess(TestEvent $event): void
    {
        $this->printTestResult($event->getTest(), 'Ok');
        $this->successfulCount++;
    }

    public function testError(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), 'ERROR');
        $this->errorCount++;
    }

    public function testFailure(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), "\033[41;37mFAIL\033[0m");
        $this->failureCount++;
    }

    public function testWarning(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), 'WARNING');
        $this->warningCount++;
    }

    public function testSkipped(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), 'Skipped');
        $this->skippedCount++;
    }

    public function testIncomplete(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), 'Incomplete');
        $this->incompleteCount++;
    }

    public function testUseless(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), 'Useless');
        $this->uselessCount++;
    }

    private function printTestResult(Test $test, string $status): void
    {
        $name = Descriptor::getTestAsString($test);
        if (strlen($name) > 75) {
            $name = substr($name, 0, 70);
        }

        $this->message($name)
            ->width(75, '.')
            ->append($status)
            ->writeln();
    }

    public function afterResult(PrintResultEvent $event): void
    {
        $counts = [
            sprintf("Successful: %s", $this->successfulCount)
        ];

        $failedCount = $this->errorCount + $this->errorCount + $this->warningCount;

        if ($failedCount > 0) {
            $counts [] = sprintf("Failed: %s", $failedCount);
        }
        if ($this->incompleteCount > 0) {
            $counts [] = sprintf("Incomplete: %s", $this->incompleteCount);
        }
        if ($this->skippedCount > 0) {
            $counts [] = sprintf("Skipped: %s", $this->skippedCount);
        }
        if ($this->uselessCount > 0) {
            $counts [] = sprintf("Useless: %s", $this->uselessCount);
        }

        $this->output->writeln("\nCodeception Results");
        $this->output->writeln(implode('. ', $counts) . '.');
    }
}
