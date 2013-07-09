<?php
namespace Codeception\PHPUnit;

use Codeception\Event\Test;
use Codeception\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Listener implements \PHPUnit_Framework_TestListener
{

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    protected $unsuccessfulTests = array();

    public function __construct(EventDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time) {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire('test.fail', new \Codeception\Event\Fail($test, $e));
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire('test.error', new \Codeception\Event\Fail($test, $e));
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire('test.incomplete', new \Codeception\Event\Fail($test, $e));
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->fire('test.skipped', new \Codeception\Event\Fail($test, $e));
    }

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->dispatcher->dispatch('suite.start', new \Codeception\Event\Suite($suite));        
    }
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite) {
        $this->dispatcher->dispatch('suite.end', new \Codeception\Event\Suite($suite));
    }

    public function startTest(\PHPUnit_Framework_Test $test) {
        $this->fire('test.before', new Test($test));
        $this->dispatcher->dispatch('test.start', new \Codeception\Event\Test($test));
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time) {
        if (!in_array(spl_object_hash($test), $this->unsuccessfulTests))
            $this->fire('test.success', new Test($test));

        $this->fire('test.after', new Test($test, $time));
        $this->dispatcher->dispatch('test.end', new Test($test, $time));
    }

    protected function fire($event, \Codeception\Event\Test $eventType)
    {
        $test = $eventType->getTest();
        if ($test instanceof TestCase) {
            foreach ($test->getScenario()->getGroups() as $group) {
                $this->dispatcher->dispatch($event.'.'.$group, $eventType);
            }
        }
        $this->dispatcher->dispatch($event, $eventType);

    }
}
