<?php
namespace Codeception\PHPUnit\ResultPrinter;

class UI extends \PHPUnit_TextUI_ResultPrinter {

    /**
     * @var
     */
    protected $dispatcher;
    
    public function __construct(\Symfony\Component\EventDispatcher\EventDispatcher $dispatcher, $options, $out = null) {
        parent::__construct($out, $options['steps'], $options['colors'], $options['debug']);
        $this->dispatcher = $dispatcher;
    }

    protected function printDefect(\PHPUnit_Framework_TestFailure $defect, $count)
    {
        $failedTest = $defect->failedTest();
        if (!($failedTest instanceof \Codeception\TestCase)) return parent::printDefect($defect, $count);
        $this->dispatcher->dispatch('fail.print', new \Codeception\Event\Fail($defect->failedTest(), $defect->thrownException()));
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if ($test instanceof \Codeception\TestCase\Test) return parent::startTest($test);
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $this->numAssertions += $test->getNumAssertions();
        $this->lastTestFailed = FALSE;
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->lastTestFailed = TRUE;
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time) {
        $this->lastTestFailed = TRUE;
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->lastTestFailed = TRUE;
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->lastTestFailed = TRUE;
    }

}
