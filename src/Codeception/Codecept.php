<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Coverage\Subscriber\Local;
use Codeception\Coverage\Subscriber\LocalServer;
use Codeception\Coverage\Subscriber\Printer as CoveragePrinter;
use Codeception\Coverage\Subscriber\RemoteServer;
use Codeception\Event\PrintResultEvent;
use Codeception\Exception\ConfigurationException;
use Codeception\Lib\Console\Output;
use Codeception\Lib\Interfaces\ConsolePrinter;
use Codeception\Lib\Notification;
use Codeception\Reporter\HtmlReporter;
use Codeception\Reporter\JUnitReporter;
use Codeception\Reporter\PhpUnitReporter;
use Codeception\Reporter\ReportPrinter;
use Codeception\Subscriber\AutoRebuild;
use Codeception\Subscriber\BeforeAfterTest;
use Codeception\Subscriber\Bootstrap;
use Codeception\Subscriber\Console;
use Codeception\Subscriber\Dependencies;
use Codeception\Subscriber\Deprecation;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Subscriber\ExtensionLoader;
use Codeception\Subscriber\FailFast;
use Codeception\Subscriber\GracefulTermination;
use Codeception\Subscriber\Module;
use Codeception\Subscriber\PrepareTest;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Codecept
{
    /**
     * @var string
     */
    public const VERSION = '5.2.0';

    protected ResultAggregator $resultAggregator;

    protected EventDispatcher $dispatcher;

    protected ExtensionLoader $extensionLoader;

    protected array $options = [
        'silent'               => false,
        'debug'                => false,
        'steps'                => false,
        'html'                 => false,
        'xml'                  => false,
        'phpunit-xml'          => false,
        'no-redirect'          => true,
        'report'               => false,
        'colors'               => false,
        'coverage'             => false,
        'coverage-xml'         => false,
        'coverage-html'        => false,
        'coverage-text'        => false,
        'coverage-crap4j'      => false,
        'coverage-cobertura'   => false,
        'coverage-phpunit'     => false,
        'disable-coverage-php' => false,
        'groups'               => null,
        'excludeGroups'        => null,
        'filter'               => null,
        'shard'                => null,
        'env'                  => null,
        'fail-fast'            => 0,
        'ansi'                 => true,
        'verbosity'            => 1,
        'interactive'          => true,
        'no-rebuild'           => false,
        'quiet'                => false,
    ];

    protected array $config = [];

    protected array $extensions = [];

    private readonly Output $output;

    public function __construct(array $options = [])
    {
        $this->resultAggregator = new ResultAggregator();
        $this->dispatcher = new EventDispatcher();
        $this->extensionLoader = new ExtensionLoader($this->dispatcher);

        $baseOptions = $this->mergeOptions($options);
        $this->extensionLoader->bootGlobalExtensions($baseOptions); // extensions may override config

        $this->config  = Configuration::config();
        $this->options = $this->mergeOptions($options); // options updated from config

        $this->output = new Output($this->options);

        $this->registerSubscribers();
    }

    /**
     * Merges given options with default values and current configuration
     *
     * @throws ConfigurationException
     */
    protected function mergeOptions(array $options): array
    {
        $config      = Configuration::config();
        $baseOptions = array_merge($this->options, $config['settings']);
        return array_merge($baseOptions, $options);
    }

    public function registerSubscribers(): void
    {
        // required
        $this->dispatcher->addSubscriber(new GracefulTermination($this->resultAggregator));
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

        if ($this->options['fail-fast'] > 0) {
            $this->dispatcher->addSubscriber(new FailFast($this->options['fail-fast'], $this->resultAggregator));
        }

        if ($this->options['coverage']) {
            $this->dispatcher->addSubscriber(new Local($this->options));
            $this->dispatcher->addSubscriber(new LocalServer($this->options));
            $this->dispatcher->addSubscriber(new RemoteServer($this->options));
            $this->dispatcher->addSubscriber(new CoveragePrinter($this->options, $this->output));
        }

        if ($this->options['report']) {
            $this->dispatcher->addSubscriber(new ReportPrinter($this->options));
        }

        $this->dispatcher->addSubscriber($this->extensionLoader);
        $this->extensionLoader->registerGlobalExtensions();

        if (!$this->options['silent'] && !$this->isConsolePrinterSubscribed()) {
            $this->dispatcher->addSubscriber(new Console($this->options));
        }

        $this->dispatcher->addSubscriber(new Deprecation($this->options));

        $this->registerReporters();
    }

    private function isConsolePrinterSubscribed(): bool
    {
        foreach ($this->dispatcher->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof ConsolePrinter) {
                    return true;
                }
                if (is_array($listener) && $listener[0] instanceof ConsolePrinter) {
                    return true;
                }
            }
        }
        return false;
    }

    private function registerReporters(): void
    {
        if (isset($this->config['reporters'])) {
            Notification::warning(
                "'reporters' option is not supported! Custom reporters must be reimplemented as extensions.",
                ''
            );
        }
        if ($this->options['html']) {
            $this->dispatcher->addSubscriber(
                new HtmlReporter($this->options, $this->output)
            );
        }
        if ($this->options['xml']) {
            $this->dispatcher->addSubscriber(
                new JUnitReporter($this->options, $this->output)
            );
        }
        if ($this->options['phpunit-xml']) {
            $this->dispatcher->addSubscriber(
                new PhpUnitReporter($this->options, $this->output)
            );
        }
    }

    public function run(string $suite, ?string $test = null, ?array $config = null): void
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
            $envSet         = explode(',', (string) $envList);
            $suiteEnvConfig = $config;

            // contains a list of the environments used in this suite configuration env set.
            $envConfigs = [];
            foreach ($envSet as $currentEnv) {
                // The $settings['env'] actually contains all parsed configuration files as a
                // filename => filecontents key-value array. If there is no configuration file for the
                // $currentEnv the merge will be skipped.
                if (!array_key_exists($currentEnv, $config['env'])) {
                    return;
                }

                // Merge configuration consecutively with already build configuration
                if (is_array($config['env'][$currentEnv])) {
                    $suiteEnvConfig = Configuration::mergeConfigs($suiteEnvConfig, $config['env'][$currentEnv]);
                }
                $envConfigs[]   = $currentEnv;
            }

            $suiteEnvConfig['current_environment'] = implode(',', $envConfigs);

            $suiteToRun = $suite;
            if (!empty($envList)) {
                $suiteToRun .= ' (' . implode(', ', $envSet) . ')';
            }
            $this->runSuite($suiteEnvConfig, $suiteToRun, $test);
        }
    }

    public function runSuite(array $settings, string $suite, ?string $test = null): void
    {
        $settings['shard'] = $this->options['shard'];
        $suiteManager = new SuiteManager($this->dispatcher, $suite, $settings, $this->options);
        $suiteManager->initialize();
        mt_srand($this->options['seed']);
        $suiteManager->loadTests($test);
        mt_srand();
        $suiteManager->run($this->resultAggregator);
    }

    public static function versionString(): string
    {
        return 'Codeception PHP Testing Framework v' . self::VERSION;
    }

    public function printResult(): void
    {
        $this->dispatcher->dispatch(new PrintResultEvent($this->resultAggregator), Events::RESULT_PRINT_AFTER);
    }

    public function getResultAggregator(): ResultAggregator
    {
        return $this->resultAggregator;
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
