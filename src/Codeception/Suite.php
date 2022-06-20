<?php

declare(strict_types=1);

namespace Codeception;

use AssertionError;
use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Lib\Notification;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;
use Codeception\Test\Test;
use Codeception\Test\Unit;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Framework\RiskyBecauseNoAssertionsWerePerformedException;
use PHPUnit\Framework\RiskyDueToUnexpectedAssertionsException;
use PHPUnit\Framework\RiskyDueToUnintentionallyCoveredCodeException;
use PHPUnit\Framework\RiskyTest;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTest;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\UnintentionallyCoveredCodeError;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Metadata\Api\CodeCoverage as CodeCoverageMetadataApi;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\Util\Test as TestUtil;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

use function count;

class Suite
{
    /**
     * @var Array<string, Module>
     */
    protected array $modules = [];

    protected ?string $baseName = null;

    private bool $reportUselessTests = false;
    private bool $backupGlobals = false;
    private bool $beStrictAboutChangesToGlobalState = false;
    private bool $disallowTestOutput = false;
    private bool $collectCodeCoverage = false;

    /**
     * @var array<TestInterface|PHPUnitTest>
     */
    private array $tests = [];

    public function __construct(private EventDispatcher $dispatcher, private string $name = '')
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function reportUselessTests(bool $enabled): void
    {
        $this->reportUselessTests = $enabled;
    }

    public function backupGlobals(bool $enabled): void
    {
        $this->backupGlobals = $enabled;
    }

    public function beStrictAboutChangesToGlobalState(bool $enabled): void
    {
        $this->beStrictAboutChangesToGlobalState = $enabled;
    }

    public function disallowTestOutput(bool $enabled): void
    {
        $this->disallowTestOutput = $enabled;
    }

    public function collectCodeCoverage(bool $enabled): void
    {
        $this->collectCodeCoverage = $enabled;
    }

    public function run(ResultAggregator $result): void
    {
        if (count($this->tests) === 0) {
            return;
        }

        $this->dispatcher->dispatch(new SuiteEvent($this), 'suite.start');

        foreach ($this->tests as $test) {
            if ($result->shouldStop()) {
                break;
            }
            $this->dispatcher->dispatch(new TestEvent($test), Events::TEST_START);

            if ($test instanceof TestInterface) {
                if ($test->getMetadata()->isBlocked()) {
                    $result->startTest($test);

                    $skip = $test->getMetadata()->getSkip();
                    if ($skip !== null) {
                        if (class_exists(SkippedWithMessageException::class)) {
                            $exception = new SkippedWithMessageException($skip);
                        } else {
                            $exception = new SkippedTestError($skip);
                        }
                        $failEvent = new FailEvent($test, $exception, 0);
                        $result->addSkipped($failEvent);
                        $this->dispatcher->dispatch($failEvent, Events::TEST_SKIPPED);
                    }
                    $incomplete = $test->getMetadata()->getIncomplete();
                    if ($incomplete !== null) {
                        $exception = new IncompleteTestError($incomplete);
                        $failEvent = new FailEvent($test, $exception, 0);
                        $result->addIncomplete($failEvent);
                        $this->dispatcher->dispatch($failEvent, Events::TEST_INCOMPLETE);
                    }

                    $this->endTest($test, $result, 0);
                    continue;
                }
            }

            if ($test instanceof TestCase) {
                if (Version::series() < 10) {
                    $test->setBeStrictAboutChangesToGlobalState($this->beStrictAboutChangesToGlobalState);
                    $test->setBackupGlobals($this->backupGlobals);
                }
                if ($test instanceof Unit) {
                    $test->setResultAggregator($result);
                }
                $this->runPhpUnitTest($test, $result);
            } elseif ($test instanceof Test) {
                $test->setEventDispatcher($this->dispatcher);
                $test->reportUselessTests($this->reportUselessTests);
                $test->collectCodeCoverage($this->collectCodeCoverage);
                $test->realRun($result);
            }
        }
    }

