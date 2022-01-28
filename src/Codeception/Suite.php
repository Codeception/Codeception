<?php

declare(strict_types=1);

namespace Codeception;

use AssertionError;
use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Lib\Notification;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;
use Codeception\Test\Test;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Framework\RiskyDueToUnintentionallyCoveredCodeException;
use PHPUnit\Framework\RiskyTest;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTest;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Metadata\Api\CodeCoverage as CodeCoverageMetadataApi;
use PHPUnit\Runner\CodeCoverage;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

class Suite extends TestSuite
{
    /**
     * @var Array<string, Module>
     */
    protected array $modules = [];

    protected ?string $baseName = null;

    private EventDispatcher $dispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
        parent::__construct('', '');
    }

    public function run(TestResult $result): void
    {
        if (count($this) === 0) {
            return;
        }

        $result->startTestSuite($this);
        $this->dispatcher->dispatch(new SuiteEvent($this), 'suite.start');

        foreach ($this as $test) {
            if ($result->shouldStop()) {
                break;
            }
            $this->dispatcher->dispatch(new TestEvent($test), Events::TEST_START);

            if ($test instanceof TestInterface) {
                if ($test->getMetadata()->isBlocked()) {
                    $result->startTest($test);

                    $skip = $test->getMetadata()->getSkip();
                    if ($skip !== null) {
                        $exception = new SkippedWithMessageException($skip);
                        $result->addFailure($test, $exception, 0);
                        $this->dispatcher->dispatch(new FailEvent($test, 0, $exception), Events::TEST_SKIPPED);
                    }
                    $incomplete = $test->getMetadata()->getIncomplete();
                    if ($incomplete !== null) {
                        $exception = new IncompleteTestError($incomplete);
                        $result->addFailure($test, $exception, 0);
                        $this->dispatcher->dispatch(new FailEvent($test, 0, $exception), Events::TEST_INCOMPLETE);
                    }

                    $this->endTest($test, $result, 0);
                    continue;
                }
            }

            if ($test instanceof TestCase)  {
                $this->runPhpUnitTest($test, $result);
            } elseif ($test instanceof Test) {
                $test->setEventDispatcher($this->dispatcher);
                $test->run($result);
            }
        }

        $result->endTestSuite($this);
    }

    private function runPhpUnitTest(TestCase $test, TestResult $result): void
    {
        Assert::resetCount();

        $shouldCodeCoverageBeCollected = (new CodeCoverageMetadataApi)->shouldCodeCoverageBeCollectedFor(
            $test::class,
            $test->getName(false)
        );

        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        $result->startTest($test);
        try {
            $this->fire(Events::TEST_BEFORE, new TestEvent($test));
        } catch (\Exception $e) {
            $result->addError($test, $e, 0);
            $this->fire(Events::TEST_ERROR, new FailEvent($this, 0, $e));
            $this->fire(Events::TEST_AFTER, new TestEvent($test, 0));
            $this->endTest($test, $result, 0);
            return;
        }

        $collectCodeCoverage = CodeCoverage::isActive() &&
            !$test instanceof ErrorTestCase &&
            !$test instanceof WarningTestCase &&
            $shouldCodeCoverageBeCollected;

        if ($collectCodeCoverage) {
            CodeCoverage::start($test);
        }

        $timer = new Timer;
        $timer->start();

        try {
            $test->runBare();
        } catch (AssertionFailedError $e) {
            $failure = true;

            if ($e instanceof RiskyTest) {
                $risky = true;
            } elseif ($e instanceof IncompleteTestError) {
                $incomplete = true;
            } elseif ($e instanceof SkippedTest) {
                $skipped = true;
            }
        } catch (AssertionError $e) {
            $test->addToAssertionCount(1);

            $failure = true;
            $frame   = $e->getTrace()[0];

            $e = new AssertionFailedError(
                sprintf(
                    '%s in %s:%s',
                    $e->getMessage(),
                    $frame['file'],
                    $frame['line']
                )
            );
        } catch (Warning $e) {
            Notification::warning($e->getMessage(), '');
            $warning = true;
        } catch (Exception $e) {
            $error = true;
        } catch (Throwable $e) {
            $e     = new ExceptionWrapper($e);
            $error = true;
        }

        $time = $timer->stop()->asSeconds();
        $test->addToAssertionCount(Assert::getCount());

        if ($collectCodeCoverage) {
            $append           = !$risky && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];

            if ($append) {
                try {
                    $linesToBeCovered = (new CodeCoverageMetadataApi)->linesToBeCovered(
                        $test::class,
                        $test->getName(false)
                    );

                    $linesToBeUsed = (new CodeCoverageMetadataApi)->linesToBeUsed(
                        $test::class,
                        $test->getName(false)
                    );
                } catch (InvalidCoversTargetException $cce) {
                    $result->addWarning(
                        $test,
                        new Warning(
                            $cce->getMessage()
                        ),
                        $time
                    );
                }
            }

            try {
                CodeCoverage::stop(
                    $append,
                    $linesToBeCovered,
                    $linesToBeUsed
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                $unintentionallyCoveredCodeError = new RiskyDueToUnintentionallyCoveredCodeException(
                    'This test executed code that is not listed as code to be covered or used:' .
                    PHP_EOL . $cce->getMessage()
                );
            } catch (OriginalCodeCoverageException $cce) {
                $error = true;
                $e = $e ?? $cce;
            }
        }

        if ($error && isset($e)) {
            $result->addError($test, $e, $time);
            $eventType = Events::TEST_ERROR;
        } elseif ($failure && isset($e)) {
            $result->addFailure($test, $e, $time);
            if ($skipped) {
                $eventType = Events::TEST_SKIPPED;
            } elseif($incomplete) {
                $eventType = Events::TEST_INCOMPLETE;
            } else {
                $eventType = Events::TEST_FAIL;
            }
        } elseif ($warning && isset($e)) {
            $result->addWarning($test, $e, $time);
            $eventType = Events::TEST_WARNING;
        } elseif (isset($unintentionallyCoveredCodeError)) {
            $result->addFailure(
                $test,
                $unintentionallyCoveredCodeError,
                $time
            );
            $eventType = Events::TEST_ERROR;
        } else {
            $eventType = Events::TEST_SUCCESS;
        }

        if ($eventType === Events::TEST_SUCCESS) {
            $this->fire($eventType, new TestEvent($test, $time));
        } else {
            $this->fire($eventType, new FailEvent($test, $time, $e));
        }

        $this->fire(Events::TEST_AFTER, new TestEvent($test, $time));
        $this->endTest($test, $result, $time);
    }

    public function reorderDependencies(): void
    {
        $tests = [];
        foreach (parent::tests() as $test) {
            $tests = array_merge($tests, $this->getDependencies($test));
        }

        $queue = [];
        $hashes = [];
        foreach ($tests as $test) {
            if (in_array(spl_object_hash($test), $hashes, true)) {
                continue;
            }
            $hashes[] = spl_object_hash($test);
            $queue[] = $test;
        }
        $this->setTests($queue);
    }

    protected function getDependencies(Dependent|SelfDescribing $test): array
    {
        if (!$test instanceof Dependent) {
            return [$test];
        }
        $tests = [];
        foreach ($test->fetchDependencies() as $requiredTestName) {
            $required = $this->findMatchedTest($requiredTestName);
            if ($required === null) {
                continue;
            }
            $tests = array_merge($tests, $this->getDependencies($required));
        }
        $tests[] = $test;
        return $tests;
    }

    protected function findMatchedTest(string $testSignature): ?SelfDescribing
    {
        /** @var SelfDescribing $test */
        foreach (parent::tests() as $test) {
            $signature = Descriptor::getTestSignature($test);
            if ($signature === $testSignature) {
                return $test;
            }
        }

        return null;
    }

    /**
     * @return Array<string,Module>
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param Array<string,Module> $modules
     */
    public function setModules(array $modules): void
    {
        $this->modules = $modules;
    }

    public function getBaseName(): string
    {
        return $this->baseName;
    }

    public function setBaseName(string $baseName): void
    {
        $this->baseName = $baseName;
    }

    protected function fire(string $eventType, TestEvent $event): void
    {
        $test = $event->getTest();
        if ($test instanceof TestInterface) {
            foreach ($test->getMetadata()->getGroups() as $group) {
                $this->dispatcher->dispatch($event, $eventType . '.' . $group);
            }
        }
        $this->dispatcher->dispatch($event, $eventType);
    }

    private function endTest(PHPUnitTest $test, TestResult $result, float $time): void
    {
        $this->dispatcher->dispatch(new TestEvent($test, $time), Events::TEST_END);
        $result->endTest($test, $time);
    }
}
