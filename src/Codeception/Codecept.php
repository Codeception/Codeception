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
        $this->logHandler = new \Monolog\Handler\RotatingFileHandler($this->config['paths']['output'].'/codeception.log', $this->config['settings']['log_max_files']);

    }
    
    private function mergeOptions($options) {
        foreach ($options as $option => $value) {
            if (isset($this->config['settings'][$option])) {
                if (!$value && $this->config['settings'][$option]) $value = $this->config['settings'][$option];
            }
            // no colors on windows
            if ($option == 'colors' && !$options['colors'] && strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
                $value = false;
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

        if (!isset($config['suites'])) {
            $suites = Finder::create()->files()->name('*.suite.yml')->in($config['paths']['tests']);
            $config['suites'] = array();
            foreach ($suites as $suite) {
                preg_match('~\/(.*?)(\.suite|\.suite\.dist)\.yml~', $suite, $matches);
                $config['suites'][] = $matches[1];
            }
        }
        return $config;
    }


    public function runSuite($suite, $settings, $test = null) {
        $class = $settings['suite_class'];
        if (!class_exists($class)) throw new \RuntimeException("Suite class for $suite not found");

        if (file_exists($testguy = sprintf('%s/%s/%s.php', $this->path, $suite, $settings['class_name']))) {
            require_once $testguy;
        }
        if (!class_exists($settings['class_name'])) throw new \RuntimeException("No guys were found in $testguy. Tried to find {$settings['class_name']} but he was not there.");

        \Codeception\SuiteManager::init($settings);

        $testManager = new \Codeception\SuiteManager(new $class, $this->options['debug']);
        if (isset($settings['bootstrap'])) $testManager->setBootstrtap($settings['bootstrap']);

        if ($test) {
            $testManager->loadTest($test, $this->path.'/'.$suite);
        } else {
            $testManager->loadTests($this->path.'/'.$suite);
        }
        $tests = $testManager->getCurrentSuite()->tests();

        foreach ($tests as $test) {
            if (method_exists($test, 'setOutput')) $test->setOutput($this->output);
            if (method_exists($test, 'setLogHandler')) $test->setLogHandler($this->logHandler);
        }

        $this->runner->doRun($testManager->getCurrentSuite(), $this->result, array_merge(array('convertErrorsToExceptions' => false), $this->options));
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
