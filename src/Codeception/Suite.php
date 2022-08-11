<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;
use Codeception\Test\Test;
use Codeception\Test\TestCaseWrapper;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\Configuration\Registry;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var Test[]
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
                    $result->addTest($test);

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

            if ($test instanceof TestCaseWrapper) {
                $testCase = $test->getTestCase();
                if (Version::series() < 10) {
                    $testCase->setBeStrictAboutChangesToGlobalState($this->beStrictAboutChangesToGlobalState);
                    $testCase->setBackupGlobals($this->backupGlobals);
                }
            }

            $test->setEventDispatcher($this->dispatcher);
            $test->reportUselessTests($this->reportUselessTests);
            $test->collectCodeCoverage($this->collectCodeCoverage);
            $test->realRun($result);
        }
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

    protected function getDependencies(Test $test): array
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

    protected function findMatchedTest(string $testSignature): ?Test
    {
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

    private function endTest(Test $test, ResultAggregator $result, float $time): void
    {
        $this->dispatcher->dispatch(new TestEvent($test, $time), Events::TEST_END);
        $result->addSuccessful($test);
    }

    public function addTest(Test $test): void
    {
        $this->tests [] = $test;
    }

    /**
     * @return Test[]
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
