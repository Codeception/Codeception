<?php
namespace Codeception;

class Runner extends \PHPUnit_TextUI_TestRunner {

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

	    if (!$arguments['convertErrorsToExceptions']) {
	        $result->convertErrorsToExceptions(FALSE);
	    }

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
	            $this->printer = new \Codeception\ResultPrinter\UI(
	              NULL,
	              $arguments['verbose'],
	              $arguments['colors'],
	              $arguments['debug']
	            );
	        }
	    }

	    if (isset($arguments['report'])) {
		    if ($arguments['report']) $this->printer = new \Codeception\ResultPrinter\Report();
	    }

	    if (isset($arguments['html'])) {

	        if ($arguments['html']) $arguments['listeners'][] = new \Codeception\ResultPrinter\HTML($arguments['html']);
	    }

        $arguments['listeners'][] = $this->printer;

	    foreach ($arguments['listeners'] as $listener) {
            $result->removeListener($listener);
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
	    $result->flushListeners();

	    return $result;
	}


}
