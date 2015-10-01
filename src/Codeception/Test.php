<?php
namespace Codeception;

abstract class Test implements \PHPUnit_Framework_Test, \PHPUnit_Framework_SelfDescribing
{
    use TestFormat\Decorator\AssertionCounter;
    use TestFormat\Decorator\CodeCoverage;
    use TestFormat\Decorator\ErrorLogger;

    protected $testResult;

    protected $decorators = [
      'coverage',
      'assertionCounter',
      'errorLogger'
    ];


    const STATUS_FAIL = 'fail';
    const STATUS_ERROR = 'error';
    const STATUS_OK = 'ok';

    public function count()
    {
        return 1;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     *
     * @param  \PHPUnit_Framework_TestResult $result
     * @return \PHPUnit_Framework_TestResult
     */
    final public function run(\PHPUnit_Framework_TestResult $result = null)
    {
        $this->testResult = $result;
        $result->startTest($this);
        foreach ($this->decorators as $decorator) {
            $this->{$decorator.'Start'}();
        }

        $status = null;
        $e = null;

        \PHP_Timer::start();
        try {
            $this->test();
            $status = self::STATUS_OK;
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $status = self::STATUS_FAIL;
        } catch (\PHPUnit_Framework_Exception $e) {
            $status = self::STATUS_ERROR;
        } catch (\Exception $e) {
            $e     = new \PHPUnit_Framework_ExceptionWrapper($e);
            $status = self::STATUS_ERROR;
        }
        $time = \PHP_Timer::stop();

        foreach (array_reverse($this->decorators) as $decorator) {
            $this->{$decorator.'End'}($status, $time, $e);
        }

        $result->endTest($this, $time);
        return $result;
    }

    public function getTestResult()
    {
        return $this->testResult;
    }

    abstract public function test();

    abstract public function toString();
}