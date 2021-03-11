<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Codeception\PHPUnit\NonFinal;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const PHP_SAPI;
use const PHP_VERSION;
use function array_diff;
use function array_map;
use function assert;
use function class_exists;
use function count;
use function dirname;
use function htmlspecialchars;
use function is_array;
use function is_int;
use function is_string;
use function mt_srand;
use function range;
use function realpath;
use function sprintf;
use function time;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\DefaultTestResultCache;
//use PHPUnit\Runner\Extension\ExtensionHandler;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\Filter\IncludeGroupFilterIterator;
use PHPUnit\Runner\Filter\NameFilterIterator;
use PHPUnit\Runner\Hook;
use PHPUnit\Runner\NullTestResultCache;
use PHPUnit\Runner\ResultCacheExtension;
use PHPUnit\Runner\TestHook;
use PHPUnit\Runner\TestListenerAdapter;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\FilterMapper;
use PHPUnit\TextUI\XmlConfiguration\Configuration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\TextUI\XmlConfiguration\PhpHandler;
use PHPUnit\Util\Filesystem;
use PHPUnit\Util\Log\JUnit;
use PHPUnit\Util\Log\TeamCity;
use PHPUnit\Util\Printer;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use PHPUnit\Util\TestDox\HtmlResultPrinter;
use PHPUnit\Util\TestDox\TextResultPrinter;
use PHPUnit\Util\TestDox\XmlResultPrinter;
use PHPUnit\Util\Xml\SchemaDetector;
use ReflectionClass;
use ReflectionException;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Cobertura as CoberturaReport;
use SebastianBergmann\CodeCoverage\Report\Crap4j as Crap4jReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Xml\Facade as XmlReport;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Environment\Runtime;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Timer\Timer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class TestRunner
{
    public const SUCCESS_EXIT = 0;

    public const FAILURE_EXIT = 1;

    public const EXCEPTION_EXIT = 2;

    private static $versionStringPrinted = false;

    private $codeCoverageFilter = null;

    protected $printer = null;

    private $messagePrinted = false;

    /**
     * @var Hook[]
     */
    private $extensions = [];

    private $timer;

    public function __construct(CodeCoverageFilter $filter = null)
    {
        if ($filter === null) {
            $filter = new CodeCoverageFilter;
        }

        $this->codeCoverageFilter = $filter;
        $this->timer              = new Timer;
    }

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\TextUI\XmlConfiguration\Exception
     * @throws Exception
     */
    public function run(TestSuite $suite, array $arguments = [], array $warnings = [], bool $exit = true): TestResult
    {
        if (isset($arguments['configuration'])) {
            $GLOBALS['__PHPUNIT_CONFIGURATION_FILE'] = $arguments['configuration'];
        }

        $this->handleConfiguration($arguments);

        if (is_int($arguments['columns']) && $arguments['columns'] < 16) {
            $arguments['columns']   = 16;
            $tooFewColumnsRequested = true;
        }

        if (isset($arguments['bootstrap'])) {
            $GLOBALS['__PHPUNIT_BOOTSTRAP'] = $arguments['bootstrap'];
        }

        if ($arguments['backupGlobals'] === true) {
            $suite->setBackupGlobals(true);
        }

        if ($arguments['backupStaticAttributes'] === true) {
            $suite->setBackupStaticAttributes(true);
        }

        if ($arguments['beStrictAboutChangesToGlobalState'] === true) {
            $suite->setBeStrictAboutChangesToGlobalState(true);
        }

        if ($arguments['executionOrder'] === TestSuiteSorter::ORDER_RANDOMIZED) {
            mt_srand($arguments['randomOrderSeed']);
        }

        if ($arguments['cacheResult']) {
            if (isset($arguments['cacheDirectory']) &&
                Filesystem::createDirectory($arguments['cacheDirectory'])) {
                $cacheResultFile = realpath($arguments['cacheDirectory']) . DIRECTORY_SEPARATOR . 'test-results';
            } elseif (isset($arguments['cacheResultFile'])) {
                $cacheResultFile = $arguments['cacheResultFile'];
            } elseif (isset($arguments['configurationObject'])) {
                assert($arguments['configurationObject'] instanceof Configuration);

                $cacheResultFile = dirname(realpath($arguments['configurationObject']->filename())) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
            } else {
                $cacheResultFile = dirname(realpath($_SERVER['PHP_SELF'])) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
            }

            $cache = new DefaultTestResultCache($cacheResultFile);

            $this->addExtension(new ResultCacheExtension($cache));
        }

        if ($arguments['executionOrder'] !== TestSuiteSorter::ORDER_DEFAULT || $arguments['executionOrderDefects'] !== TestSuiteSorter::ORDER_DEFAULT || $arguments['resolveDependencies']) {
            $cache = $cache ?? new NullTestResultCache;

            $cache->load();

            $sorter = new TestSuiteSorter($cache);

            $sorter->reorderTestsInSuite($suite, $arguments['executionOrder'], $arguments['resolveDependencies'], $arguments['executionOrderDefects']);
            $originalExecutionOrder = $sorter->getOriginalExecutionOrder();

            unset($sorter);
        }

        if (is_int($arguments['repeat']) && $arguments['repeat'] > 0) {
            $_suite = new TestSuite;

            /* @noinspection PhpUnusedLocalVariableInspection */
            foreach (range(1, $arguments['repeat']) as $step) {
                $_suite->addTest($suite);
            }

            $suite = $_suite;

            unset($_suite);
        }

        $result = new TestResult;

        $listener       = new TestListenerAdapter;
        $listenerNeeded = false;

        foreach ($this->extensions as $extension) {
            if ($extension instanceof TestHook) {
                $listener->add($extension);

                $listenerNeeded = true;
            }
        }

        if ($listenerNeeded) {
            $result->addListener($listener);
        }

        unset($listener, $listenerNeeded);

        if (!$arguments['convertDeprecationsToExceptions']) {
            $result->convertDeprecationsToExceptions(false);
        }

        if (!$arguments['convertErrorsToExceptions']) {
            $result->convertErrorsToExceptions(false);
        }

        if (!$arguments['convertNoticesToExceptions']) {
            $result->convertNoticesToExceptions(false);
        }

        if (!$arguments['convertWarningsToExceptions']) {
            $result->convertWarningsToExceptions(false);
        }

        if ($arguments['stopOnError']) {
            $result->stopOnError(true);
        }

        if ($arguments['stopOnFailure']) {
            $result->stopOnFailure(true);
        }

        if ($arguments['stopOnWarning']) {
            $result->stopOnWarning(true);
        }

        if ($arguments['stopOnIncomplete']) {
            $result->stopOnIncomplete(true);
        }

        if ($arguments['stopOnRisky']) {
            $result->stopOnRisky(true);
        }

        if ($arguments['stopOnSkipped']) {
            $result->stopOnSkipped(true);
        }

        if ($arguments['stopOnDefect']) {
            $result->stopOnDefect(true);
        }

        if ($arguments['registerMockObjectsFromTestArgumentsRecursively']) {
            $result->registerMockObjectsFromTestArgumentsRecursively();
        }

        if ($this->printer === null) {
            if (isset($arguments['printer'])) {
                if ($arguments['printer'] instanceof ResultPrinter) {
                    $this->printer = $arguments['printer'];
                } elseif (is_string($arguments['printer']) && class_exists($arguments['printer'], false)) {
                    try {
                        $reflector = new ReflectionClass($arguments['printer']);

                        if ($reflector->implementsInterface(ResultPrinter::class)) {
                            $this->printer = $this->createPrinter($arguments['printer'], $arguments);
                        }

                        // @codeCoverageIgnoreStart
                    } catch (ReflectionException $e) {
                        throw new Exception(
                            $e->getMessage(),
                            (int) $e->getCode(),
                            $e
                        );
                    }
                    // @codeCoverageIgnoreEnd
                }
            } else {
                $this->printer = $this->createPrinter(DefaultResultPrinter::class, $arguments);
            }
        }

        if (isset($originalExecutionOrder) && $this->printer instanceof CliTestDoxPrinter) {
            assert($this->printer instanceof CliTestDoxPrinter);

            $this->printer->setOriginalExecutionOrder($originalExecutionOrder);
            $this->printer->setShowProgressAnimation(!$arguments['noInteraction']);
        }

        $this->printer->write(
            Version::getVersionString() . "\n"
        );

        self::$versionStringPrinted = true;

        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $result->addListener($this->printer);

        $coverageFilterFromConfigurationFile = false;
        $coverageFilterFromOption            = false;
        $codeCoverageReports                 = 0;

        if (isset($arguments['testdoxHTMLFile'])) {
            $result->addListener(
                new HtmlResultPrinter(
                    $arguments['testdoxHTMLFile'],
                    $arguments['testdoxGroups'],
                    $arguments['testdoxExcludeGroups']
                )
            );
        }

        if (isset($arguments['testdoxTextFile'])) {
            $result->addListener(
                new TextResultPrinter(
                    $arguments['testdoxTextFile'],
                    $arguments['testdoxGroups'],
                    $arguments['testdoxExcludeGroups']
                )
            );
        }

        if (isset($arguments['testdoxXMLFile'])) {
            $result->addListener(
                new XmlResultPrinter(
                    $arguments['testdoxXMLFile']
                )
            );
        }

        if (isset($arguments['teamcityLogfile'])) {
            $result->addListener(
                new TeamCity($arguments['teamcityLogfile'])
            );
        }

        if (isset($arguments['junitLogfile'])) {
            $result->addListener(
                new JUnit(
                    $arguments['junitLogfile'],
                    $arguments['reportUselessTests']
                )
            );
        }

        if (isset($arguments['coverageClover'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageCobertura'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageCrap4J'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageHtml'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coveragePHP'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageText'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageXml'])) {
            $codeCoverageReports++;
        }

        if ($codeCoverageReports > 0) {
            if (isset($arguments['coverageFilter'])) {
                if (!is_array($arguments['coverageFilter'])) {
                    $coverageFilterDirectories = [$arguments['coverageFilter']];
                } else {
                    $coverageFilterDirectories = $arguments['coverageFilter'];
                }

                foreach ($coverageFilterDirectories as $coverageFilterDirectory) {
                    $this->codeCoverageFilter->includeDirectory($coverageFilterDirectory);
                }

                $coverageFilterFromOption = true;
            }

            if (isset($arguments['configurationObject'])) {
                assert($arguments['configurationObject'] instanceof Configuration);

                $codeCoverageConfiguration = $arguments['configurationObject']->codeCoverage();

                if ($codeCoverageConfiguration->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
                    $coverageFilterFromConfigurationFile = true;

                    (new FilterMapper)->map(
                        $this->codeCoverageFilter,
                        $codeCoverageConfiguration
                    );
                }
            }
        }

        if ($codeCoverageReports > 0) {
            try {
                if (isset($codeCoverageConfiguration) &&
                    ($codeCoverageConfiguration->pathCoverage() || (isset($arguments['pathCoverage']) && $arguments['pathCoverage'] === true))) {
                    CodeCoverage::activate($this->codeCoverageFilter, true);
                } else {
                    CodeCoverage::activate($this->codeCoverageFilter, false);
                }

                if (isset($arguments['cacheDirectory']) &&
                    Filesystem::createDirectory($arguments['cacheDirectory'])) {
                    CodeCoverage::instance()->cacheStaticAnalysis(realpath($arguments['cacheDirectory']) . DIRECTORY_SEPARATOR . 'code-coverage');
                } elseif (isset($arguments['coverageCacheDirectory']) &&
                    Filesystem::createDirectory($arguments['coverageCacheDirectory'])) {
                    CodeCoverage::instance()->cacheStaticAnalysis($arguments['coverageCacheDirectory']);
                } elseif (isset($codeCoverageConfiguration) &&
                    $codeCoverageConfiguration->hasCacheDirectory() &&
                    Filesystem::createDirectory($codeCoverageConfiguration->cacheDirectory()->path())) {
                    CodeCoverage::instance()->cacheStaticAnalysis($codeCoverageConfiguration->cacheDirectory()->path());
                }

                CodeCoverage::instance()->excludeSubclassesOfThisClassFromUnintentionallyCoveredCodeCheck(Comparator::class);

                if ($arguments['strictCoverage']) {
                    CodeCoverage::instance()->enableCheckForUnintentionallyCoveredCode();
                }

                if (isset($arguments['ignoreDeprecatedCodeUnitsFromCodeCoverage'])) {
                    if ($arguments['ignoreDeprecatedCodeUnitsFromCodeCoverage']) {
                        CodeCoverage::instance()->ignoreDeprecatedCode();
                    } else {
                        CodeCoverage::instance()->doNotIgnoreDeprecatedCode();
                    }
                }

                if (isset($arguments['disableCodeCoverageIgnore'])) {
                    if ($arguments['disableCodeCoverageIgnore']) {
                        CodeCoverage::instance()->disableAnnotationsForIgnoringCode();
                    } else {
                        CodeCoverage::instance()->enableAnnotationsForIgnoringCode();
                    }
                }

                if (isset($arguments['configurationObject'])) {
                    $codeCoverageConfiguration = $arguments['configurationObject']->codeCoverage();

                    if ($codeCoverageConfiguration->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
                        if ($codeCoverageConfiguration->includeUncoveredFiles()) {
                            CodeCoverage::instance()->includeUncoveredFiles();
                        } else {
                            CodeCoverage::instance()->excludeUncoveredFiles();
                        }

                        if ($codeCoverageConfiguration->processUncoveredFiles()) {
                            CodeCoverage::instance()->processUncoveredFiles();
                        } else {
                            CodeCoverage::instance()->doNotProcessUncoveredFiles();
                        }
                    }
                }

                if ($this->codeCoverageFilter->isEmpty()) {
                    if (!$coverageFilterFromConfigurationFile && !$coverageFilterFromOption) {
                        $warnings[] = 'No filter is configured, code coverage will not be processed';
                    } else {
                        $warnings[] = 'Incorrect filter configuration, code coverage will not be processed';
                    }

                    CodeCoverage::deactivate();
                }
            } catch (CodeCoverageException $e) {
                $warnings[] = $e->getMessage();
            }
        }

        if ($arguments['verbose']) {
            if (PHP_SAPI === 'phpdbg') {
                $this->writeMessage('Runtime', 'PHPDBG ' . PHP_VERSION);
            } else {
                $runtime = 'PHP ' . PHP_VERSION;

                if (CodeCoverage::isActive()) {
                    $runtime .= ' with ' . CodeCoverage::driver()->nameAndVersion();
                }

                $this->writeMessage('Runtime', $runtime);
            }

            if (isset($arguments['configurationObject'])) {
                assert($arguments['configurationObject'] instanceof Configuration);

                $this->writeMessage(
                    'Configuration',
                    $arguments['configurationObject']->filename()
                );
            }

            foreach ($arguments['loadedExtensions'] as $extension) {
                $this->writeMessage(
                    'Extension',
                    $extension
                );
            }

            foreach ($arguments['notLoadedExtensions'] as $extension) {
                $this->writeMessage(
                    'Extension',
                    $extension
                );
            }
        }

        if ($arguments['executionOrder'] === TestSuiteSorter::ORDER_RANDOMIZED) {
            $this->writeMessage(
                'Random Seed',
                (string) $arguments['randomOrderSeed']
            );
        }

        if (isset($tooFewColumnsRequested)) {
            $warnings[] = 'Less than 16 columns requested, number of columns set to 16';
        }

        if ((new Runtime)->discardsComments()) {
            $warnings[] = 'opcache.save_comments=0 set; annotations will not work';
        }

        if (isset($arguments['conflictBetweenPrinterClassAndTestdox'])) {
            $warnings[] = 'Directives printerClass and testdox are mutually exclusive';
        }

        foreach ($warnings as $warning) {
            $this->writeMessage('Warning', $warning);
        }

        if (isset($arguments['configurationObject'])) {
            assert($arguments['configurationObject'] instanceof Configuration);

            if ($arguments['configurationObject']->hasValidationErrors()) {
                if ((new SchemaDetector)->detect($arguments['configurationObject']->filename())->detected()) {
                    $this->writeMessage('Warning', 'Your XML configuration validates against a deprecated schema.');
                    $this->writeMessage('Suggestion', 'Migrate your XML configuration using "--migrate-configuration"!');
                } else {
                    $this->write(
                        "\n  Warning - The configuration file did not pass validation!\n  The following problems have been detected:\n"
                    );

                    $this->write($arguments['configurationObject']->validationErrors());

                    $this->write("\n  Test results may not be as expected.\n\n");
                }
            }
        }

        $this->printer->write("\n");

        $result->beStrictAboutTestsThatDoNotTestAnything($arguments['reportUselessTests']);
        $result->beStrictAboutOutputDuringTests($arguments['disallowTestOutput']);
        $result->beStrictAboutTodoAnnotatedTests($arguments['disallowTodoAnnotatedTests']);
        $result->beStrictAboutResourceUsageDuringSmallTests($arguments['beStrictAboutResourceUsageDuringSmallTests']);

        if ($arguments['enforceTimeLimit'] === true && !(new Invoker)->canInvokeWithTimeout()) {
            $this->writeMessage('Error', 'PHP extension pcntl is required for enforcing time limits');
        }

        $result->enforceTimeLimit($arguments['enforceTimeLimit']);
        $result->setDefaultTimeLimit($arguments['defaultTimeLimit']);
        $result->setTimeoutForSmallTests($arguments['timeoutForSmallTests']);
        $result->setTimeoutForMediumTests($arguments['timeoutForMediumTests']);
        $result->setTimeoutForLargeTests($arguments['timeoutForLargeTests']);

        if (isset($arguments['forceCoversAnnotation']) && $arguments['forceCoversAnnotation'] === true) {
            $result->forceCoversAnnotation();
        }

        $this->processSuiteFilters($suite, $arguments);
        $suite->setRunTestInSeparateProcess($arguments['processIsolation']);

        foreach ($this->extensions as $extension) {
            if ($extension instanceof BeforeFirstTestHook) {
                $extension->executeBeforeFirstTest();
            }
        }

        $testSuiteWarningsPrinted = false;

        foreach ($suite->warnings() as $warning) {
            $this->writeMessage('Warning', $warning);

            $testSuiteWarningsPrinted = true;
        }

        if ($testSuiteWarningsPrinted) {
            $this->write(PHP_EOL);
        }

        $suite->run($result);

        foreach ($this->extensions as $extension) {
            if ($extension instanceof AfterLastTestHook) {
                $extension->executeAfterLastTest();
            }
        }

        $result->flushListeners();
        $this->printer->printResult($result);

        if (CodeCoverage::isActive()) {
            if (isset($arguments['coverageClover'])) {
                $this->codeCoverageGenerationStart('Clover XML');

                try {
                    $writer = new CloverReport;
                    $writer->process(CodeCoverage::instance(), $arguments['coverageClover']);

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if (isset($arguments['coverageCobertura'])) {
                $this->codeCoverageGenerationStart('Cobertura XML');

                try {
                    $writer = new CoberturaReport;
                    $writer->process(CodeCoverage::instance(), $arguments['coverageCobertura']);

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if (isset($arguments['coverageCrap4J'])) {
                $this->codeCoverageGenerationStart('Crap4J XML');

                try {
                    $writer = new Crap4jReport($arguments['crap4jThreshold']);
                    $writer->process(CodeCoverage::instance(), $arguments['coverageCrap4J']);

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if (isset($arguments['coverageHtml'])) {
                $this->codeCoverageGenerationStart('HTML');

                try {
                    $writer = new HtmlReport(
                        $arguments['reportLowUpperBound'],
                        $arguments['reportHighLowerBound'],
                        sprintf(
                            ' and <a href="https://phpunit.de/">PHPUnit %s</a>',
                            Version::id()
                        )
                    );

                    $writer->process(CodeCoverage::instance(), $arguments['coverageHtml']);

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if (isset($arguments['coveragePHP'])) {
                $this->codeCoverageGenerationStart('PHP');

                try {
                    $writer = new PhpReport;
                    $writer->process(CodeCoverage::instance(), $arguments['coveragePHP']);

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if (isset($arguments['coverageText'])) {
                if ($arguments['coverageText'] === 'php://stdout') {
                    $outputStream = $this->printer;
                    $colors       = $arguments['colors'] && $arguments['colors'] !== DefaultResultPrinter::COLOR_NEVER;
                } else {
                    $outputStream = new Printer($arguments['coverageText']);
                    $colors       = false;
                }

                $processor = new TextReport(
                    $arguments['reportLowUpperBound'],
                    $arguments['reportHighLowerBound'],
                    $arguments['coverageTextShowUncoveredFiles'],
                    $arguments['coverageTextShowOnlySummary']
                );

                $outputStream->write(
                    $processor->process(CodeCoverage::instance(), $colors)
                );
            }

            if (isset($arguments['coverageXml'])) {
                $this->codeCoverageGenerationStart('PHPUnit XML');

                try {
                    $writer = new XmlReport(Version::id());
                    $writer->process(CodeCoverage::instance(), $arguments['coverageXml']);

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }
        }

        if ($exit) {
            if (isset($arguments['failOnEmptyTestSuite']) && $arguments['failOnEmptyTestSuite'] === true && count($result) === 0) {
                exit(self::FAILURE_EXIT);
            }

            if ($result->wasSuccessfulIgnoringWarnings()) {
                if ($arguments['failOnRisky'] && !$result->allHarmless()) {
                    exit(self::FAILURE_EXIT);
                }

                if ($arguments['failOnWarning'] && $result->warningCount() > 0) {
                    exit(self::FAILURE_EXIT);
                }

                if ($arguments['failOnIncomplete'] && $result->notImplementedCount() > 0) {
                    exit(self::FAILURE_EXIT);
                }

                if ($arguments['failOnSkipped'] && $result->skippedCount() > 0) {
                    exit(self::FAILURE_EXIT);
                }

                exit(self::SUCCESS_EXIT);
            }

            if ($result->errorCount() > 0) {
                exit(self::EXCEPTION_EXIT);
            }

            if ($result->failureCount() > 0) {
                exit(self::FAILURE_EXIT);
            }
        }

        return $result;
    }

    public function addExtension(Hook $extension): void
    {
        $this->extensions[] = $extension;
    }

    private function write(string $buffer): void
    {
        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            $buffer = htmlspecialchars($buffer);
        }

        if ($this->printer !== null) {
            $this->printer->write($buffer);
        } else {
            print $buffer;
        }
    }

    /**
     * @throws \PHPUnit\TextUI\XmlConfiguration\Exception
     * @throws Exception
     */
    protected function handleConfiguration(array &$arguments): void
    {
        if (!isset($arguments['configurationObject']) && isset($arguments['configuration'])) {
            $arguments['configurationObject'] = (new Loader)->load($arguments['configuration']);
        }

        $arguments['debug']     = $arguments['debug'] ?? false;
        $arguments['filter']    = $arguments['filter'] ?? false;
        $arguments['listeners'] = $arguments['listeners'] ?? [];

        if (isset($arguments['configurationObject'])) {
            (new PhpHandler)->handle($arguments['configurationObject']->php());

            $codeCoverageConfiguration = $arguments['configurationObject']->codeCoverage();

            if (!isset($arguments['noCoverage'])) {
                if (!isset($arguments['coverageClover']) && $codeCoverageConfiguration->hasClover()) {
                    $arguments['coverageClover'] = $codeCoverageConfiguration->clover()->target()->path();
                }

                if (!isset($arguments['coverageCobertura']) && $codeCoverageConfiguration->hasCobertura()) {
                    $arguments['coverageCobertura'] = $codeCoverageConfiguration->cobertura()->target()->path();
                }

                if (!isset($arguments['coverageCrap4J']) && $codeCoverageConfiguration->hasCrap4j()) {
                    $arguments['coverageCrap4J'] = $codeCoverageConfiguration->crap4j()->target()->path();

                    if (!isset($arguments['crap4jThreshold'])) {
                        $arguments['crap4jThreshold'] = $codeCoverageConfiguration->crap4j()->threshold();
                    }
                }

                if (!isset($arguments['coverageHtml']) && $codeCoverageConfiguration->hasHtml()) {
                    $arguments['coverageHtml'] = $codeCoverageConfiguration->html()->target()->path();

                    if (!isset($arguments['reportLowUpperBound'])) {
                        $arguments['reportLowUpperBound'] = $codeCoverageConfiguration->html()->lowUpperBound();
                    }

                    if (!isset($arguments['reportHighLowerBound'])) {
                        $arguments['reportHighLowerBound'] = $codeCoverageConfiguration->html()->highLowerBound();
                    }
                }

                if (!isset($arguments['coveragePHP']) && $codeCoverageConfiguration->hasPhp()) {
                    $arguments['coveragePHP'] = $codeCoverageConfiguration->php()->target()->path();
                }

                if (!isset($arguments['coverageText']) && $codeCoverageConfiguration->hasText()) {
                    $arguments['coverageText']                   = $codeCoverageConfiguration->text()->target()->path();
                    $arguments['coverageTextShowUncoveredFiles'] = $codeCoverageConfiguration->text()->showUncoveredFiles();
                    $arguments['coverageTextShowOnlySummary']    = $codeCoverageConfiguration->text()->showOnlySummary();
                }

                if (!isset($arguments['coverageXml']) && $codeCoverageConfiguration->hasXml()) {
                    $arguments['coverageXml'] = $codeCoverageConfiguration->xml()->target()->path();
                }
            }

            $phpunitConfiguration = $arguments['configurationObject']->phpunit();

            $arguments['backupGlobals']                                   = $arguments['backupGlobals'] ?? $phpunitConfiguration->backupGlobals();
            $arguments['backupStaticAttributes']                          = $arguments['backupStaticAttributes'] ?? $phpunitConfiguration->backupStaticProperties();
            $arguments['beStrictAboutChangesToGlobalState']               = $arguments['beStrictAboutChangesToGlobalState'] ?? $phpunitConfiguration->beStrictAboutChangesToGlobalState();
            $arguments['cacheResult']                                     = $arguments['cacheResult'] ?? $phpunitConfiguration->cacheResult();
            $arguments['colors']                                          = $arguments['colors'] ?? $phpunitConfiguration->colors();
            $arguments['convertDeprecationsToExceptions']                 = $arguments['convertDeprecationsToExceptions'] ?? $phpunitConfiguration->convertDeprecationsToExceptions();
            $arguments['convertErrorsToExceptions']                       = $arguments['convertErrorsToExceptions'] ?? $phpunitConfiguration->convertErrorsToExceptions();
            $arguments['convertNoticesToExceptions']                      = $arguments['convertNoticesToExceptions'] ?? $phpunitConfiguration->convertNoticesToExceptions();
            $arguments['convertWarningsToExceptions']                     = $arguments['convertWarningsToExceptions'] ?? $phpunitConfiguration->convertWarningsToExceptions();
            $arguments['processIsolation']                                = $arguments['processIsolation'] ?? $phpunitConfiguration->processIsolation();
            $arguments['stopOnDefect']                                    = $arguments['stopOnDefect'] ?? $phpunitConfiguration->stopOnDefect();
            $arguments['stopOnError']                                     = $arguments['stopOnError'] ?? $phpunitConfiguration->stopOnError();
            $arguments['stopOnFailure']                                   = $arguments['stopOnFailure'] ?? $phpunitConfiguration->stopOnFailure();
            $arguments['stopOnWarning']                                   = $arguments['stopOnWarning'] ?? $phpunitConfiguration->stopOnWarning();
            $arguments['stopOnIncomplete']                                = $arguments['stopOnIncomplete'] ?? $phpunitConfiguration->stopOnIncomplete();
            $arguments['stopOnRisky']                                     = $arguments['stopOnRisky'] ?? $phpunitConfiguration->stopOnRisky();
            $arguments['stopOnSkipped']                                   = $arguments['stopOnSkipped'] ?? $phpunitConfiguration->stopOnSkipped();
            $arguments['failOnEmptyTestSuite']                            = $arguments['failOnEmptyTestSuite'] ?? $phpunitConfiguration->failOnEmptyTestSuite();
            $arguments['failOnIncomplete']                                = $arguments['failOnIncomplete'] ?? $phpunitConfiguration->failOnIncomplete();
            $arguments['failOnRisky']                                     = $arguments['failOnRisky'] ?? $phpunitConfiguration->failOnRisky();
            $arguments['failOnSkipped']                                   = $arguments['failOnSkipped'] ?? $phpunitConfiguration->failOnSkipped();
            $arguments['failOnWarning']                                   = $arguments['failOnWarning'] ?? $phpunitConfiguration->failOnWarning();
            $arguments['enforceTimeLimit']                                = $arguments['enforceTimeLimit'] ?? $phpunitConfiguration->enforceTimeLimit();
            $arguments['defaultTimeLimit']                                = $arguments['defaultTimeLimit'] ?? $phpunitConfiguration->defaultTimeLimit();
            $arguments['timeoutForSmallTests']                            = $arguments['timeoutForSmallTests'] ?? $phpunitConfiguration->timeoutForSmallTests();
            $arguments['timeoutForMediumTests']                           = $arguments['timeoutForMediumTests'] ?? $phpunitConfiguration->timeoutForMediumTests();
            $arguments['timeoutForLargeTests']                            = $arguments['timeoutForLargeTests'] ?? $phpunitConfiguration->timeoutForLargeTests();
            $arguments['reportUselessTests']                              = $arguments['reportUselessTests'] ?? $phpunitConfiguration->beStrictAboutTestsThatDoNotTestAnything();
            $arguments['strictCoverage']                                  = $arguments['strictCoverage'] ?? $phpunitConfiguration->beStrictAboutCoversAnnotation();
            $arguments['ignoreDeprecatedCodeUnitsFromCodeCoverage']       = $arguments['ignoreDeprecatedCodeUnitsFromCodeCoverage'] ?? $codeCoverageConfiguration->ignoreDeprecatedCodeUnits();
            $arguments['disallowTestOutput']                              = $arguments['disallowTestOutput'] ?? $phpunitConfiguration->beStrictAboutOutputDuringTests();
            $arguments['disallowTodoAnnotatedTests']                      = $arguments['disallowTodoAnnotatedTests'] ?? $phpunitConfiguration->beStrictAboutTodoAnnotatedTests();
            $arguments['beStrictAboutResourceUsageDuringSmallTests']      = $arguments['beStrictAboutResourceUsageDuringSmallTests'] ?? $phpunitConfiguration->beStrictAboutResourceUsageDuringSmallTests();
            $arguments['verbose']                                         = $arguments['verbose'] ?? $phpunitConfiguration->verbose();
            $arguments['reverseDefectList']                               = $arguments['reverseDefectList'] ?? $phpunitConfiguration->reverseDefectList();
            $arguments['forceCoversAnnotation']                           = $arguments['forceCoversAnnotation'] ?? $phpunitConfiguration->forceCoversAnnotation();
            $arguments['disableCodeCoverageIgnore']                       = $arguments['disableCodeCoverageIgnore'] ?? $codeCoverageConfiguration->disableCodeCoverageIgnore();
            $arguments['registerMockObjectsFromTestArgumentsRecursively'] = $arguments['registerMockObjectsFromTestArgumentsRecursively'] ?? $phpunitConfiguration->registerMockObjectsFromTestArgumentsRecursively();
            $arguments['noInteraction']                                   = $arguments['noInteraction'] ?? $phpunitConfiguration->noInteraction();
            $arguments['executionOrder']                                  = $arguments['executionOrder'] ?? $phpunitConfiguration->executionOrder();
            $arguments['resolveDependencies']                             = $arguments['resolveDependencies'] ?? $phpunitConfiguration->resolveDependencies();

            if (!isset($arguments['bootstrap']) && $phpunitConfiguration->hasBootstrap()) {
                $arguments['bootstrap'] = $phpunitConfiguration->bootstrap();
            }

            if (!isset($arguments['cacheDirectory']) && $phpunitConfiguration->hasCacheDirectory()) {
                $arguments['cacheDirectory'] = $phpunitConfiguration->cacheDirectory();
            }

            if (!isset($arguments['cacheResultFile']) && $phpunitConfiguration->hasCacheResultFile()) {
                $arguments['cacheResultFile'] = $phpunitConfiguration->cacheResultFile();
            }

            if (!isset($arguments['executionOrderDefects'])) {
                $arguments['executionOrderDefects'] = $phpunitConfiguration->defectsFirst() ? TestSuiteSorter::ORDER_DEFECTS_FIRST : TestSuiteSorter::ORDER_DEFAULT;
            }

            if ($phpunitConfiguration->conflictBetweenPrinterClassAndTestdox()) {
                $arguments['conflictBetweenPrinterClassAndTestdox'] = true;
            }

            $groupCliArgs = [];

            if (!empty($arguments['groups'])) {
                $groupCliArgs = $arguments['groups'];
            }

            $groupConfiguration = $arguments['configurationObject']->groups();

            if (!isset($arguments['groups']) && $groupConfiguration->hasInclude()) {
                $arguments['groups'] = $groupConfiguration->include()->asArrayOfStrings();
            }

            if (!isset($arguments['excludeGroups']) && $groupConfiguration->hasExclude()) {
                $arguments['excludeGroups'] = array_diff($groupConfiguration->exclude()->asArrayOfStrings(), $groupCliArgs);
            }

//            foreach ($arguments['configurationObject']->extensions() as $extension) {
//                (new ExtensionHandler)->registerExtension($extension, $this);
//            }

            foreach ($arguments['unavailableExtensions'] as $extension) {
                $arguments['warnings'][] = sprintf(
                    'Extension "%s" is not available',
                    $extension
                );
            }

            $loggingConfiguration = $arguments['configurationObject']->logging();

            if (!isset($arguments['noLogging'])) {
                if ($loggingConfiguration->hasText()) {
                    $arguments['listeners'][] = new DefaultResultPrinter(
                        $loggingConfiguration->text()->target()->path(),
                        true
                    );
                }

                if (!isset($arguments['teamcityLogfile']) && $loggingConfiguration->hasTeamCity()) {
                    $arguments['teamcityLogfile'] = $loggingConfiguration->teamCity()->target()->path();
                }

                if (!isset($arguments['junitLogfile']) && $loggingConfiguration->hasJunit()) {
                    $arguments['junitLogfile'] = $loggingConfiguration->junit()->target()->path();
                }

                if (!isset($arguments['testdoxHTMLFile']) && $loggingConfiguration->hasTestDoxHtml()) {
                    $arguments['testdoxHTMLFile'] = $loggingConfiguration->testDoxHtml()->target()->path();
                }

                if (!isset($arguments['testdoxTextFile']) && $loggingConfiguration->hasTestDoxText()) {
                    $arguments['testdoxTextFile'] = $loggingConfiguration->testDoxText()->target()->path();
                }

                if (!isset($arguments['testdoxXMLFile']) && $loggingConfiguration->hasTestDoxXml()) {
                    $arguments['testdoxXMLFile'] = $loggingConfiguration->testDoxXml()->target()->path();
                }
            }

            $testdoxGroupConfiguration = $arguments['configurationObject']->testdoxGroups();

            if (!isset($arguments['testdoxGroups']) && $testdoxGroupConfiguration->hasInclude()) {
                $arguments['testdoxGroups'] = $testdoxGroupConfiguration->include()->asArrayOfStrings();
            }

            if (!isset($arguments['testdoxExcludeGroups']) && $testdoxGroupConfiguration->hasExclude()) {
                $arguments['testdoxExcludeGroups'] = $testdoxGroupConfiguration->exclude()->asArrayOfStrings();
            }
        }

//        $extensionHandler = new ExtensionHandler;
//
//        foreach ($arguments['extensions'] as $extension) {
//            $extensionHandler->registerExtension($extension, $this);
//        }

        unset($extensionHandler);

        $arguments['backupGlobals']                                   = $arguments['backupGlobals'] ?? null;
        $arguments['backupStaticAttributes']                          = $arguments['backupStaticAttributes'] ?? null;
        $arguments['beStrictAboutChangesToGlobalState']               = $arguments['beStrictAboutChangesToGlobalState'] ?? null;
        $arguments['beStrictAboutResourceUsageDuringSmallTests']      = $arguments['beStrictAboutResourceUsageDuringSmallTests'] ?? false;
        $arguments['cacheResult']                                     = $arguments['cacheResult'] ?? true;
        $arguments['colors']                                          = $arguments['colors'] ?? DefaultResultPrinter::COLOR_DEFAULT;
        $arguments['columns']                                         = $arguments['columns'] ?? 80;
        $arguments['convertDeprecationsToExceptions']                 = $arguments['convertDeprecationsToExceptions'] ?? true;
        $arguments['convertErrorsToExceptions']                       = $arguments['convertErrorsToExceptions'] ?? true;
        $arguments['convertNoticesToExceptions']                      = $arguments['convertNoticesToExceptions'] ?? true;
        $arguments['convertWarningsToExceptions']                     = $arguments['convertWarningsToExceptions'] ?? true;
        $arguments['crap4jThreshold']                                 = $arguments['crap4jThreshold'] ?? 30;
        $arguments['disallowTestOutput']                              = $arguments['disallowTestOutput'] ?? false;
        $arguments['disallowTodoAnnotatedTests']                      = $arguments['disallowTodoAnnotatedTests'] ?? false;
        $arguments['defaultTimeLimit']                                = $arguments['defaultTimeLimit'] ?? 0;
        $arguments['enforceTimeLimit']                                = $arguments['enforceTimeLimit'] ?? false;
        $arguments['excludeGroups']                                   = $arguments['excludeGroups'] ?? [];
        $arguments['executionOrder']                                  = $arguments['executionOrder'] ?? TestSuiteSorter::ORDER_DEFAULT;
        $arguments['executionOrderDefects']                           = $arguments['executionOrderDefects'] ?? TestSuiteSorter::ORDER_DEFAULT;
        $arguments['failOnIncomplete']                                = $arguments['failOnIncomplete'] ?? false;
        $arguments['failOnRisky']                                     = $arguments['failOnRisky'] ?? false;
        $arguments['failOnSkipped']                                   = $arguments['failOnSkipped'] ?? false;
        $arguments['failOnWarning']                                   = $arguments['failOnWarning'] ?? false;
        $arguments['groups']                                          = $arguments['groups'] ?? [];
        $arguments['noInteraction']                                   = $arguments['noInteraction'] ?? false;
        $arguments['processIsolation']                                = $arguments['processIsolation'] ?? false;
        $arguments['randomOrderSeed']                                 = $arguments['randomOrderSeed'] ?? time();
        $arguments['registerMockObjectsFromTestArgumentsRecursively'] = $arguments['registerMockObjectsFromTestArgumentsRecursively'] ?? false;
        $arguments['repeat']                                          = $arguments['repeat'] ?? false;
        $arguments['reportHighLowerBound']                            = $arguments['reportHighLowerBound'] ?? 90;
        $arguments['reportLowUpperBound']                             = $arguments['reportLowUpperBound'] ?? 50;
        $arguments['reportUselessTests']                              = $arguments['reportUselessTests'] ?? true;
        $arguments['reverseList']                                     = $arguments['reverseList'] ?? false;
        $arguments['resolveDependencies']                             = $arguments['resolveDependencies'] ?? true;
        $arguments['stopOnError']                                     = $arguments['stopOnError'] ?? false;
        $arguments['stopOnFailure']                                   = $arguments['stopOnFailure'] ?? false;
        $arguments['stopOnIncomplete']                                = $arguments['stopOnIncomplete'] ?? false;
        $arguments['stopOnRisky']                                     = $arguments['stopOnRisky'] ?? false;
        $arguments['stopOnSkipped']                                   = $arguments['stopOnSkipped'] ?? false;
        $arguments['stopOnWarning']                                   = $arguments['stopOnWarning'] ?? false;
        $arguments['stopOnDefect']                                    = $arguments['stopOnDefect'] ?? false;
        $arguments['strictCoverage']                                  = $arguments['strictCoverage'] ?? false;
        $arguments['testdoxExcludeGroups']                            = $arguments['testdoxExcludeGroups'] ?? [];
        $arguments['testdoxGroups']                                   = $arguments['testdoxGroups'] ?? [];
        $arguments['timeoutForLargeTests']                            = $arguments['timeoutForLargeTests'] ?? 60;
        $arguments['timeoutForMediumTests']                           = $arguments['timeoutForMediumTests'] ?? 10;
        $arguments['timeoutForSmallTests']                            = $arguments['timeoutForSmallTests'] ?? 1;
        $arguments['verbose']                                         = $arguments['verbose'] ?? false;
    }

    private function processSuiteFilters(TestSuite $suite, array $arguments): void
    {
        if (!$arguments['filter'] &&
            empty($arguments['groups']) &&
            empty($arguments['excludeGroups']) &&
            empty($arguments['testsCovering']) &&
            empty($arguments['testsUsing'])) {
            return;
        }

        $filterFactory = new Factory;

        if (!empty($arguments['excludeGroups'])) {
            $filterFactory->addFilter(
                new ReflectionClass(ExcludeGroupFilterIterator::class),
                $arguments['excludeGroups']
            );
        }

        if (!empty($arguments['groups'])) {
            $filterFactory->addFilter(
                new ReflectionClass(IncludeGroupFilterIterator::class),
                $arguments['groups']
            );
        }

        if (!empty($arguments['testsCovering'])) {
            $filterFactory->addFilter(
                new ReflectionClass(IncludeGroupFilterIterator::class),
                array_map(
                    static function (string $name): string {
                        return '__phpunit_covers_' . $name;
                    },
                    $arguments['testsCovering']
                )
            );
        }

        if (!empty($arguments['testsUsing'])) {
            $filterFactory->addFilter(
                new ReflectionClass(IncludeGroupFilterIterator::class),
                array_map(
                    static function (string $name): string {
                        return '__phpunit_uses_' . $name;
                    },
                    $arguments['testsUsing']
                )
            );
        }

        if ($arguments['filter']) {
            $filterFactory->addFilter(
                new ReflectionClass(NameFilterIterator::class),
                $arguments['filter']
            );
        }

        $suite->injectFilter($filterFactory);
    }

    private function writeMessage(string $type, string $message): void
    {
        if (!$this->messagePrinted) {
            $this->write("\n");
        }

        $this->write(
            sprintf(
                "%-15s%s\n",
                $type . ':',
                $message
            )
        );

        $this->messagePrinted = true;
    }

    private function createPrinter(string $class, array $arguments): ResultPrinter
    {
        $object = new $class(
            (isset($arguments['stderr']) && $arguments['stderr'] === true) ? 'php://stderr' : null,
            $arguments['verbose'],
            $arguments['colors'],
            $arguments['debug'],
            $arguments['columns'],
            $arguments['reverseList']
        );

        assert($object instanceof ResultPrinter);

        return $object;
    }

    private function codeCoverageGenerationStart(string $format): void
    {
        $this->printer->write(
            sprintf(
                "\nGenerating code coverage report in %s format ... ",
                $format
            )
        );

        $this->timer->start();
    }

    private function codeCoverageGenerationSucceeded(): void
    {
        $this->printer->write(
            sprintf(
                "done [%s]\n",
                $this->timer->stop()->asString()
            )
        );
    }

    private function codeCoverageGenerationFailed(\Exception $e): void
    {
        $this->printer->write(
            sprintf(
                "failed [%s]\n%s\n",
                $this->timer->stop()->asString(),
                $e->getMessage()
            )
        );
    }
}
