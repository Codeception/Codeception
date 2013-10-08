<?php
namespace Codeception\PHPUnit\ResultPrinter;

use Symfony\Component\EventDispatcher\EventDispatcher;

class UI extends \PHPUnit_TextUI_ResultPrinter {

    /**
     * @var
     */
    protected $dispatcher;

    public function __construct(EventDispatcher $dispatcher, $options, $out = null) {
        parent::__construct($out, $options['verbosity'] > 1, $options['colors']);
        $this->dispatcher = $dispatcher;
    }

    protected function printDefect(\PHPUnit_Framework_TestFailure $defect, $count)
    {
        $failedTest = $defect->failedTest();
        $this->write("\n---------\n");
        if (!($failedTest instanceof \Codeception\TestCase)) return parent::printDefect($defect, $count);
        $this->dispatcher->dispatch('test.fail.print', new \Codeception\Event\Fail($defect->failedTest(), $defect->thrownException(), $count));
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

            $this->write(sprintf("#%d %s(%s)",
                $i+1,
                $frame['file'],
                isset($frame['line']) ? $frame['line'] : '?'));

            $this->writeNewLine();
        }
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {

        if ($test instanceof \Codeception\TestCase\Test) return parent::startTest($test);
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $this->numAssertions += $test->getNumAssertions();
        $this->lastTestFailed = FALSE;
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->lastTestFailed = TRUE;
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time) {
        $this->lastTestFailed = TRUE;
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->lastTestFailed = TRUE;
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time) {
        $this->lastTestFailed = TRUE;
    }


}
