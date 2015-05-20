<?php

namespace Codeception\PHPUnit\ResultPrinter;

use Codeception\Events;
use Codeception\Event\FailEvent;
use Codeception\TestCase\Test;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UI extends \PHPUnit_TextUI_ResultPrinter
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct(EventDispatcher $dispatcher, $options, $out = null)
    {
        parent::__construct($out, $options['verbosity'] > 1, $options['colors'] ? 'always' : 'never');
        $this->dispatcher = $dispatcher;
    }

    protected function printDefect(\PHPUnit_Framework_TestFailure $defect, $count)
    {
        $this->write("\n---------\n");
        $this->dispatcher->dispatch(
                         Events::TEST_FAIL_PRINT,
                         new FailEvent($defect->failedTest(), $defect->thrownException(), $count)
        );
    }

    /**
     * @param \PHPUnit_Framework_TestFailure $defect
     */
    protected function printDefectTrace(\PHPUnit_Framework_TestFailure $defect)
    {
        $this->write($defect->getExceptionAsString());
        $this->writeNewLine();

        $stackTrace = \PHPUnit_Util_Filter::getFilteredStacktrace($defect->thrownException(), false);

        foreach ($stackTrace as $i => $frame) {
            if (! isset($frame['file'])) {
                continue;
            }

            $this->write(
                 sprintf(
                     "#%d %s(%s)",
                     $i + 1,
                     $frame['file'],
                     isset($frame['line']) ? $frame['line'] : '?'
                 )
            );

            $this->writeNewLine();
        }
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if ($test instanceof Test) {
            parent::startTest($test);
        }
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        }

        $this->lastTestFailed = false;
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->lastTestFailed = true;
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->lastTestFailed = true;
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->lastTestFailed = true;
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->lastTestFailed = true;
    }
}
