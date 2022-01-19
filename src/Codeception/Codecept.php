<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Coverage\Subscriber\Local;
use Codeception\Coverage\Subscriber\LocalServer;
use Codeception\Coverage\Subscriber\Printer as CoveragePrinter;
use Codeception\Coverage\Subscriber\RemoteServer;
use Codeception\Event\PrintResultEvent;
use Codeception\Exception\ConfigurationException;
use Codeception\Subscriber\AutoRebuild;
use Codeception\Subscriber\BeforeAfterTest;
use Codeception\Subscriber\Bootstrap;
use Codeception\Subscriber\Console;
use Codeception\Subscriber\Dependencies;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Subscriber\ExtensionLoader;
use Codeception\Subscriber\FailFast;
use Codeception\Subscriber\GracefulTermination;
use Codeception\Subscriber\Module;
use Codeception\Subscriber\PrepareTest;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\Util\Printer as PHPUnitPrinter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Codecept
{
    /**
     * @var string
     */
    public const VERSION = '5.0.0';

    protected ?TestRunner $runner = null;

    protected TestResult $result;

    protected EventDispatcher $dispatcher;

    protected ExtensionLoader $extensionLoader;

    private PHPUnitPrinter $printer;

    protected array $options = [
        'silent'          => false,
        'debug'           => false,
        'steps'           => false,
        'html'            => false,
        'xml'             => false,
        'phpunit-xml'     => false,
        'no-redirect'     => true,
        'report'          => false,
        'colors'          => false,
        'coverage'        => false,
        'coverage-xml'    => false,
        'coverage-html'   => false,
        'coverage-text'   => false,
        'coverage-crap4j' => false,
        'coverage-cobertura' => false,
        'coverage-phpunit'=> false,
        'groups'          => null,
        'excludeGroups'   => null,
        'filter'          => null,
        'env'             => null,
        'fail-fast'       => 0,
        'ansi'            => true,
        'verbosity'       => 1,
        'interactive'     => true,
        'no-rebuild'      => false,
        'quiet'           => false,
    ];

    protected array $config = [];

    protected array $extensions = [];

    public function __construct(array $options = [])
    {

// TODO: implement these options
//        if (isset($arguments['report_useless_tests'])) {
//            $result->beStrictAboutTestsThatDoNotTestAnything((bool)$arguments['report_useless_tests']);
//        }
//
//        if (isset($arguments['disallow_test_output'])) {
//            $result->beStrictAboutOutputDuringTests((bool)$arguments['disallow_test_output']);
//        }

        $this->result = $this->initializeTestResult();
        $this->dispatcher = new EventDispatcher();
        $this->extensionLoader = new ExtensionLoader($this->dispatcher);

        $baseOptions = $this->mergeOptions($options);
        $this->extensionLoader->bootGlobalExtensions($baseOptions); // extensions may override config

        $this->config = Configuration::config();
        $this->options = $this->mergeOptions($options); // options updated from config

        // TODO: implement options
        // $printer = new UIResultPrinter($this->dispatcher, $this->options);
        $this->printer = new PHPUnitPrinter('php://stdout');
        $this->runner = new TestRunner();
        $this->registerSubscribers();
    }

    private function initializeTestResult(): TestResult
    {
        /*
         * Configuration must be registered, but TestResult only cares about stopOnError,
         * stopOnFailure and other stopOn settings that we don't set
         */
        $cliConfiguration = (new \PHPUnit\TextUI\CliArguments\Builder())->fromParameters([], []);
        $xmlConfiguration = \PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration::create();
        Registry::init($cliConfiguration, $xmlConfiguration);
        return new TestResult;
    }

    /**
     * Merges given options with default values and current configuration
     *
     * @throws ConfigurationException
     */
    protected function mergeOptions(array $options): array
    {
        $config = Configuration::config();
        $baseOptions = array_merge($this->options, $config['settings']);
        return array_merge($baseOptions, $options);
    }

    public function registerSubscribers(): void
    {
        // required
        $this->dispatcher->addSubscriber(new GracefulTermination());
        $this->dispatcher->addSubscriber(new ErrorHandler());
        $this->dispatcher->addSubscriber(new Dependencies());
        $this->dispatcher->addSubscriber(new Bootstrap());
        $this->dispatcher->addSubscriber(new PrepareTest());
        $this->dispatcher->addSubscriber(new Module());
        $this->dispatcher->addSubscriber(new BeforeAfterTest());

        // optional
        if (!$this->options['no-rebuild']) {
            $this->dispatcher->addSubscriber(new AutoRebuild());
        }
        if (!$this->options['silent']) {
            $this->dispatcher->addSubscriber(new Console($this->options));
        }
        if ($this->options['fail-fast'] > 0) {
            $this->dispatcher->addSubscriber(new FailFast($this->options['fail-fast']));
        }

        if ($this->options['coverage']) {
            $this->dispatcher->addSubscriber(new Local($this->options));
            $this->dispatcher->addSubscriber(new LocalServer($this->options));
            $this->dispatcher->addSubscriber(new RemoteServer($this->options));
            $this->dispatcher->addSubscriber(new CoveragePrinter($this->options));
        }
        $this->dispatcher->addSubscriber($this->extensionLoader);
        $this->extensionLoader->registerGlobalExtensions();
    }

    public function run(string $suite, string $test = null, array $config = null): void
    {
        ini_set(
            'memory_limit',
            $this->config['settings']['memory_limit'] ?? '1024M'
        );

        $config = $config ?: Configuration::config();
        $config = Configuration::suiteSettings($suite, $config);

        $selectedEnvironments = $this->options['env'];

        if (!$selectedEnvironments || empty($config['env'])) {
            $this->runSuite($config, $suite, $test);
            return;
        }

        // Iterate over all unique environment sets and runs the given suite with each of the merged configurations.
        foreach (array_unique($selectedEnvironments) as $envList) {
            $envSet = explode(',', $envList);
            $suiteEnvConfig = $config;

            // contains a list of the environments used in this suite configuration env set.
            $envConfigs = [];
            foreach ($envSet as $currentEnv) {
                if (isset($config['env'])) {
                    // The $settings['env'] actually contains all parsed configuration files as a
                    // filename => filecontents key-value array. If there is no configuration file for the
                    // $currentEnv the merge will be skipped.
                    if (!array_key_exists($currentEnv, $config['env'])) {
                        return;
                    }

                    // Merge configuration consecutively with already build configuration
                    $suiteEnvConfig = Configuration::mergeConfigs($suiteEnvConfig, $config['env'][$currentEnv]);
                    $envConfigs[] = $currentEnv;
                }
            }

            $suiteEnvConfig['current_environment'] = implode(',', $envConfigs);

            if (empty($suiteEnvConfig)) {
                continue;
            }
            $suiteToRun = $suite;
            if (!empty($envList)) {
                $suiteToRun .= ' (' . implode(', ', $envSet) . ')';
            }
            $this->runSuite($suiteEnvConfig, $suiteToRun, $test);
        }
    }

    public function runSuite(array $settings, string $suite, string $test = null): TestResult
    {
        $suiteManager = new SuiteManager($this->dispatcher, $suite, $settings);
        $suiteManager->initialize();
        srand($this->options['seed']);
        $suiteManager->loadTests($test);
        srand();
        $suiteManager->run($this->runner, $this->result, $this->options);
        return $this->result;
    }

    public static function versionString(): string
    {
        return 'Codeception PHP Testing Framework v' . self::VERSION;
    }

    public function printResult(): void
    {
        $result = $this->getResult();
        $this->dispatcher->dispatch(new PrintResultEvent($result, $this->printer), Events::RESULT_PRINT_AFTER);
    }

    public function getResult(): TestResult
    {
        return $this->result;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }
}
