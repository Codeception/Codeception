<?php
namespace Codeception;

use Codeception\Configuration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\Finder\Finder;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use Codeception\Exception\Configuration as ConfigurationException;

class Codecept
{
    const VERSION = "1.8.2";

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
     * @var \Monolog\Handler\StreamHandler
     */
    protected $logHandler;

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
        'log' => false,
        'coverage' => false,
	    'defer-flush' => false,
        'groups' => null,
        'excludeGroups' => null,
        'filter' => null,
        'env' => null,
    );

    public function __construct($options = array()) {
        $this->result = new \PHPUnit_Framework_TestResult;

        $this->config = Configuration::config();
        $this->options = $this->mergeOptions($options);


        $this->dispatcher = new EventDispatcher();
        $this->registerSubscribers();
        $this->registerPHPUnitListeners();

        $printer = new PHPUnit\ResultPrinter\UI($this->dispatcher, $this->options);
        $this->runner = new PHPUnit\Runner($this->config);
        $this->runner->setPrinter($printer);
    }

    private function mergeOptions($options) {

        foreach ($this->options as $option => $default) {
            $value = isset($options[$option]) ? $options[$option] : $default;
            if (!$value) {
                $options[$option] = isset($this->config['settings'][$option])
                    ? $this->config['settings'][$option]
                    : $this->options[$option];
            }
        }
        if (isset($options['no-colors']) && $options['no-colors']) $options['colors'] = false;
        if (isset($options['report']) && $options['report']) $options['silent'] = true;
        if (isset($options['group']) && $options['group']) $options['groups'] = $options['group'];
        if (isset($options['skip-group']) && $options['skip-group']) $options['excludeGroups'] = $options['skip-group'];

        return $options;
    }

    protected function registerPHPUnitListeners() {
        $listener = new PHPUnit\Listener($this->dispatcher);
        $this->result->addListener($listener);
    }

    public function registerSubscribers() {
        // required
        $this->dispatcher->addSubscriber(new Subscriber\ErrorHandler());
        $this->dispatcher->addSubscriber(new Subscriber\Module());
        $this->dispatcher->addSubscriber(new Subscriber\Cest());
        $this->dispatcher->addSubscriber(new Subscriber\BeforeAfterClass());

        // optional
        if (!$this->options['silent'])  $this->dispatcher->addSubscriber(new Subscriber\Console($this->options));
        if ($this->options['log'])      $this->dispatcher->addSubscriber(new Subscriber\Logger());
        if ($this->options['coverage']) {
            $this->dispatcher->addSubscriber(new Subscriber\CodeCoverage($this->options));
            $this->dispatcher->addSubscriber(new Subscriber\RemoteCodeCoverage($this->options));
        }

        // custom event listeners
        foreach ($this->config['extensions']['enabled'] as $subscriber) {
            if (!class_exists($subscriber)) throw new ConfigurationException("Class $subscriber not defined. Please include it in global '_bootstrap.php' file of 'tests' directory");
            if ($subscriber instanceof EventSubscriberInterface) throw new ConfigurationException("Class $subscriber is not a EventListener. Please create it as Extension or Group class.");
            $this->dispatcher->addSubscriber(new $subscriber($this->config, $this->options));
        }
    }

    public function run($suite, $test = null)
    {
        ini_set('memory_limit', isset($this->config['settings']['memory_limit']) ? $this->config['settings']['memory_limit'] : '1024M');
        $settings = Configuration::suiteSettings($suite, Configuration::config());

        $selectedEnvironments = $this->options['env'];
        $environments = \Codeception\Configuration::suiteEnvironments($suite);

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

        $test
            ? $suiteManager->loadTest($settings['path'].$test)
            : $suiteManager->loadTests();

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

        $this->dispatcher->dispatch('result.print.after', new Event\PrintResult($result, $printer));
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
