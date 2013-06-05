<?php
namespace Codeception;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Finder\Finder;
use \Symfony\Component\EventDispatcher\EventDispatcher;

class Codecept
{
    const VERSION = "1.6.2";

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
        'log' => true,
        'coverage' => false,
	    'defer-flush' => false,
        'group' => null
    );

    public function __construct($options = array()) {
        $this->result = new \PHPUnit_Framework_TestResult;
        $this->runner = new PHPUnit\Runner();

        $this->config = Configuration::config($options['config']);
        $this->options = $this->mergeOptions($options);

        $this->dispatcher = new EventDispatcher();
        $this->path = $this->config['paths']['tests'];
        $this->registerSubscribers();
        $this->registerListeners();
    }

    private function mergeOptions($options) {

        foreach ($this->options as $option => $default) {
            $value = isset($options[$option]) ? $options[$option] : $default;
            if (!$value) {
                $options[$option] = isset($this->config['settings'][$option]) ? $this->config['settings'][$option] : $this->options[$option];
            }
        }
        if ($options['report']) $options['silent'] = true;
        if ($options['group']) $options['groups'] = $options['group'];

        return $options;
    }

    protected function registerListeners() {
        $listener = new PHPUnit\Listener($this->dispatcher);
        $this->result->addListener($listener);
    }

    public function registerSubscribers() {
        $this->dispatcher->addSubscriber(new Subscriber\ErrorHandler());
        $this->dispatcher->addSubscriber(new Subscriber\Console($this->options));
        $this->dispatcher->addSubscriber(new Subscriber\Logger());
        $this->dispatcher->addSubscriber(new Subscriber\Module());
        $this->dispatcher->addSubscriber(new Subscriber\Cest());

        if ($this->options['coverage']) {
            $this->dispatcher->addSubscriber(new Subscriber\CodeCoverage($this->options));
            $this->dispatcher->addSubscriber(new Subscriber\RemoteCodeCoverage($this->options));
        }
    }

    public function runSuite($suite, $test = null) {
        $settings = Configuration::suiteSettings($suite, $this->config);

        $suiteManager = new SuiteManager($this->dispatcher, $suite, $settings);

        $test ? $suiteManager->loadTest($settings['path'].$test) : $suiteManager->loadTests();

        if (!$this->runner->getPrinter()) {
            $printer = new PHPUnit\ResultPrinter\UI($this->dispatcher, $this->options);
            $this->runner->setPrinter($printer);
        }
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

    public static function checkLastVersion()
    {
        if (! class_exists('SimpleXMLElement')) {
            return false;
        }

        $file = @file_get_contents("http://codeception.com/pear/feed.xml");
        if (! $file) {
            return '';
        }

        try {
            $feed = new \SimpleXMLElement($file, LIBXML_NOERROR);
            @$codeception = $feed->entry[0]->title;
        } catch (\Exception $e) {
            $codeception = false;
        }

        if (! $codeception) {
            return '';
        }

        preg_match('~(\d+\.)?(\d+\.)?(\*|\d+)~', $codeception[0], $version);

        return isset($version[0]) ? $version[0] : '';
    }
}