    private function runPhpUnitTest(TestCase $test, ResultAggregator $result): void
    {
        Assert::resetCount();

        $isPhpUnit9 = Version::series() < 10;
        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $useless    = false;
        $skipped    = false;

        $result->startTest($test);
        try {
            $this->fire(Events::TEST_BEFORE, new TestEvent($test));
        } catch (\Exception $e) {
            $failEvent = new FailEvent($test, $e, 0);
            $result->addError($failEvent);
            $this->fire(Events::TEST_ERROR, $failEvent);
            $this->fire(Events::TEST_AFTER, new TestEvent($test, 0));
            $this->endTest($test, $result, 0);
            return;
        }

        $shouldCodeCoverageBeCollected = false;

        if ($this->collectCodeCoverage && !$test instanceof ErrorTestCase && !$test instanceof WarningTestCase) {
            $codeCoverage = PhpCodeCoverageFactory::build();

            if ($isPhpUnit9) {
                $shouldCodeCoverageBeCollected = TestUtil::requiresCodeCoverageDataCollection($test);
            } else {
                $shouldCodeCoverageBeCollected = (new CodeCoverageMetadataApi())->shouldCodeCoverageBeCollectedFor(
                    $test::class,
                    $test->getName(false)
                );
            }

            if ($shouldCodeCoverageBeCollected) {
                $codeCoverage->start($test);
            }
        }

        $timer = new Timer();
        $timer->start();

        try {
            $test->runBare();
        } catch (AssertionFailedError $e) {
            $failure = true;

            if ($e instanceof RiskyTest || $e instanceof RiskyTestError) {
                $useless = true;
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
        $numberOfAssertionsPerformed = Assert::getCount();
        $test->addToAssertionCount($numberOfAssertionsPerformed);
        $result->addToAssertionCount($numberOfAssertionsPerformed);

        if (
            $this->reportUselessTests &&
            !$incomplete &&
            !$skipped &&
            !$error &&
            !$failure &&
            !$warning &&
            !$test->doesNotPerformAssertions() &&
            $numberOfAssertionsPerformed === 0
        ) {
            $failure = true;
            $useless = true;
            if ($isPhpUnit9) {
                $e = new RiskyTestError('This test did not perform any assertions');
            } else {
                $e = new RiskyBecauseNoAssertionsWerePerformedException();
            }
        }

        if ($shouldCodeCoverageBeCollected) {
            $append           = !$useless && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];

            if ($append) {
                try {
                    if ($isPhpUnit9) {
                        $linesToBeCovered = TestUtil::getLinesToBeCovered(
                            get_class($test),
                            $test->getName(false)
                        );

                        $linesToBeUsed = TestUtil::getLinesToBeUsed(
                            get_class($test),
                            $test->getName(false)
                        );
                    } else {
                        $linesToBeCovered = (new CodeCoverageMetadataApi())->linesToBeCovered(
                            $test::class,
                            $test->getName(false)
                        );

                        $linesToBeUsed = (new CodeCoverageMetadataApi())->linesToBeUsed(
                            $test::class,
                            $test->getName(false)
                        );
                    }
                } catch (InvalidCoversTargetException $cce) {
                    $result->addWarning(
                        new FailEvent(
                            $test,
                            new Warning($cce->getMessage()),
                            $time,
                        ),
                    );
                }
            }

            try {
                $codeCoverage->stop(
                    $append,
                    $linesToBeCovered,
                    $linesToBeUsed
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                $message = 'This test executed code that is not listed as code to be covered or used:' .
                    PHP_EOL . $cce->getMessage();
                if ($isPhpUnit9) {
                    $unintentionallyCoveredCodeError = new UnintentionallyCoveredCodeError($message);
                } else {
                    $unintentionallyCoveredCodeError = new RiskyDueToUnintentionallyCoveredCodeException($message);
                }
            } catch (OriginalCodeCoverageException $cce) {
                $error = true;
                $e ??= $cce;
            }
        }

        if ($error && isset($e)) {
            $result->addError(new FailEvent($test, $e, $time));
            $eventType = Events::TEST_ERROR;
        } elseif ($failure && isset($e)) {
            $failEvent = new FailEvent($test, $e, $time);
            if ($skipped) {
                $result->addSkipped($failEvent);
                $eventType = Events::TEST_SKIPPED;
            } elseif ($incomplete) {
                $result->addIncomplete($failEvent);
                $eventType = Events::TEST_INCOMPLETE;
            } elseif ($useless) {
                $result->addUseless($failEvent);
                $eventType = Events::TEST_USELESS;
            } else {
                $result->addFailure($failEvent);
                $eventType = Events::TEST_FAIL;
            }
        } elseif ($warning && isset($e)) {
            $result->addWarning(new FailEvent($test, $e, $time));
            $eventType = Events::TEST_WARNING;
        } elseif (isset($unintentionallyCoveredCodeError)) {
            $e = $unintentionallyCoveredCodeError;
            $result->addFailure(new FailEvent($test, $unintentionallyCoveredCodeError, $time));
            $eventType = Events::TEST_ERROR;
        } elseif (
            $this->reportUselessTests &&
            $test->doesNotPerformAssertions() &&
            $numberOfAssertionsPerformed > 0
        ) {
            if ($isPhpUnit9) {
                $e = new RiskyTestError(
                    sprintf(
                        'This test is annotated with "@doesNotPerformAssertions" but performed %d assertions',
                        $test->getNumAssertions()
                    )
                );
            } else {
                $e = new RiskyDueToUnexpectedAssertionsException(
                    $numberOfAssertionsPerformed
                );
            }
            $result->addUseless(new FailEvent($test, $e, $time));
            $eventType = Events::TEST_USELESS;
        } else {
            $eventType = Events::TEST_SUCCESS;
        }

        if ($eventType === Events::TEST_SUCCESS) {
            $this->fire($eventType, new TestEvent($test, $time));
        } else {
            $this->fire($eventType, new FailEvent($test, $e, $time));
        }

        $this->fire(Events::TEST_AFTER, new TestEvent($test, $time));
        $this->endTest($test, $result, $time);
    }

    public function reorderDependencies(): void
    {
        $tests = [];
        foreach ($this->tests as $test) {
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
        $this->tests = $queue;
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
        foreach ($this->tests as $test) {
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

    private function endTest(PHPUnitTest $test, ResultAggregator $result, float $time): void
    {
        $this->dispatcher->dispatch(new TestEvent($test, $time), Events::TEST_END);
        $result->endTest($test);
    }

    public function addTest(TestInterface|PHPUnitTest $test): void
    {
        $this->tests [] = $test;
    }

    /**
     * @return PHPUnitTest[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    public function getTestCount(): int
    {
        return count($this->tests);
    }

    public function initPHPUnitConfiguration(): void
    {
        $cliParameters = [];
        if ($this->backupGlobals) {
            $cliParameters [] = '--globals-backup';
        }
        if ($this->beStrictAboutChangesToGlobalState) {
            $cliParameters [] = '--strict-global-state';
        }
        if ($this->disallowTestOutput) {
            $cliParameters [] = '--disallow-test-output';
        }

        $cliConfiguration = (new \PHPUnit\TextUI\CliArguments\Builder())->fromParameters($cliParameters, []);
        $xmlConfiguration = \PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration::create();
        Registry::init($cliConfiguration, $xmlConfiguration);
    }
}
