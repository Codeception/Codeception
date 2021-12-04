<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\Timer\Timer;
use Throwable;
use function array_reverse;
use function method_exists;

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

    private ?TestResult $testResult = null;

    private bool $ignored = false;

    /**
     * Enabled traits with methods to be called before and after the test.
     */
    protected array $hooks = [
      'ignoreIfMetadataBlocked',
      'codeCoverage',
      'assertionCounter',
      'errorLogger'
    ];

    /**
     * @var string
     */
    public const STATUS_FAIL = 'fail';
    /**
     * @var string
     */
    public const STATUS_ERROR = 'error';
    /**
     * @var string
     */
    public const STATUS_OK = 'ok';
    /**
     * @var string
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Everything inside this method is treated as a test.
     *
     * @return mixed
     */
    abstract public function test();

    /**
     * Test representation
     */
    abstract public function toString(): string;

    /**
     * Runs a test and collects its result in a TestResult instance.
     * Executes before/after hooks coming from traits.
     */
    final public function run(TestResult $result = null): TestResult
    {
        $this->testResult = $result;

        $status = self::STATUS_PENDING;
        $time = 0;
        $e = null;
        $timer = new Timer();

        $result->startTest($this);

        foreach ($this->hooks as $hook) {
            if (method_exists($this, $hook.'Start')) {
                $this->{$hook.'Start'}();
            }
        }

        $failedToStart = ReflectionHelper::readPrivateProperty($result, 'lastTestFailed');

        if (!$this->ignored && !$failedToStart) {
            $timer->start();

            try {
                $this->test();
                $status = self::STATUS_OK;
            } catch (AssertionFailedError $e) {
                $status = self::STATUS_FAIL;
            } catch (Exception $e) {
                $status = self::STATUS_ERROR;
            } catch (Throwable $e) {
                $e = new ExceptionWrapper($e);
                $status = self::STATUS_ERROR;
            }

            $time = $timer->stop()->asSeconds();
        }

        foreach (array_reverse($this->hooks) as $hook) {
            if (method_exists($this, $hook.'End')) {
                $this->{$hook.'End'}($status, $time, $e);
            }
        }

        $result->endTest($this, $time);
        return $result;
    }

    public function getTestResultObject(): TestResult
    {
        return $this->testResult;
    }

    /**
     * This class represents exactly one test
     */
    public function count(): int
    {
        return 1;
    }

    /**
     * Should a test be skipped (can be set from hooks)
     */
    protected function ignore(bool $ignored): void
    {
        $this->ignored = $ignored;
    }
}
