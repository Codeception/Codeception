<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
    use Feature\MetadataCollector;
    use Feature\IgnoreIfMetadataBlocked;

    private ?TestResult $testResult = null;

    private bool $ignored = false;

    private int $assertionCount = 0;

    private ?EventDispatcher $eventDispatcher = null;

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

    public function setEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     * Executes before/after hooks coming from traits.
     */
    final public function run(TestResult $result): void
    {
        $this->testResult = $result;

        $status = self::STATUS_PENDING;
        $time = 0;
        $e = null;
        $timer = new Timer();

        $result->startTest($this);

        try {
            $this->fire(Events::TEST_BEFORE, new TestEvent($this));

            foreach ($this->hooks as $hook) {
                if (method_exists($this, $hook . 'Start')) {
                    $this->{$hook . 'Start'}();
                }
            }
            $failedToStart = ReflectionHelper::readPrivateProperty($result, 'lastTestFailed');
        } catch (\Exception $e) {
            $failedToStart = true;
            $result->addError($this, $e, $time);
            $this->fire(Events::TEST_ERROR, new FailEvent($this, $time, $e));
        }

        if (!$this->ignored && !$failedToStart) {
            Assert::resetCount();
            $timer->start();
            try {
                $this->test();
                $status = self::STATUS_OK;
                $eventType = Events::TEST_SUCCESS;
            } catch (AssertionFailedError $e) {
                $result->addFailure($this, $e, $time);
                $status = self::STATUS_FAIL;
                $eventType = Events::TEST_FAIL;
            } catch (Exception $e) {
                $result->addError($this, $e, $time);
                $status = self::STATUS_ERROR;
                $eventType = Events::TEST_ERROR;
            } catch (Throwable $e) {
                $result->addError($this, $e, $time);
                $e = new ExceptionWrapper($e);
                $status = self::STATUS_ERROR;
                $eventType = Events::TEST_ERROR;
            }

            $time = $timer->stop()->asSeconds();
            $this->assertionCount = Assert::getCount();

            if ($eventType === Events::TEST_SUCCESS) {
                $this->fire($eventType, new TestEvent($this, $time));
            } else {
                $this->fire($eventType, new FailEvent($this, $time, $e));
            }
        }

        foreach (array_reverse($this->hooks) as $hook) {
            if (method_exists($this, $hook.'End')) {
                $this->{$hook.'End'}($status, $time, $e);
            }
        }

        $this->fire(Events::TEST_AFTER, new TestEvent($this, $time));
        $this->eventDispatcher->dispatch(new TestEvent($this, $time), Events::TEST_END);
        $result->endTest($this, $time);
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

    public function numberOfAssertionsPerformed(): int
    {
        return $this->assertionCount;
    }


    protected function fire(string $eventType, TestEvent $event): void
    {
        if ($this->eventDispatcher === null) {
            throw new \RuntimeException('EventDispatcher must be injected before running test');
        }
        $test = $event->getTest();
        if ($test instanceof TestInterface) {
            foreach ($test->getMetadata()->getGroups() as $group) {
                $this->eventDispatcher->dispatch($event, $eventType . '.' . $group);
            }
        }
        $this->eventDispatcher->dispatch($event, $eventType);
    }
}
