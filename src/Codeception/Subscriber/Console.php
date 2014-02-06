<?php
namespace Codeception\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\SuiteManager;
use Codeception\TestCase\ScenarioDriven;
use Codeception\TestCase;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Console\Output;
use Codeception\Util\Debug;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        CodeceptionEvents::SUITE_BEFORE    => 'beforeSuite',
        CodeceptionEvents::SUITE_AFTER     => 'afterSuite',
        CodeceptionEvents::TEST_BEFORE     => 'before',
        CodeceptionEvents::TEST_AFTER      => 'afterTest',
        CodeceptionEvents::TEST_START      => 'startTest',
        CodeceptionEvents::TEST_END        => 'endTest',
        CodeceptionEvents::STEP_BEFORE     => 'beforeStep',
        CodeceptionEvents::STEP_AFTER      => 'afterStep',
        CodeceptionEvents::TEST_SUCCESS    => 'testSuccess',
        CodeceptionEvents::TEST_FAIL       => 'testFail',
        CodeceptionEvents::TEST_ERROR      => 'testError',
        CodeceptionEvents::TEST_INCOMPLETE => 'testIncomplete',
        CodeceptionEvents::TEST_SKIPPED    => 'testSkipped',
        CodeceptionEvents::TEST_FAIL_PRINT => 'printFail',
    ];

    protected $steps = true;
    protected $debug = false;
    protected $color = true;
    protected $silent = false;
    protected $lastTestFailed = false;
    protected $printedTest = null;

    protected $traceLength = 5;

    protected $columns = array(40, 5);

    public function __construct($options)
    {
        $this->debug  = $options['debug'] || $options['verbosity'] >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $this->steps  = $this->debug || $options['steps'];
        $this->output = new Output($options);
        if ($this->debug) {
            Debug::setOutput($this->output);
        }
    }

    // triggered for scenario based tests: cept, cest
    public function beforeSuite(SuiteEvent $e)
    {
        $this->buildResultsTable($e);

        $this->message("%s Tests (%d) ")
            ->with(ucfirst($e->getSuite()->getName()), count($e->getSuite()->tests()))
            ->style('bold')
            ->width(array_sum($this->columns), '-')
            ->prepend("\n")
            ->writeln();

        $message = $this->message(implode(', ',array_map(function ($module) {
            return $module->getName();
        }, SuiteManager::$modules)));
        $message->style('info')
            ->prepend('Modules: ')
            ->writeln(OutputInterface::VERBOSITY_VERBOSE);

        $this->message('')->width(array_sum($this->columns), '-')->writeln(OutputInterface::VERBOSITY_VERBOSE);

    }

    // triggered for all tests
    public function startTest(TestEvent $e)
    {
        $test = $e->getTest();
        $this->printedTest = $test;

        if ($test instanceof TestCase) {
            if ($test->getFeature()) return;
        }

        $this->message($test->toString())
            ->style('focus')
            ->prepend('Running ')
            ->width($this->columns[0])
            ->write();
    }

    public function before(TestEvent $e)
    {
        $test = $e->getTest();
        $filename = $test->getFileName();

        if ($test->getFeature()) {
            $this->message("Trying to <focus>%s</focus> (%s) ")
                ->with($test->getFeature(), $filename)
                ->width($this->columns[0])
                ->write();

        } else {
            $this->message("Running <focus>%s</focus> ")
                ->with($filename)
                ->width($this->columns[0])
                ->write();
        }

        if ($this->steps && $this->isDetailed($test)) {
            $this->output->writeln("\nScenario:");
        }

    }

    public function afterTest(TestEvent $e)
    {
    }

    public function testSuccess(TestEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('PASSED')->center(' ')->style('ok')->append("\n")->writeln();
            return;
        }
        $this->message('Ok')->writeln();
    }

    public function endTest(TestEvent $e)
    {
        $this->printedTest = null;
    }

    public function testFail(FailEvent $e)
    {
        if (!$this->steps && ($e->getFail() instanceof ConditionalAssertionFailed)) {
            $this->message('[F]')->style('error')->prepend(' ')->write();
            return;
        }
        if ($this->isDetailed($e->getTest())) {
            $this->message('FAIL')->center(' ')->style('error')->append("\n")->writeln();
            return;
        }
        $this->message('Fail')->style('error')->writeln();
    }

    public function testError(FailEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('ERROR')->center(' ')->style('error')->append("\n")->writeln();
            return;
        }
        $this->message('Error')->style('error')->writeln();
    }

    public function testSkipped(FailEvent $e)
    {
        if (!$this->printedTest) {
            return;
        }
        $message = $this->message('Skipped');
        if ($this->isDetailed($e->getTest())) {
            $message->apply('strtoupper')->append("\n");
        }
        $message->writeln();
    }

    public function testIncomplete(FailEvent $e)
    {
        $message = $this->message('Incomplete');
        if ($this->isDetailed($e->getTest())) {
            $message->apply('strtoupper')->append("\n");
        }
        $message->writeln();
    }

    protected function isDetailed($test)
    {
        if ($test instanceof ScenarioDriven && $this->steps) {
            return !$test->getScenario()->isBlocked();
        };
        return false;
    }

    public function beforeStep(StepEvent $e)
    {
        if (!$this->steps or !$e->getTest() instanceof ScenarioDriven) {
            return;
        }
        $this->output->writeln("* " . $e->getStep());
    }

    public function afterStep(StepEvent $e)
    {
    }

    public function afterSuite(SuiteEvent $e)
    {
        $this->message()->width(array_sum($this->columns), '-')->writeln();
    }

    public function printFail(FailEvent $e)
    {
        $failedTest = $e->getTest();
        $fail = $e->getFail();
        $this->output->write($e->getCount() . ") ");

        if ($e->getTest() instanceof ScenarioDriven) {
            $this->printScenarioFail($failedTest, $fail);
            return;
        }

        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);
        $this->message(get_class($failedTest))
            ->append('::')
            ->append($failedTest->getName())
            ->style('bold')
            ->append("\n")
            ->append($failToString)
            ->writeln();

        $this->printException($fail);
    }

    protected function printScenarioFail(ScenarioDriven $failedTest, $fail)
    {
        $feature = $failedTest->getScenario()->getFeature();
        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);
        $failMessage = $this->message($failedTest->getFilename())->style('bold');

        if ($fail instanceof \PHPUnit_Framework_SkippedTest
            or $fail instanceof \PHPUnit_Framework_IncompleteTest
        ) {
            $this->printSkippedTest($feature, $failedTest->getFileName(), $failToString);
            return;
        }
        if ($feature) {
            $failMessage->prepend("Failed to $feature in ");
        }
        $failMessage->writeln();
        $this->printScenarioTrace($failedTest, $failToString);
        if ($this->output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
            $this->printException($fail);
            return;
        }
        if (!$fail instanceof \PHPUnit_Framework_AssertionFailedError) {
            $this->printException($fail);
            return;
        }
    }

    public function printException(\Exception $e)
    {
        static $limit = 10;
        $this->message("[%s] %s")->with(get_class($e), $e->getMessage())->block('error')->writeln(
            $e instanceof \PHPUnit_Framework_AssertionFailedError
                ? OutputInterface::VERBOSITY_DEBUG
                : OutputInterface::VERBOSITY_VERBOSE
        );

        $trace = \PHPUnit_Util_Filter::getFilteredStacktrace($e, false);
        $i = 0;
        foreach ($trace as $step) {
            $i++;

            $message = $this->message($i)->prepend('#')->width(4);
            $message->append($step['file'] . ':' . $step['line']);
            $message->writeln();

            if ($i >= $limit) {
                break;
            }
        }
    }

    protected function message($text = '')
    {
        return new Message($text, $this->output);
    }

    /**
     * Sample Message: create user in CreateUserCept.php is not ready for release
     *
     * @param $feature
     * @param $fileName
     * @param $failToString
     */
    public function printSkippedTest($feature, $fileName, $failToString)
    {
        $message = $this->message();
        if ($feature) {
            $message->append($feature)->style('focus')->append(' in ');
        }
        $message->append($fileName);
        if ($failToString) {
            $message->append(": $failToString");
        }
        $message->write(OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param $action
     * @param $failToString
     */
    public function printFailMessage($action, $failToString)
    {
        if (strpos($action, "don't") === 0) {
            $action = substr($action, 6);
            $this->output->writeln("Sorry, I unexpectedly managed to $action:\n$failToString");
        } else {
            $this->output->writeln("Sorry, I couldn't $action:\n$failToString");
        }
    }

    /**
     * @param $failedTest
     * @param $fail
     */
    public function printScenarioTrace($failedTest, $failToString)
    {
        $trace = array_reverse($failedTest->getTrace());
        $length = $i = count($trace);
        $last = array_shift($trace);
        if (!method_exists($last, 'getHumanizedAction')) {
            return;
        }
        $this->printFailMessage($last->getHumanizedAction(), $failToString);

        $this->output->writeln("Scenario Steps:");
        $this->message($last)->style('error')->prepend("$i. ")->writeln();
        foreach ($trace as $step) {
            $i--;
            $this->message($i)->width(strlen($length))->append(". $step")->writeln();
            if (($length - $i - 1) >= $this->traceLength) {
                break;
            }
        }
        $this->output->writeln("");
    }

    /**
     * @param SuiteEvent $e
     */
    protected function buildResultsTable(SuiteEvent $e)
    {
        $this->columns = array(40, 5);
        foreach ($e->getSuite()->tests() as $test) {
            if ($test instanceof TestCase) {
                $this->columns[0] = max(
                    $this->columns[0],
                    20 + strlen($test->getFeature()) + strlen($test->getFileName())
                );
                continue;
            }
            if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
                $test = $test->testAt(0);
                $output_length = $test instanceof \Codeception\TestCase
                    ? strlen($test->getFeature()) + strlen($test->getFileName())
                    : $test->toString();

                $this->columns[0] = max(
                    $this->columns[0],
                    15 +$output_length
                );
                continue;
            }
            $this->columns[0] = max($this->columns[0], 10 + strlen($test->toString()));
        }
    }

}
