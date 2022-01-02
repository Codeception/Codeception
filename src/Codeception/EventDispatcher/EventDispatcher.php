<?php

namespace Codeception\EventDispatcher;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\EventDispatcher\Subscriber\TestBeforeTestMethodCalledSubscriber;
use Codeception\EventDispatcher\Subscriber\TestErroredSubscriber;
use Codeception\EventDispatcher\Subscriber\TestFailedSubscriber;
use Codeception\EventDispatcher\Subscriber\TestFinishedSubscriber;
use Codeception\EventDispatcher\Subscriber\TestPassedWithWarningSubscriber;
use Codeception\EventDispatcher\Subscriber\TestSuiteFinishedSubscriber;
use Codeception\EventDispatcher\Subscriber\TestSuiteStartedSubscriber;
use Codeception\Events;
use Codeception\TestInterface;
use PHPUnit\Event\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\TestResult;
use PHPUnit\Logging\JUnit\AssertionMadeSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

class EventDispatcher
{
    protected array $unsuccessfulTests = [];
    protected array $skippedTests = [];
    protected array $startedTests = [];

    public function __construct(private SymfonyEventDispatcher $dispatcher)
    {
    }

    public function registerSubscribers(): void
    {
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestBeforeTestMethodCalledSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestSuiteStartedSubscriber($this));
        Facade::registerSubscriber(new TestSuiteFinishedSubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));

//        Facade::registerSubscriber(new TestPreparedSubscriber($this));
//        Facade::registerSubscriber(new TestPrintedOutputSubscriber($this));



//        Facade::registerSubscriber(new TestAbortedSubscriber($this));
//        Facade::registerSubscriber(new TestSkippedSubscriber($this));

    }

    public function testFailed(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, float $time) : void
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_FAIL, new FailEvent($test, $time, $e));
    }

    public function testErrored(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_ERROR, new FailEvent($test, $time, $e));
    }

    public function testPassedWithWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time) : void
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_WARNING, new FailEvent($test, $time, $e));
    }

    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        if (in_array(spl_object_hash($test), $this->skippedTests)) {
            return;
        }
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_INCOMPLETE, new FailEvent($test, $time, $e));
        $this->skippedTests[] = spl_object_hash($test);
    }

    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        if (in_array(spl_object_hash($test), $this->skippedTests)) {
            return;
        }
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_SKIPPED, new FailEvent($test, $time, $e));
        $this->skippedTests[] = spl_object_hash($test);
    }

    public function testSuiteStarted(\PHPUnit\Framework\TestSuite $suite) : void
    {
        $this->dispatcher->dispatch(new SuiteEvent($suite), 'suite.start');
    }

    public function testSuiteEnded(\PHPUnit\Framework\TestSuite $suite) : void
    {
        $this->dispatcher->dispatch(new SuiteEvent($suite), 'suite.end');
    }

    public function startTest(\PHPUnit\Framework\Test $test) : void
    {
        $this->dispatcher->dispatch(new TestEvent($test), Events::TEST_START);
        if (!$test instanceof TestInterface) {
            return;
        }
        if ($test->getMetadata()->isBlocked()) {
            return;
        }

        /**
         * @var $testResult TestResult
         */
        if (method_exists($test, 'getTestResultObject')) {
            // PHPUnit 9 or Codeception's own test types
            $testResult = $test->getTestResultObject();
        } else {
            // PHPUnit\Framework\TestCase since PHPUnit 10
            // unavailable before test is executed
            $testResult = $test->result();
        }

        try {
            $this->startedTests[] = spl_object_hash($test);
            $this->fire(Events::TEST_BEFORE, new TestEvent($test));
        } catch (\PHPUnit\Framework\IncompleteTestError | \PHPUnit\Framework\IncompleteTest $e) {
            if ($testResult !== null) {
                $testResult->addFailure($test, $e, 0);
            }
        } catch (\PHPUnit\Framework\SkippedTestError | \PHPUnit\Framework\Exception\Skipped\SkippedTest $e) {
            if ($testResult !== null) {
                $testResult->addFailure($test, $e, 0);
            }
        } catch (\Throwable $e) {
            if ($testResult !== null) {
                $testResult->addError($test, $e, 0);
            }
        }
    }

    public function testFinished(\PHPUnit\Framework\Test $test, float $time) : void
    {
        $hash = spl_object_hash($test);
        if (!in_array($hash, $this->unsuccessfulTests)) {
            $this->fire(Events::TEST_SUCCESS, new TestEvent($test, $time));
        }
        if (in_array($hash, $this->startedTests)) {
            $this->fire(Events::TEST_AFTER, new TestEvent($test, $time));
        }

        $this->dispatcher->dispatch(new TestEvent($test, $time), Events::TEST_END);
    }

    protected function fire($eventType, TestEvent $event)
    {
        $test = $event->getTest();
        if ($test instanceof TestInterface) {
            foreach ($test->getMetadata()->getGroups() as $group) {
                $this->dispatcher->dispatch($event, $eventType . '.' . $group);
            }
        }
        $this->dispatcher->dispatch($event, $eventType);
    }
}