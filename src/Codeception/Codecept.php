<?php
namespace Codeception;

use \Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

class Codecept
{
    const VERSION = "1.01a";

    /**
     * @var \Codeception\Runner
     */
    protected $runner;
    /**
     * @var \PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * @var \Monolog\Handler\StreamHandler
     */
    protected $logHandler;

    /**
     * @var array
     */
    protected $options = array(
        'silent' => false,
        'debug' => false,
        'html' => false,
        'report' => false,
        'colors' => true,
        'log' => true
    );

    public function __construct($config = array(), $options = array()) {
        $this->runner = new \Codeception\Runner();
        $this->result = new \PHPUnit_Framework_TestResult;
        $this->config = $config;
        $this->options = $this->mergeOptions($options);
        $this->path = $this->config['paths']['tests'];
        $this->output = new Output((bool)$this->options['silent'], (bool)$this->options['colors']);

        $this->initLogger();
    }

    protected function initLogger()
    {
        if (!file_exists($path = getcwd().DIRECTORY_SEPARATOR.$this->config['paths']['output']))
            throw new \Exception("Directory $path is not created. Can't write logs");

        if (!isset($this->config['settings']['log_max_files'])) $this->config['settings']['log_max_files'] = 3;

        $this->logHandler = new \Monolog\Handler\RotatingFileHandler($path, $this->config['settings']['log_max_files']);
    }
    
    private function mergeOptions($options) {
        foreach ($options as $option => $value) {
            if (isset($this->config['settings'][$option])) {
                if (!$value && $this->config['settings'][$option]) $value = $this->config['settings'][$option];
            }
            $options[$option] = $value;
        }
        if ($options['report']) {
            $options['silent'] = true;
        }
        $options['verbose'] = !$options['silent'];
        if ($options['html']) $options['html'] = $this->config['paths']['output'] . '/result.html';
        return $options;
    }

    protected function includeSuiteSettings($suite)
    {
        $globalConf = $this->config['settings'];
        $moduleConf = array('modules' => isset($this->config['modules']) ? $this->config['modules'] : array());


        $suiteConf = file_exists(getcwd().DIRECTORY_SEPARATOR.$this->path . DIRECTORY_SEPARATOR . "$suite.suite.yml") ? Yaml::parse(getcwd().DIRECTORY_SEPARATOR.$this->path . DIRECTORY_SEPARATOR .  "/$suite.suite.yml") : array();
        $suiteDistconf = file_exists(getcwd().DIRECTORY_SEPARATOR.$this->path . DIRECTORY_SEPARATOR .  "/$suite.suite.dist.yml") ? Yaml::parse(getcwd().DIRECTORY_SEPARATOR.$this->path . DIRECTORY_SEPARATOR .  "/$suite.suite.dist.yml") : array();

        $settings = array_merge_recursive($globalConf, $moduleConf, $suiteDistconf, $suiteConf);
        return $settings;
    }
    
    public function getSuites() {
        return $this->config['suites'];
    }

    public function getSuiteSettings($suite) {
        if (!in_array($suite, $this->config['suites'])) throw new \Exception("Suite $suite was not loaded");
        return $this->includeSuiteSettings($suite);
    }

    public static function loadConfiguration()
    {
        $config = file_exists('codeception.yml') ? Yaml::parse('codeception.yml') : array();
        $distConfig = file_exists('codeception.dist.yml') ? Yaml::parse('codeception.dist.yml') : array();
        $config = array_merge($distConfig, $config);

        if (!isset($config['paths'])) throw new \Codeception\Exception\Configuration('Paths not defined');

        if (isset($config['paths']['helpers'])) {
            // Helpers
            $helpers = Finder::create()->files()->name('*Helper.php')->in($config['paths']['helpers']);
            foreach ($helpers as $helper) include_once($helper);
        }

        if (isset($config['paths']['modules'])) {
            // Helpers
            $helpers = Finder::create()->files()->name('*.php')->in($config['paths']['modules']);
            foreach ($helpers as $helper) include_once($helper);
        }

        if (!isset($config['suites'])) {
            $suites = Finder::create()->files()->name('*.suite.yml')->in($config['paths']['tests']);
            $config['suites'] = array();
            foreach ($suites as $suite) {
                preg_match('~(.*?)(\.suite|\.suite\.dist)\.yml~', $suite->getFilename(), $matches);
                $config['suites'][] = $matches[1];
            }
        }
        return $config;
    }

    public function runSuite($suite, $test = null) {
        $settings = $this->includeSuiteSettings($suite);

        $suitePath = getcwd().DIRECTORY_SEPARATOR.$this->path.DIRECTORY_SEPARATOR.$suite.DIRECTORY_SEPARATOR;

        $class = $settings['suite_class'];
        if (!class_exists($class)) throw new \RuntimeException("Suite class for $suite not found");

        if (file_exists($guy = $suitePath.$settings['class_name'].'.php')) {
            require_once $guy;
        }
        if (!class_exists($settings['class_name'])) throw new \RuntimeException("No guys were found in $guy. Tried to find {$settings['class_name']} but he was not there.");

        \Codeception\SuiteManager::init($settings);

        $testManager = new \Codeception\SuiteManager(new $class, $this->options['debug']);
        if (isset($settings['bootstrap'])) $testManager->setBootstrtap($suitePath.$settings['bootstrap']);

        if ($test) {
            $testManager->loadTest($suitePath.$test);
        } else {
            $testManager->loadTests($suitePath);
        }
        $tests = $testManager->getCurrentSuite()->tests();

        foreach ($tests as $test) {
            if (method_exists($test, 'setOutput')) $test->setOutput($this->output);
            if (method_exists($test, 'setLogHandler')) $test->setLogHandler($this->logHandler);
        }

        $this->runner->perform($testManager->getCurrentSuite(), $this->result, array_merge(array('convertErrorsToExceptions' => false), $this->options));
        return $this->result;
    }

    public static function versionString() {
   	    return 'Codeception PHP Testing Framework v'.self::VERSION;
   	}

    /**
     * @return \Codeception\Runner
     */
    public function getRunner()
    {
        return $this->runner;
    }

    /**
     * @return \PHPUnit_Framework_TestResult
     */
    public function getResult()
    {
        return $this->result;
    }

    public function getOptions() {
        return $this->options;
    }

}
