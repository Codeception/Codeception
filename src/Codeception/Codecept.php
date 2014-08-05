<?php
namespace Codeception;

use Codeception\Configuration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\Finder\Finder;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use Codeception\Exception\Configuration as ConfigurationException;

class Codecept
{
    const VERSION = "2.0.5";

    /**
     * @var \Codeception\PHPUnit\Runner
     */
    protected $runner;
    /**
     * @var \PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $options = array(
        'silent' => false,
        'debug' => false,
        'steps' => false,
        'html' => false,
        'xml' => false,
        'json' => false,
        'tap' => false,
        'report' => false,
        'colors' => false,
        'coverage' => false,
        'coverage-xml' => false,
        'coverage-html' => false,
        'coverage-text' => false,
        'groups' => null,
        'excludeGroups' => null,
        'filter' => null,
        'env' => null,
        'fail-fast' => false,
        'verbosity' => 1
    );

    /**
     * @var array
     */
    protected $extensions = array();

    public function __construct($options = array()) {
        $this->result = new \PHPUnit_Framework_TestResult;
        $this->dispatcher = new EventDispatcher();
        $this->loadExtensions($options);

        $this->config = Configuration::config();
        $this->options = $this->mergeOptions($options);

        $this->registerSubscribers();
        $this->registerPHPUnitListeners();

        $printer = new PHPUnit\ResultPrinter\UI($this->dispatcher, $this->options);
        $this->runner = new PHPUnit\Runner($this->options);
        $this->runner->setPrinter($printer);
    }

    private function mergeOptions($options) {

        foreach ($this->options as $option => $default) {
            if (!isset($options[$option])) {
                $options[$option] = $default;
            }
            if (isset($this->config['settings'][$option])) {
                $options[$option] = $this->config['settings'][$option];
            }
        }
        return $options;
    }

    protected function loadExtensions($options)
    {
        $config = Configuration::config();
        // custom event listeners
        foreach ($config['extensions']['enabled'] as $extension) {
            if (!class_exists($extension)) {
                throw new ConfigurationException("Class $extension not defined. Autoload it or include into '_bootstrap.php' file of 'tests' directory");
            }
            if ($extension instanceof EventSubscriberInterface) {
                throw new ConfigurationException("Class $extension is not a EventListener. Please create it as Extension or Group class.");
            }
            $extensionConfig =  isset($config['extensions']['config'][$extension])
                ? $config['extensions']['config'][$extension]
                : [];

            $this->extensions[] = new $extension($extensionConfig, $options);
        }
    }

    protected function registerPHPUnitListeners() {
        $listener = new PHPUnit\Listener($this->dispatcher);
        $this->result->addListener($listener);
    }

    public function registerSubscribers() {
        // required
        $this->dispatcher->addSubscriber(new Subscriber\ErrorHandler());
        $this->dispatcher->addSubscriber(new Subscriber\Bootstrap());
        $this->dispatcher->addSubscriber(new Subscriber\Module());
        $this->dispatcher->addSubscriber(new Subscriber\BeforeAfterTest());
        $this->dispatcher->addSubscriber(new Subscriber\AutoRebuild());

        // optional
        if (!$this->options['silent'])    $this->dispatcher->addSubscriber(new Subscriber\Console($this->options));
        if ($this->options['fail-fast'])  $this->dispatcher->addSubscriber(new Subscriber\FailFast());

        if ($this->options['coverage']) {
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\Local($this->options));
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\LocalServer($this->options));
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\RemoteServer($this->options));
            $this->dispatcher->addSubscriber(new Coverage\Subscriber\Printer($this->options));
        }

        // extensions
        foreach ($this->extensions as $subscriber) {
            $this->dispatcher->addSubscriber($subscriber);
        }
    }

    public function run($suite, $test = null)
    {
        ini_set('memory_limit', isset($this->config['settings']['memory_limit']) ? $this->config['settings']['memory_limit'] : '1024M');
        $settings = Configuration::suiteSettings($suite, Configuration::config());

        $selectedEnvironments = $this->options['env'];
        $environments = Configuration::suiteEnvironments($suite);

        if (!$selectedEnvironments or empty($environments)) {
            $this->runSuite($settings, $suite, $test);
            return;
        }

        foreach ($environments as $env => $config) {
            if (!in_array($env, $selectedEnvironments)) {
                continue;
            }
            $suiteToRun = is_int($env) ? $suite : "{$suite}-{$env}";
            $this->runSuite($config, $suiteToRun, $test);
        }
    }

    public function runSuite($settings, $suite, $test = null) {
        $suiteManager = new SuiteManager($this->dispatcher, $suite, $settings);
        $suiteManager->initialize();
        $suiteManager->loadTests($test);
        $suiteManager->run($this->runner, $this->result, $this->options);

        return $this->result;
    }

    public static function versionString() {
        return 'Codeception PHP Testing Framework v'.self::VERSION;
    }

    public function printResult() {
        $result = $this->getResult();
        $result->flushListeners();

        $printer = $this->runner->getPrinter();
        $printer->printResult($result);

        $this->dispatcher->dispatch(Events::RESULT_PRINT_AFTER, new Event\PrintResultEvent($result, $printer));
    }

    /**
     * @return \PHPUnit_Framework_TestResult
     */
    public function getResult() {
        return $this->result;
    }

    public function getOptions() {
        return $this->options;
    }

    /**
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
}
