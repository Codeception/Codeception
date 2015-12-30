<?php
namespace Codeception\Test;

use Codeception\Test\Interfaces\Descriptive;
use Codeception\Testable;

abstract class Test implements Testable, Descriptive
{
    use Feature\AssertionCounter;
    use Feature\CodeCoverage;
    use Feature\ErrorLogger;
    use Feature\MetadataCollector;

    protected $testResult;

    protected $mixins = [
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

        foreach ($this->mixins as $mixin) {
            if (method_exists($this, $mixin.'Start')) {
                $this->{$mixin.'Start'}();
            }
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
        } catch (\Throwable $e) {
            $e     = new \PHPUnit_Framework_ExceptionWrapper($e);
            $status = self::STATUS_ERROR;
        } catch (\Exception $e) {
            $e     = new \PHPUnit_Framework_ExceptionWrapper($e);
            $status = self::STATUS_ERROR;
        }
        $time = \PHP_Timer::stop();

        foreach (array_reverse($this->mixins) as $mixin) {
            if (method_exists($this, $mixin.'End')) {
                $this->{$mixin.'End'}($status, $time, $e);
            }
        }
        $result->endTest($this, $time);
        return $result;
    }

    public function getTestResultObject()
    {
        return $this->testResult;
    }

    abstract public function test();

    abstract public function toString();

}