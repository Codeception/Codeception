<?php
namespace Codeception\PHPUnit;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;

class Runner extends \PHPUnit\TextUI\TestRunner
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
     * @return null|\PHPUnit\TextUI\ResultPrinter
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    public function prepareSuite(\PHPUnit\Framework\Test $suite, array &$arguments)
    {
        $this->handleConfiguration($arguments);

        $filterFactory = new \PHPUnit\Runner\Filter\Factory();
        if ($arguments['groups']) {
            $filterFactory->addFilter(
                new \ReflectionClass('PHPUnit\Runner\Filter\IncludeGroupFilterIterator'),
                $arguments['groups']
            );
        }

        if ($arguments['excludeGroups']) {
            $filterFactory->addFilter(
                new \ReflectionClass('PHPUnit\Runner\Filter\ExcludeGroupFilterIterator'),
                $arguments['excludeGroups']
            );
        }

        if ($arguments['filter']) {
            $filterFactory->addFilter(
                new \ReflectionClass('Codeception\PHPUnit\FilterTest'),
                $arguments['filter']
            );
        }

        $suite->injectFilter($filterFactory);
    }

    public function doEnhancedRun(
        \PHPUnit\Framework\Test $suite,
        \PHPUnit\Framework\TestResult $result,
        array $arguments = []
    ) {
        unset($GLOBALS['app']); // hook for not to serialize globals

        $result->convertErrorsToExceptions(false);

        if (isset($arguments['report_useless_tests'])) {
            $result->beStrictAboutTestsThatDoNotTestAnything((bool)$arguments['report_useless_tests']);
        }

        if (isset($arguments['disallow_test_output'])) {
            $result->beStrictAboutOutputDuringTests((bool)$arguments['disallow_test_output']);
        }

        if (empty(self::$persistentListeners)) {
            $this->applyReporters($result, $arguments);
        }

        if (class_exists('\Symfony\Bridge\PhpUnit\SymfonyTestsListener')) {
            $arguments['listeners'] = isset($arguments['listeners']) ? $arguments['listeners'] : [];
            $arguments['listeners'][] = new \Symfony\Bridge\PhpUnit\SymfonyTestsListener();
        }

        $arguments['listeners'][] = $this->printer;

        // clean up listeners between suites
        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $suite->run($result);
        unset($suite);

        foreach ($arguments['listeners'] as $listener) {
            $result->removeListener($listener);
        }

        return $result;
    }

    /**
     * @param \PHPUnit\Framework\TestResult $result
     * @param array $arguments
     *
     * @return array
     */
    protected function applyReporters(\PHPUnit\Framework\TestResult $result, array $arguments)
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
            self::$persistentListeners[] = $this->instantiateReporter(
                'html',
                [$this->absolutePath($arguments['html'])]
            );
        }
        if ($arguments['xml']) {
            codecept_debug('Printing JUNIT report into ' . $arguments['xml']);
            self::$persistentListeners[] = $this->instantiateReporter(
                'xml',
                [$this->absolutePath($arguments['xml']), (bool)$arguments['log_incomplete_skipped']]
            );
        }
        if ($arguments['tap']) {
            codecept_debug('Printing TAP report into ' . $arguments['tap']);
            self::$persistentListeners[] = $this->instantiateReporter('tap', [$this->absolutePath($arguments['tap'])]);
        }
        if ($arguments['json']) {
            codecept_debug('Printing JSON report into ' . $arguments['json']);
            self::$persistentListeners[] = $this->instantiateReporter(
                'json',
                [$this->absolutePath($arguments['json'])]
            );
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
