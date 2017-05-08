<?php
namespace Codeception\Test;

use Codeception\TestInterface;

/**
 * The most simple testcase (with only one test in it) which can be executed by PHPUnit/Codeception.
 * It can be extended with included traits. Turning on/off a trait should not break class functionality.
 *
 * Class has exactly one method to be executed for testing, wrapped with before/after callbacks delivered from included traits.
 * A trait providing before/after callback should contain corresponding protected methods: `{traitName}Start` and `{traitName}End`,
 * then this trait should be enabled in `hooks` property.
 *
 * Inherited class must implement `test` method.
 */
abstract class Test implements TestInterface, Interfaces\Descriptive
{
    use Feature\AssertionCounter;
    use Feature\CodeCoverage;
    use Feature\ErrorLogger;
    use Feature\MetadataCollector;
    use Feature\IgnoreIfMetadataBlocked;

    private $testResult;
    private $ignored = false;

    /**
     * Enabled traits with methods to be called before and after the test.
     *
     * @var array
     */
    protected $hooks = [
      'ignoreIfMetadataBlocked',
      'codeCoverage',
      'assertionCounter',
      'errorLogger'
    ];

    const STATUS_FAIL = 'fail';
    const STATUS_ERROR = 'error';
    const STATUS_OK = 'ok';
    const STATUS_PENDING = 'pending';

    /**
     * Everything inside this method is treated as a test.
     *
     * @return mixed
     */
    abstract public function test();

    /**
     * Test representation
     *
     * @return mixed
     */
    abstract public function toString();

    /**
     * Runs a test and collects its result in a TestResult instance.
     * Executes before/after hooks coming from traits.
     *
     * @param  \PHPUnit_Framework_TestResult $result
     * @return \PHPUnit_Framework_TestResult
     */
    final public function run(\PHPUnit_Framework_TestResult $result = null)
    {
        $this->testResult = $result;

        $status = self::STATUS_PENDING;
        $time = 0;
        $e = null;
        
        $result->startTest($this);

        foreach ($this->hooks as $hook) {
            if (method_exists($this, $hook.'Start')) {
                $this->{$hook.'Start'}();
            }
        }

        if ($result->errorCount() > 0) {
            return;
        }

        if (!$this->ignored) {
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
        }

        foreach (array_reverse($this->hooks) as $hook) {
            if (method_exists($this, $hook.'End')) {
                $this->{$hook.'End'}($status, $time, $e);
            }
        }

        $result->endTest($this, $time);
        return $result;
    }

    public function getTestResultObject()
    {
        return $this->testResult;
    }

    /**
     * This class represents exactly one test
     * @return int
     */
    public function count()
    {
        return 1;
    }

    /**
     * Should a test be skipped (can be set from hooks)
     *
     * @param boolean $ignored
     */
    protected function ignore($ignored)
    {
        $this->ignored = $ignored;
    }
}
