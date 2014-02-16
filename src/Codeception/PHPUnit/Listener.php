<?php
namespace Codeception\PHPUnit;

use Codeception\CodeceptionEvents;
use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Listener implements \PHPUnit_Framework_TestListener
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    protected $unsuccessfulTests = array();

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(CodeceptionEvents::TEST_FAIL, new FailEvent($test, $e));
        $this->fire(CodeceptionEvents::TEST_AFTER, new TestEvent($test, $time));
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(CodeceptionEvents::TEST_ERROR, new FailEvent($test, $e));
        $this->fire(CodeceptionEvents::TEST_AFTER, new TestEvent($test, $time));
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(CodeceptionEvents::TEST_INCOMPLETE, new FailEvent($test, $e));
        $this->fire(CodeceptionEvents::TEST_AFTER, new TestEvent($test, $time));
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire(CodeceptionEvents::TEST_SKIPPED, new FailEvent($test, $e));
        $this->fire(CodeceptionEvents::TEST_AFTER, new TestEvent($test, $time));
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
        $this->dispatcher->dispatch(CodeceptionEvents::TEST_START, new TestEvent($test));
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if (! in_array(spl_object_hash($test), $this->unsuccessfulTests)) {
            $this->fire(CodeceptionEvents::TEST_SUCCESS, new TestEvent($test));
        }

        $this->dispatcher->dispatch(CodeceptionEvents::TEST_END, new TestEvent($test, $time));
    }

    protected function fire($event, TestEvent $eventType)
    {
        $test = $eventType->getTest();
        if ($test instanceof TestCase) {
            foreach ($test->getScenario()->getGroups() as $group) {
                $this->dispatcher->dispatch($event . '.' . $group, $eventType);
            }
        }
        $this->dispatcher->dispatch($event, $eventType);
    }
}
