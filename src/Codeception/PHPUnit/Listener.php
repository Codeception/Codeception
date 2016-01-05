<?php
namespace Codeception\PHPUnit;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Testable;
use Exception;
use PHPUnit_Framework_Test;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Listener implements \PHPUnit_Framework_TestListener
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
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_FAIL, new FailEvent($test, $e));
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_ERROR, new FailEvent($test, $e));
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if (in_array($test, $this->skippedTests)) {
            return;
        }
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_INCOMPLETE, new FailEvent($test, $e));
        $this->skippedTests[] = $test;
        $test->getTestResultObject()->addError($test, $e, $time);
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if (in_array(spl_object_hash($test), $this->skippedTests)) {
            return;
        }
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_SKIPPED, new FailEvent($test, $e));
        $this->skippedTests[] = spl_object_hash($test);
        $test->getTestResultObject()->addError($test, $e, $time);
    }

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->dispatcher->dispatch('suite.start', new SuiteEvent($suite));
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->dispatcher->dispatch('suite.end', new SuiteEvent($suite));
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->dispatcher->dispatch(Events::TEST_START, new TestEvent($test));
        if (!$test instanceof Testable) {
            return;
        }
        if ($test->getMetadata()->getSkip() !== null) {
            $this->addSkippedTest($test, new \PHPUnit_Framework_SkippedTestError((string)$test->getMetadata()->getSkip()), 0);
            return;
        }
        if ($test->getMetadata()->getIncomplete() !== null) {
            $this->addIncompleteTest($test, new \PHPUnit_Framework_IncompleteTestError((string)$test->getMetadata()->getIncomplete()), 0);
            return;
        }

        try {
            $this->startedTests[] = spl_object_hash($test);
            $this->fire(Events::TEST_BEFORE, new TestEvent($test));
        } catch (\PHPUnit_Framework_IncompleteTestError $e) {
            $this->addIncompleteTest($test, $e, 0);
        } catch (\PHPUnit_Framework_SkippedTestError $e) {
            $this->addSkippedTest($test, $e, 0);
        }
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
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
        if ($test instanceof Testable) {
            foreach ($test->getMetadata()->getGroups() as $group) {
                $this->dispatcher->dispatch($event . '.' . $group, $eventType);
            }
        }
        $this->dispatcher->dispatch($event, $eventType);
    }
}
