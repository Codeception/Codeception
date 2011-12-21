<?php
namespace Codeception;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Finder\Finder;
use \Symfony\Component\EventDispatcher\EventDispatcher;

class Codecept
{
    const VERSION = "0.9.5";

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
        'report' => false,
        'colors' => true,
        'log' => true
    );

    public function __construct($options = array()) {
        $this->result = new \PHPUnit_Framework_TestResult;
        $this->dispatcher = new EventDispatcher();
        $this->config = \Codeception\Configuration::config();
        $this->options = $this->mergeOptions($options);
        $this->path = $this->config['paths']['tests'];
        $this->addSubscribers();
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

    public function addSubscribers() {
        if (!$this->options['silent']) $this->dispatcher->addSubscriber(new \Codeception\Subscriber\Output($colors, $steps, $debug));
        $this->dispatcher->addSubscriber(\Codeception\Subscriber\Logger);
    }

    public function runSuite($suite, $test = null) {

        $settings = \Codeception\Configuration::suiteSettings($suite, $this->config);
        $suiteManager = new \Codeception\SuiteManager($this->dispatcher, $settings);

        $test ? $suiteManager->loadTest($test) : $suiteManager->loadTests();

        $suiteManager->run($this->result, $this->options);

        return $this->result;
    }

    public static function versionString() {
   	    return 'Codeception PHP Testing Framework v'.self::VERSION;
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
