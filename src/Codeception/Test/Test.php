<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\UselessTestException;
use Codeception\PHPUnit\Wrapper\Test as TestWrapper;
use Codeception\ResultAggregator;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedTest;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Runner\Version as PHPUnitVersion;
use RuntimeException;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

use function array_reverse;
use function method_exists;

// phpcs:disable
if (PHPUnitVersion::series() < 10) {
    require_once __DIR__ . '/../../PHPUnit/Wrapper/PhpUnit9/Test.php';
} else {
    require_once __DIR__ . '/../../PHPUnit/Wrapper/PhpUnit10/Test.php';
}
// phpcs:enable

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
abstract class Test extends TestWrapper implements TestInterface, Interfaces\Descriptive
{
    use Feature\AssertionCounter;
    use Feature\CodeCoverage;
    use Feature\MetadataCollector;
    use Feature\IgnoreIfMetadataBlocked;

    private ?ResultAggregator $resultAggregator = null;

    private bool $ignored = false;

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
     * @var string
     */
    public const STATUS_USELESS = 'useless';
    /**
     * @var string
     */
    public const STATUS_INCOMPLETE = 'incomplete';
    /**
     * @var string
     */
    public const STATUS_SKIPPED = 'skipped';

    protected bool $reportUselessTests = false;

    private bool $collectCodeCoverage = false;

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

    public function collectCodeCoverage(bool $enabled): void
    {
        $this->collectCodeCoverage = $enabled;
    }

    public function reportUselessTests(bool $enabled): void
    {
        $this->reportUselessTests = $enabled;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     * Executes before/after hooks coming from traits.
     */
    final public function realRun(ResultAggregator $result): void
    {
        $this->resultAggregator = $result;

        $status = self::STATUS_PENDING;
        $time = 0;
        $e = null;
        $timer = new Timer();

        $result->addTest($this);

        try {
            $this->fire(Events::TEST_BEFORE, new TestEvent($this));

            foreach ($this->hooks as $hook) {
                if ($hook === 'codeCoverage' && !$this->collectCodeCoverage) {
                    continue;
                }
                if (method_exists($this, $hook . 'Start')) {
                    $this->{$hook . 'Start'}();
                }
            }
            $failedToStart = false;
        } catch (\Exception $e) {
            $failedToStart = true;
            $result->addError(new FailEvent($this, $e, $time));
            $this->fire(Events::TEST_ERROR, new FailEvent($this, $e, $time));
        }

        if (!$this->ignored && !$failedToStart) {
            Assert::resetCount();
            $timer->start();
            try {
                $this->test();
                $status = self::STATUS_OK;
                $eventType = Events::TEST_SUCCESS;

                $this->checkConditionalAsserts($result);
            } catch (UselessTestException $e) {
                $result->addUseless(new FailEvent($this, $e, $time));
                $status = self::STATUS_USELESS;
                $eventType = Events::TEST_USELESS;
            } catch (IncompleteTestError $e) {
                $result->addIncomplete(new FailEvent($this, $e, $time));
                $status = self::STATUS_INCOMPLETE;
                $eventType = Events::TEST_INCOMPLETE;
            } catch (SkippedTest | SkippedTestError $e) {
                $result->addSkipped(new FailEvent($this, $e, $time));
                $status = self::STATUS_SKIPPED;
                $eventType = Events::TEST_SKIPPED;
            } catch (AssertionFailedError $e) {
                $result->addFailure(new FailEvent($this, $e, $time));
                $status = self::STATUS_FAIL;
                $eventType = Events::TEST_FAIL;
            } catch (Exception $e) {
                $result->addError(new FailEvent($this, $e, $time));
                $status = self::STATUS_ERROR;
                $eventType = Events::TEST_ERROR;
            } catch (Throwable $e) {
                $result->addError(new FailEvent($this, $e, $time));
                $status = self::STATUS_ERROR;
                $eventType = Events::TEST_ERROR;
            }

            $time = $timer->stop()->asSeconds();

            $this->callTestEndHooks($status, $time, $e);

            // We need to get the number of performed assertions _after_ calling the test end hooks because the
            // AssertionCounter needs to set the number of performed assertions first.
            $result->addToAssertionCount($this->numberOfAssertionsPerformed());

            if (
                $this->reportUselessTests &&
                $this->numberOfAssertionsPerformed() === 0 &&
                !$this->doesNotPerformAssertions() &&
                $eventType === Events::TEST_SUCCESS
            ) {
                $eventType = Events::TEST_USELESS;
                $e = new UselessTestException('This test did not perform any assertions');
                $result->addUseless(new FailEvent($this, $e, $time));
            }

            if ($eventType === Events::TEST_SUCCESS) {
                $result->addSuccessful($this);
                $this->fire($eventType, new TestEvent($this, $time));
            } else {
                $this->fire($eventType, new FailEvent($this, $e, $time));
            }
        } else {
            $this->callTestEndHooks($status, $time, $e);
        }

        $this->fire(Events::TEST_AFTER, new TestEvent($this, $time));
        $this->eventDispatcher->dispatch(new TestEvent($this, $time), Events::TEST_END);
    }

    /**
     * Return false by default, the Unit-specific TestCaseWrapper implements this properly as it supports the PHPUnit
     * test override `->expectNotToPerformAssertions()`.
     */
    protected function doesNotPerformAssertions(): bool
    {
        return false;
    }

    public function getResultAggregator(): ResultAggregator
    {
        if ($this->resultAggregator === null) {
            throw new \LogicException('ResultAggregator is not set');
        }
        return $this->resultAggregator;
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
        return $this->getNumAssertions();
    }


    protected function fire(string $eventType, TestEvent $event): void
    {
        if ($this->eventDispatcher === null) {
            throw new RuntimeException('EventDispatcher must be injected before running test');
        }
        $test = $event->getTest();
        if ($test instanceof TestInterface) {
            foreach ($test->getMetadata()->getGroups() as $group) {
                $this->eventDispatcher->dispatch($event, $eventType . '.' . $group);
            }
        }
        $this->eventDispatcher->dispatch($event, $eventType);
    }

    private function callTestEndHooks(string $status, float $time, ?Throwable $e): void
    {
        foreach (array_reverse($this->hooks) as $hook) {
            if ($hook === 'codeCoverage' && !$this->collectCodeCoverage) {
                continue;
            }
            if (method_exists($this, $hook . 'End')) {
                $this->{$hook . 'End'}($status, $time, $e);
            }
        }
    }

    private function checkConditionalAsserts(ResultAggregator $result): void
    {
        if (!method_exists($this, 'getScenario')) {
            return;
        }

        $lastFailure = $result->getLastFailure();
        if ($lastFailure === null) {
            return;
        }

        if (Descriptor::getTestSignatureUnique($lastFailure->getTest()) !== Descriptor::getTestSignatureUnique($this)) {
            return;
        }

        foreach ($this->getScenario()?->getSteps() ?? [] as $step) {
            if ($step->hasFailed()) {
                $result->popLastFailure();
                throw $lastFailure->getFail();
            }
        }
    }
}
