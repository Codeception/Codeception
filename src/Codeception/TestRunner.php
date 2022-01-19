<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Exception\ConfigurationException;
use Codeception\PHPUnit\ConsolePrinter;
use Codeception\PHPUnit\FilterTest;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\Filter\IncludeGroupFilterIterator;
use ReflectionProperty;

class TestRunner
{
    public static $persistentListeners = [];

    protected $defaultListeners = [
        'xml'         => false,
        'phpunit-xml' => false,
        'html'        => false,
        'tap'         => false,
        'json'        => false,
        'report'      => false
    ];

    protected $config = [];

    protected $logDir = null;

    public function __construct()
    {
        $this->config = Configuration::config();
        $this->logDir = Configuration::outputDir(); // prepare log dir
    }

    public function prepareSuite(\PHPUnit\Framework\Test $suite, array $arguments)
    {
        // TODO: handle configuration
        // $this->handleConfiguration($arguments);

        $filterAdded = false;

        $filterFactory = new Factory();
        if (!empty($arguments['groups'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                IncludeGroupFilterIterator::class,
                $arguments['groups']
            );
        }

        if (!empty($arguments['excludeGroups'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                ExcludeGroupFilterIterator::class,
                $arguments['excludeGroups']
            );
        }

        if (!empty($arguments['filter'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                FilterTest::class,
                $arguments['filter']
            );
        }

        if ($filterAdded) {
            $suite->injectFilter($filterFactory);
        }
    }

    private function addFilterToFactory(Factory $filterFactory, string $filterClass, $filterParameter)
    {
        $filterReflectionClass = new \ReflectionClass($filterClass);

        $property = new ReflectionProperty(get_class($filterFactory), 'filters');
        $property->setAccessible(true);

        $filters = $property->getValue($filterFactory);
        $filters []= [
            $filterReflectionClass,
            $filterParameter,
        ];
        $property->setValue($filterFactory, $filters);
        $property->setAccessible(false);
    }

    public function doEnhancedRun(
        Suite $suite,
        TestResult $result,
        array $arguments = []
    ) {
        unset($GLOBALS['app']); // hook for not to serialize globals

        if (empty(self::$persistentListeners)) {
            $this->applyReporters($result, $arguments);
        }

        if (class_exists('\Symfony\Bridge\PhpUnit\SymfonyTestsListener')) {
            $arguments['listeners'] = isset($arguments['listeners']) ? $arguments['listeners'] : [];

            $listener = new \Symfony\Bridge\PhpUnit\SymfonyTestsListener();
            $listener->globalListenerDisabled();
            $arguments['listeners'][] = $listener;
        }

        // $arguments['listeners'][] = $this->printer;

        // clean up listeners between suites
        // TODO: fix listeners
//        foreach ($arguments['listeners'] as $listener) {
//            $result->addListener($listener);
//        }

        $suite->run($result);
        unset($suite);

        // TODO: fix listeners
//        foreach ($arguments['listeners'] as $listener) {
//            $result->removeListener($listener);
//        }

        return $result;
    }

    /**
     * @param TestResult $result
     * @param array $arguments
     *
     * @return array
     */
    protected function applyReporters(TestResult $result, array $arguments)
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
        if ($arguments['phpunit-xml']) {
            codecept_debug('Printing PHPUNIT report into ' . $arguments['phpunit-xml']);
            self::$persistentListeners[] = $this->instantiateReporter(
                'phpunit-xml',
                [$this->absolutePath($arguments['phpunit-xml']), (bool)$arguments['log_incomplete_skipped']]
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