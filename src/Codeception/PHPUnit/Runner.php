<?php
namespace Codeception\PHPUnit;

use Codeception\Configuration;
use Codeception\PHPUnit\Log\JUnit;
use Codeception\PHPUnit\ResultPrinter\HTML;
use Codeception\PHPUnit\ResultPrinter\Report;

class Runner extends \PHPUnit_TextUI_TestRunner {

    public static $persistentListeners = array();
    protected $defaultListeners = array('xml' => false, 'html' => false, 'tap' => false, 'json' => false);
    protected $config = array();
    protected $log_dir = null;
    protected $defaultArguments = array(
        'report' => false,
    );

    public function __construct()
    {
        $this->config = Configuration::config();
        $this->log_dir = Configuration::logDir(); // prepare log dir
        parent::__construct();
    }

    /**
     * @return null|\PHPUnit_TextUI_ResultPrinter
     */
    public function getPrinter() {
        return $this->printer;
    }

	public function doEnhancedRun(\PHPUnit_Framework_Test $suite, \PHPUnit_Framework_TestResult $result, array $arguments = array())
	{
        $arguments = array_merge($this->defaultArguments, $arguments);
	    $this->handleConfiguration($arguments);
        $result->convertErrorsToExceptions(false);
        
        if ($arguments['report']) $this->printer = new Report();

        if (empty(self::$persistentListeners)) $this->applyReporters($result, $arguments);

        $arguments['listeners'][] = $this->printer;

        // clean up listeners between suites
	    foreach ($arguments['listeners'] as $listener) {
	        $result->addListener($listener);
	    }

	    $suite->run(
	      $result,
	      $arguments['filter'],
	      $arguments['groups'],
	      $arguments['excludeGroups'],
	      $arguments['processIsolation']
	    );

	    unset($suite);

        foreach ($arguments['listeners'] as $listener) {
   	        $result->removeListener($listener);
   	    }

	    return $result;
	}

    /**
     * @param \PHPUnit_Framework_TestResult $result
     * @param array $arguments
     * @return array
     */
    protected function applyReporters(\PHPUnit_Framework_TestResult $result, array $arguments)
    {
        foreach ($this->defaultListeners as $listener => $value) {
            if (!isset($arguments[$listener])) $arguments[$listener] = $value;
        }

        if ($arguments['html']) self::$persistentListeners[] = new HTML($this->log_dir . 'report.html');
        if ($arguments['xml']) self::$persistentListeners[] = new JUnit($this->log_dir . 'report.xml', false);
        if ($arguments['tap']) self::$persistentListeners[] = new \PHPUnit_Util_Log_TAP($this->log_dir . 'report.tap.log');
        if ($arguments['json']) self::$persistentListeners[] = new \PHPUnit_Util_Log_JSON($this->log_dir . 'report.json');

        foreach (self::$persistentListeners as $listener) {
            $result->addListener($listener);
        }
    }

}
