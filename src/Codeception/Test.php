<?php
namespace Codeception;

abstract class Test implements \PHPUnit_Framework_Test, \PHPUnit_Framework_SelfDescribing
{
    protected $testResult;

    public function count()
    {
        return 1;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @return PHPUnit_Framework_TestResult
     */
    final public function run(\PHPUnit_Framework_TestResult $result = null)
    {
        $this->testResult = $result;
        $result->startTest($this);
        \PHP_Timer::start();
        $this->test();
        $time = \PHP_Timer::stop();
        $result->endTest($this, $time);
        return $result;
    }

    abstract public function test();

    abstract public function toString();
}