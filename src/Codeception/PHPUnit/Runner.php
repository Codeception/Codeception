<?php

namespace Codeception\PHPUnit;

use Codeception\Configuration;
use Codeception\PHPUnit\Log\JUnit;
use Codeception\PHPUnit\ResultPrinter\HTML;
use Codeception\PHPUnit\ResultPrinter\Report;

class Runner extends \PHPUnit_TextUI_TestRunner
{
    public static $persistentListeners = array();

    protected $defaultListeners = array(
        'xml'  => false,
        'html' => false,
        'tap'  => false,
        'json' => false
    );

    protected $config = array();

    protected $logDir = null;

    protected $defaultArguments = array(
        'report' => false,
    );

    public function __construct()
    {
        $this->config  = Configuration::config();
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
        array $arguments = array()
    ) {
        unset($GLOBALS['app']); // hook for not to serialize globals

        $arguments = array_merge($this->defaultArguments, $arguments);
        $this->handleConfiguration($arguments);
        $result->convertErrorsToExceptions(false);

        if ($arguments['report']) {
            $this->printer = new Report();
        }

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
     * @param array                         $arguments
     *
     * @return array
     */
    protected function applyReporters(\PHPUnit_Framework_TestResult $result, array $arguments)
    {
        foreach ($this->defaultListeners as $listener => $value) {
            if (! isset($arguments[$listener])) {
                $arguments[$listener] = $value;
            }
        }

        if ($arguments['html']) {
            codecept_debug('Printing HTML report into '.$arguments['html']);
            self::$persistentListeners[] = new HTML($this->absolutePath($arguments['html']));
        }
        if ($arguments['xml']) {
            codecept_debug('Printing JUNIT report into '.$arguments['xml']);
            self::$persistentListeners[] = new JUnit($this->absolutePath($arguments['xml']), false);
        }
        if ($arguments['tap']) {
            codecept_debug('Printing TAP report into '.$arguments['tap']);
            self::$persistentListeners[] = new \PHPUnit_Util_Log_TAP($this->absolutePath($arguments['tap']));
        }
        if ($arguments['json']) {
            codecept_debug('Printing JSON report into '.$arguments['json']);
            self::$persistentListeners[] = new \PHPUnit_Util_Log_JSON($this->absolutePath($arguments['json']));
        }

        foreach (self::$persistentListeners as $listener) {
            $result->addListener($listener);
        }
    }

    private function absolutePath($path)
    {
        if ((strpos($path, '/') === 0) or (strpos($path, ':') === 1)) { // absolute path
            return $path;
        }
        return $this->logDir . $path;
    }

}
