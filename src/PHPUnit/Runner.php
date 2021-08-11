<?php
namespace Codeception\PHPUnit;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\PHPUnit\Compatibility\PHPUnit9;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\Filter\IncludeGroupFilterIterator;
use ReflectionProperty;

class Runner extends NonFinal\TestRunner
{
    public static $persistentListeners = [];

    protected $defaultListeners = [
        'xml'         => false,
        'phpunit-xml' => false,
        'html'        => false,
        'report'      => false
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

    public function setPrinter($printer)
    {
        $this->printer = $printer;
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

        $filterAdded = false;

        $filterFactory = new Factory();
        if ($arguments['groups']) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                IncludeGroupFilterIterator::class,
                $arguments['groups']
            );
        }

        if ($arguments['excludeGroups']) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                ExcludeGroupFilterIterator::class,
                $arguments['excludeGroups']
            );
        }

        if ($arguments['filter']) {
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

        if (PHPUnit9::addFilterMethodExists()) {
            $filterFactory->addFilter(
                $filterReflectionClass,
                $filterParameter
            );
        } else {
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

            $listener = new \Symfony\Bridge\PhpUnit\SymfonyTestsListener();
            $listener->globalListenerDisabled();
            $arguments['listeners'][] = $listener;
        }

        $arguments['listeners'][] = $this->printer;

        // clean up listeners between suites
        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $suite->run($result);
        unset($suite);

        if (PHPUnit9::removeListenerMethodExists($result)) {
            foreach ($arguments['listeners'] as $listener) {
                $result->removeListener($listener);
            }
        } else {
            $property = new ReflectionProperty($result, 'listeners');
            $property->setAccessible(true);
            $resultListeners = $property->getValue($result);
            foreach ($resultListeners as $key => $_listener) {
                if (in_array($_listener, $arguments['listeners'], true)) {
                    unset($resultListeners[$key]);
                }
            }
            $property->setValue($result, $resultListeners);
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
            self::$persistentListeners[] = $this->instantiateReporter('report', ['php://stdout']);
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
