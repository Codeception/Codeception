<?php
namespace Codeception\PHPUnit\ResultPrinter;

use Codeception\Event\FailEvent;
use Codeception\Events;
use Codeception\PHPUnit\Compatibility\PHPUnit10;
use Codeception\Test\Unit;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UI extends \PHPUnit\TextUI\DefaultResultPrinter
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct(EventDispatcher $dispatcher, $options, $out = null)
    {
        parent::__construct($out, $options['verbosity'] > OutputInterface::VERBOSITY_NORMAL, $options['colors'] ? 'always' : 'never');
        $this->dispatcher = $dispatcher;
    }

    protected function printDefect(\PHPUnit\Framework\TestFailure $defect, int $count): void
    {
        $this->write("\n---------\n");
        $this->dispatcher->dispatch(new FailEvent($defect->failedTest(), null, $defect->thrownException(), $count), Events::TEST_FAIL_PRINT);
    }

    /**
     * @param \PHPUnit\Framework\TestFailure $defect
     */
    protected function printDefectTrace(\PHPUnit\Framework\TestFailure $defect): void
    {
        $this->write($defect->getExceptionAsString());
        $this->writeNewLine();

        $stackTrace = \PHPUnit\Util\Filter::getFilteredStacktrace($defect->thrownException());

        foreach ($stackTrace as $i => $frame) {
            if (!isset($frame['file'])) {
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

    public function startTest(\PHPUnit\Framework\Test $test) : void
    {
        if ($test instanceof Unit) {
            parent::startTest($test);
        }
    }

    public function endTest(\PHPUnit\Framework\Test $test, float $time) : void
    {
        if ($test instanceof \PHPUnit\Framework\TestCase or $test instanceof \Codeception\Test\Test) {
            if (PHPUnit10::numberOfAssertionsPerformedMethodExists($test)) {
                $this->numAssertions += $test->numberOfAssertionsPerformed();
            } else {
                $this->numAssertions += $test->getNumAssertions();
            }
        }

        $this->lastTestFailed = false;
    }

    public function addError(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->lastTestFailed = true;
    }

    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, float $time) : void
    {
        $this->lastTestFailed = true;
    }

    public function addWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time) : void
    {
        $this->lastTestFailed = true;
    }

    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->lastTestFailed = true;
    }

    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->lastTestFailed = true;
    }

    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->lastTestFailed = true;
    }
}
