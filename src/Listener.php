<?php
namespace Codeception\PHPUnit;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\TestInterface;
use Exception;
use PHPUnit\Framework\Test;
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
     * @param Exception $e
     * @param float $time
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(\PHPUnit\Framework\Test $test, Exception $e, $time)
    {
    }

    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_FAIL, new FailEvent($test, $time, $e));
    }

    public function addError(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_ERROR, new FailEvent($test, $time, $e));
    }

    // This method was added in PHPUnit 6
    public function addWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, $time)
    {
    }

    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
        if (in_array(spl_object_hash($test), $this->skippedTests)) {
            return;
        }
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_INCOMPLETE, new FailEvent($test, $time, $e));
        $this->skippedTests[] = spl_object_hash($test);
    }

    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
        if (in_array(spl_object_hash($test), $this->skippedTests)) {
            return;
        }
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_SKIPPED, new FailEvent($test, $time, $e));
        $this->skippedTests[] = spl_object_hash($test);
    }

    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        $this->dispatcher->dispatch('suite.start', new SuiteEvent($suite));
    }

    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        $this->dispatcher->dispatch('suite.end', new SuiteEvent($suite));
    }

    public function startTest(\PHPUnit\Framework\Test $test)
    {
        $this->dispatcher->dispatch(Events::TEST_START, new TestEvent($test));
        if (!$test instanceof TestInterface) {
            return;
        }
        if ($test->getMetadata()->isBlocked()) {
            return;
        }

        try {
            $this->startedTests[] = spl_object_hash($test);
            $this->fire(Events::TEST_BEFORE, new TestEvent($test));
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            $test->getTestResultObject()->addFailure($test, $e, 0);
        } catch (\PHPUnit\Framework\SkippedTestError $e) {
            $test->getTestResultObject()->addFailure($test, $e, 0);
        } catch (\Exception $e) {
            $test->getTestResultObject()->addError($test, $e, 0);
        }
    }

    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
        $hash = spl_object_hash($test);
        if (!in_array($hash, $this->unsuccessfulTests)) {
            $this->fire(Events::TEST_SUCCESS, new TestEvent($test, $time));
        }
        if (in_array($hash, $this->startedTests)) {
            $this->fire(Events::TEST_AFTER, new TestEvent($test, $time));
        }

        $this->dispatcher->dispatch(Events::TEST_END, new TestEvent($test, $time));
    }

    protected function fire($event, TestEvent $eventType)
    {
        $test = $eventType->getTest();
        if ($test instanceof TestInterface) {
            foreach ($test->getMetadata()->getGroups() as $group) {
                $this->dispatcher->dispatch($event . '.' . $group, $eventType);
            }
        }
        $this->dispatcher->dispatch($event, $eventType);
    }
}
