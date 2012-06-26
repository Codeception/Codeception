<?php
namespace Codeception\PHPUnit;

class Runner extends \PHPUnit_TextUI_TestRunner {

    public static $persistentListeners = array();

    /**
     * @return null|\PHPUnit_TextUI_ResultPrinter
     */
    public function getPrinter() {
        return $this->printer;
    }

	public function doEnhancedRun(\PHPUnit_Framework_Test $suite, \PHPUnit_Framework_TestResult $result, array $arguments = array())
	{
	    $this->handleConfiguration($arguments);

	    if (is_integer($arguments['repeat'])) {
	        $suite = new \PHPUnit_Extensions_RepeatedTest(
	          $suite,
	          $arguments['repeat'],
	          $arguments['filter'],
	          $arguments['groups'],
	          $arguments['excludeGroups'],
	          $arguments['processIsolation']
	        );
	    }

        $result->convertErrorsToExceptions(FALSE);

	    if (!$arguments['convertNoticesToExceptions']) {
	        \PHPUnit_Framework_Error_Notice::$enabled = FALSE;
	    }

	    if (!$arguments['convertWarningsToExceptions']) {
	        \PHPUnit_Framework_Error_Warning::$enabled = FALSE;
	    }

	    if ($arguments['stopOnError']) {
	        $result->stopOnError(TRUE);
	    }

	    if ($arguments['stopOnFailure']) {
	        $result->stopOnFailure(TRUE);
	    }

	    if ($arguments['stopOnIncomplete']) {
	        $result->stopOnIncomplete(TRUE);
	    }

	    if ($arguments['stopOnSkipped']) {
	        $result->stopOnSkipped(TRUE);
	    }

	    if ($this->printer === NULL) {
	        if (isset($arguments['printer']) &&
	            $arguments['printer'] instanceof \PHPUnit_Util_Printer) {
	            $this->printer = $arguments['printer'];
	        } else {
	            $this->printer = new \Codeception\PHPUnit\ResultPrinter\UI(
	              NULL,
	              $arguments['verbose'],
	              $arguments['colors'],
	              $arguments['debug']
	            );
	        }
	    }

        if (isset($arguments['report'])) {
            if ($arguments['report']) $this->printer = new \Codeception\PHPUnit\ResultPrinter\Report();
        }

        if (empty(self::$persistentListeners)) {

            if (isset($arguments['html'])) {
                if ($arguments['html']) self::$persistentListeners[] = new \Codeception\PHPUnit\ResultPrinter\HTML(\Codeception\Configuration::logDir() . 'report.html');
            }

            if (isset($arguments['xml'])) {
                if ($arguments['xml']) self::$persistentListeners[] = new \Codeception\PHPUnit\Log\JUnit(\Codeception\Configuration::logDir() . 'report.xml', false);
            }

            if (isset($arguments['tap'])) {
                if ($arguments['tap']) self::$persistentListeners[] = new \PHPUnit_Util_Log_TAP(\Codeception\Configuration::logDir() . 'report.tap.log');
            }

            if (isset($arguments['json'])) {
                if ($arguments['json']) self::$persistentListeners[] = new \PHPUnit_Util_Log_JSON(\Codeception\Configuration::logDir() . 'report.json');
            }

            foreach (self::$persistentListeners as $listener) {
       	        $result->addListener($listener);
       	    }
        }

        $arguments['listeners'][] = $this->printer;

        // clean up listeners between suites
	    foreach ($arguments['listeners'] as $listener) {
	        $result->addListener($listener);
	    }

	    if ($arguments['strict']) {
	        $result->strictMode(TRUE);
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

}
