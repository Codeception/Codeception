<?php
namespace Codeception\PHPUnit;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\TestInterface;
use PHPUnit\Framework\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Listener implements \PHPUnit\Framework\TestListener
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    protected $unsuccessfulTests = [];
    protected $skippedTests = [];
    protected $startedTests = [];

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Risky test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param \Throwable $e
     * @param float $time
     */
    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
    }

    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, float $time) : void
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_FAIL, new FailEvent($test, $time, $e));
    }

    public function addError(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_ERROR, new FailEvent($test, $time, $e));
    }

    public function addWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time) : void
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

    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite) : void
    {
        $this->dispatcher->dispatch(new SuiteEvent($suite), 'suite.start');
    }

    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite) : void
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
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            if ($testResult !== null) {
                $testResult->addFailure($test, $e, 0);
            }
        } catch (\PHPUnit\Framework\SkippedTestError $e) {
            if ($testResult !== null) {
                $testResult->addFailure($test, $e, 0);
            }
        } catch (\Throwable $e) {
            if ($testResult !== null) {
                $testResult->addError($test, $e, 0);
            }
        }
    }

    public function endTest(\PHPUnit\Framework\Test $test, float $time) : void
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
