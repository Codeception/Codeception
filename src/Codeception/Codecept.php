<?php
namespace Codeception;

use Codeception\Exception\ConfigurationException;
use Codeception\Subscriber\ExtensionLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Codecept
{
    const VERSION = "2.3.6";

    /**
     * @var \Codeception\PHPUnit\Runner
     */
    protected $runner;
    /**
     * @var \PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * @var \Codeception\CodeCoverage
     */
    protected $coverage;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var ExtensionLoader
     */
    protected $extensionLoader;

    /**
     * @var array
     */
    protected $options = [
        'silent'          => false,
        'debug'           => false,
        'steps'           => false,
        'html'            => false,
        'xml'             => false,
        'json'            => false,
        'tap'             => false,
        'report'          => false,
        'colors'          => false,
        'coverage'        => false,
        'coverage-xml'    => false,
        'coverage-html'   => false,
        'coverage-text'   => false,
        'coverage-crap4j' => false,
        'groups'          => null,
        'excludeGroups'   => null,
        'filter'          => null,
        'env'             => null,
        'fail-fast'       => false,
        'ansi'            => true,
        'verbosity'       => 1,
        'interactive'     => true,
        'no-rebuild'      => false,
        'quiet'           => false,
    ];

    protected $config = [];

    /**
     * @var array
     */
    protected $extensions = [];

    public function __construct($options = [])
    {
        $this->result = new \PHPUnit_Framework_TestResult;
        $this->dispatcher = new EventDispatcher();
        $this->extensionLoader = new ExtensionLoader($this->dispatcher);

        $baseOptions = $this->mergeOptions($options);
        $this->extensionLoader->bootGlobalExtensions($baseOptions); // extensions may override config

        $this->config = Configuration::config();
        $this->options = $this->mergeOptions($options); // options updated from config

        $this->registerSubscribers();
        $this->registerPHPUnitListeners();
        $this->registerPrinter();
    }

    /**
     * Merges given options with default values and current configuration
     *
     * @param array $options options
     * @return array
     * @throws ConfigurationException
     */
    protected function mergeOptions($options)
    {
        $config = Configuration::config();
        $baseOptions = array_merge($this->options, $config['settings']);
        return array_merge($baseOptions, $options);
    }

    protected function registerPHPUnitListeners()
    {
        $listener = new PHPUnit\Listener($this->dispatcher);
        $this->result->addListener($listener);
    }

    public function registerSubscribers()
    {
        // required
        $this->dispatcher->addSubscriber(new Subscriber\GracefulTermination());
        $this->dispatcher->addSubscriber(new Subscriber\ErrorHandler());
        $this->dispatcher->addSubscriber(new Subscriber\Dependencies());
        $this->dispatcher->addSubscriber(new Subscriber\Bootstrap());
        $this->dispatcher->addSubscriber(new Subscriber\PrepareTest());
        $this->dispatcher->addSubscriber(new Subscriber\Module());
        $this->dispatcher->addSubscriber(new Subscriber\BeforeAfterTest());

        // optional
        if (!$this->options['no-rebuild']) {
            $this->dispatcher->addSubscriber(new Subscriber\AutoRebuild());
        }
        if (!$this->options['silent']) {
            $this->dispatcher->addSubscriber(new Subscriber\Console($this->options));
        }
        if ($this->options['fail-fast']) {
            $this->dispatcher->addSubscriber(new Subscriber\FailFast());
        }

        if ($this->options['coverage']) {
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\Local($this->options));
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\LocalServer($this->options));
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\RemoteServer($this->options));
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\Printer($this->options));
        }
        $this->dispatcher->addSubscriber($this->extensionLoader);
        $this->extensionLoader->registerGlobalExtensions();
    }

    public function run($suite, $test = null, array $config = null)
    {
        ini_set(
            'memory_limit',
            isset($this->config['settings']['memory_limit']) ? $this->config['settings']['memory_limit'] : '1024M'
        );

        $config = $config ?: Configuration::config();

        $settings = Configuration::suiteSettings($suite, $config);

        $selectedEnvironments = $this->options['env'];
        $environments = Configuration::suiteEnvironments($suite);

        if (!$selectedEnvironments or empty($environments)) {
            $this->runSuite($settings, $suite, $test);
            return;
        }

        foreach (array_unique($selectedEnvironments) as $envList) {
            $envArray = explode(',', $envList);
            $config = [];
            foreach ($envArray as $env) {
                if (isset($environments[$env])) {
                    $currentEnvironment = isset($config['current_environment']) ? [$config['current_environment']] : [];
                    $config = Configuration::mergeConfigs($config, $environments[$env]);
                    $currentEnvironment[] = $config['current_environment'];
                    $config['current_environment'] = implode(',', $currentEnvironment);
                }
            }
            if (empty($config)) {
                continue;
            }
            $suiteToRun = $suite;
            if (!empty($envList)) {
                $suiteToRun .= ' (' . implode(', ', $envArray) . ')';
            }
            $this->runSuite($config, $suiteToRun, $test);
        }
    }

    public function runSuite($settings, $suite, $test = null)
    {
        $suiteManager = new SuiteManager($this->dispatcher, $suite, $settings);
        $suiteManager->initialize();
        $suiteManager->loadTests($test);
        $suiteManager->run($this->runner, $this->result, $this->options);
        return $this->result;
    }

    public static function versionString()
    {
        return 'Codeception PHP Testing Framework v' . self::VERSION;
    }

    public function printResult()
    {
        $result = $this->getResult();
        $result->flushListeners();

        $printer = $this->runner->getPrinter();
        $printer->printResult($result);

        $this->dispatcher->dispatch(Events::RESULT_PRINT_AFTER, new Event\PrintResultEvent($result, $printer));
    }

    /**
     * @return \PHPUnit_Framework_TestResult
     */
    public function getResult()
    {
        return $this->result;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    protected function registerPrinter()
    {
        $printer = new PHPUnit\ResultPrinter\UI($this->dispatcher, $this->options);
        $this->runner = new PHPUnit\Runner();
        $this->runner->setPrinter($printer);
    }
}
