<?php
namespace Codeception\PHPUnit;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\TestCase as CodeceptionTestCase;
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
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_INCOMPLETE, new FailEvent($test, $e));
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_SKIPPED, new FailEvent($test, $e));
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
        if (!$test instanceof CodeceptionTestCase) {
            return;
        }
        try {
            $test->getScenario()->stopIfBlocked();
        } catch (\PHPUnit_Framework_IncompleteTestError $e) {
            return;
        } catch (\PHPUnit_Framework_SkippedTestError $e) {
            return;
        }
        $this->startedTests[] = spl_object_hash($test);
        $this->fire(Events::TEST_BEFORE, new TestEvent($test));
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $hash = spl_object_hash($test);
        if (!in_array($hash, $this->unsuccessfulTests)) {
            $this->fire(Events::TEST_SUCCESS, new TestEvent($test));
        }
        if (in_array($hash, $this->startedTests) and ($test instanceof CodeceptionTestCase)) {
            $this->fire(Events::TEST_AFTER, new TestEvent($test));
        }

        $this->dispatcher->dispatch(Events::TEST_END, new TestEvent($test, $time));
    }

    protected function fire($event, TestEvent $eventType)
    {
        $test = $eventType->getTest();
        if ($test instanceof CodeceptionTestCase) {
            foreach ($test->getScenario()->getGroups() as $group) {
                $this->dispatcher->dispatch($event . '.' . $group, $eventType);
            }
        }
        $this->dispatcher->dispatch($event, $eventType);
    }
}
