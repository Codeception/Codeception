<?php
namespace Codeception\PHPUnit;

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
        $this->dispatcher->dispatch('test.fail', new \Codeception\Event\Fail($test, $e));
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->dispatcher->dispatch('test.error', new \Codeception\Event\Fail($test, $e));
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->dispatcher->dispatch('test.incomplete', new \Codeception\Event\Fail($test, $e));
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->unsuccessfulTests[] = spl_object_hash($test);
        $this->dispatcher->dispatch('test.skipped', new \Codeception\Event\Fail($test, $e));
    }

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->dispatcher->dispatch('suite.start', new \Codeception\Event\Suite($suite));
    }
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite) {
        $this->dispatcher->dispatch('suite.end', new \Codeception\Event\Suite($suite));
    }

    public function startTest(\PHPUnit_Framework_Test $test) {
        $this->dispatcher->dispatch('test.start', new \Codeception\Event\Test($test));
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time) {
        if (!in_array(spl_object_hash($test), $this->unsuccessfulTests))
            $this->dispatcher->dispatch('test.success', new \Codeception\Event\Test($test));

        $this->dispatcher->dispatch('test.end', new \Codeception\Event\Test($test));
    }
    
}
