<?php
namespace Codeception\PHPUnit;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;

class Runner extends \PHPUnit_TextUI_TestRunner
{
    public static $persistentListeners = [];

    protected $defaultListeners = [
        'xml'  => false,
        'html' => false,
        'tap'  => false,
        'json' => false,
        'report' => false
    ];

    protected $config = [];

    protected $logDir = null;

    public function __construct()
    {
        $this->config = Configuration::config();
        $this->logDir = Configuration::outputDir(); // prepare log dir
        $this->phpUnitOverriders();
        parent::__construct();
    }

    public function phpUnitOverriders()
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Overrides/Filter.php';
    }

    /**
     * @return null|\PHPUnit_TextUI_ResultPrinter
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    public function doEnhancedRun(
        \PHPUnit_Framework_Test $suite,
        \PHPUnit_Framework_TestResult $result,
        array $arguments = []
    ) {
        unset($GLOBALS['app']); // hook for not to serialize globals

        $this->handleConfiguration($arguments);
        $result->convertErrorsToExceptions(false);

        if (empty(self::$persistentListeners)) {
            $this->applyReporters($result, $arguments);
        }

        $arguments['listeners'][] = $this->printer;

        // clean up listeners between suites
        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $filterFactory = new \PHPUnit_Runner_Filter_Factory();
        if ($arguments['groups']) {
            $filterFactory->addFilter(
                new \ReflectionClass('PHPUnit_Runner_Filter_Group_Include'),
                $arguments['groups']
            );
        }

        if ($arguments['excludeGroups']) {
            $filterFactory->addFilter(
                new \ReflectionClass('PHPUnit_Runner_Filter_Group_Exclude'),
                $arguments['excludeGroups']
            );
        }

        if ($arguments['filter']) {
            $filterFactory->addFilter(
                new \ReflectionClass('PHPUnit_Runner_Filter_Test'),
                $arguments['filter']
            );
        }

        $suite->injectFilter($filterFactory);

        $suite->run($result);
        unset($suite);

        foreach ($arguments['listeners'] as $listener) {
            $result->removeListener($listener);
        }

        return $result;
    }

    /**
     * @param \PHPUnit_Framework_TestResult $result
     * @param array $arguments
     *
     * @return array
     */
    protected function applyReporters(\PHPUnit_Framework_TestResult $result, array $arguments)
    {
        foreach ($this->defaultListeners as $listener => $value) {
            if (!isset($arguments[$listener])) {
                $arguments[$listener] = $value;
            }
        }

        if ($arguments['report']) {
            self::$persistentListeners[] = $this->instantiateReporter('report');
        }

        if ($arguments['html']) {
            codecept_debug('Printing HTML report into ' . $arguments['html']);
            self::$persistentListeners[] = $this->instantiateReporter('html', [$this->absolutePath($arguments['html'])]);
        }
        if ($arguments['xml']) {
            codecept_debug('Printing JUNIT report into ' . $arguments['xml']);
            self::$persistentListeners[] = $this->instantiateReporter('xml', [$this->absolutePath($arguments['xml']), false]);
        }
        if ($arguments['tap']) {
            codecept_debug('Printing TAP report into ' . $arguments['tap']);
            self::$persistentListeners[] = $this->instantiateReporter('tap', [$this->absolutePath($arguments['tap'])]);
        }
        if ($arguments['json']) {
            codecept_debug('Printing JSON report into ' . $arguments['json']);
            self::$persistentListeners[] = $this->instantiateReporter('json', [$this->absolutePath($arguments['json'])]);
        }
        
        foreach (self::$persistentListeners as $listener) {
            if ($listener instanceof ConsolePrinter) {
                $this->printer = $listener;
                continue;
            }
            $result->addListener($listener);
        }
    }

    protected function instantiateReporter($name, $args = [])
    {
        if (!isset($this->config['reporters'][$name])) {
            throw new ConfigurationException("Reporter $name not defined");
        }
        return (new \ReflectionClass($this->config['reporters'][$name]))->newInstanceArgs($args);

    }

    private function absolutePath($path)
    {
        if ((strpos($path, '/') === 0) or (strpos($path, ':') === 1)) { // absolute path
            return $path;
        }
        return $this->logDir . $path;
    }
}
