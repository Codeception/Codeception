<?php
namespace Codeception\Subscriber;

use Codeception\Events;
use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\SuiteManager;
use Codeception\TestCase\Interfaces\ScenarioDriven;
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
        Events::SUITE_BEFORE    => 'beforeSuite',
        Events::SUITE_AFTER     => 'afterSuite',
        Events::TEST_BEFORE     => 'before',
        Events::TEST_AFTER      => 'afterTest',
        Events::TEST_START      => 'startTest',
        Events::TEST_END        => 'endTest',
        Events::STEP_BEFORE     => 'beforeStep',
        Events::STEP_AFTER      => 'afterStep',
        Events::TEST_SUCCESS    => 'testSuccess',
        Events::TEST_FAIL       => 'testFail',
        Events::TEST_ERROR      => 'testError',
        Events::TEST_INCOMPLETE => 'testIncomplete',
        Events::TEST_SKIPPED    => 'testSkipped',
        Events::TEST_FAIL_PRINT => 'printFail',
    ];

    /**
     * @var Message
     */
    protected $message = null;
    protected $steps = true;
    protected $debug = false;
    protected $color = true;
    protected $silent = false;
    protected $lastTestFailed = false;
    protected $printedTest = null;
    protected $rawStackTrace = false;
    protected $traceLength = 5;
    protected $columns = array(40, 5);
    protected $output;

    public function __construct($options)
    {
        $this->debug  = $options['debug'] || $options['verbosity'] >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $this->steps  = $this->debug || $options['steps'];
        $this->rawStackTrace = ($options['verbosity'] === OutputInterface::VERBOSITY_DEBUG);
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
            return $module->_getName();
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
        $this->message = null;
        $this->output->waitForDebugOutput = true;

        if (!$test instanceof TestCase) {
            $this->writeCurrentTest($test);
        }
    }

    public function before(TestEvent $e)
    {
        $test = $e->getTest();
        $this->writeCurrentTest($test);
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
        $this->writeFinishedTest($e->getTest());
        $this->message('Ok')->writeln();
    }

    public function endTest(TestEvent $e)
    {
        if (!$this->output->waitForDebugOutput) {
            $this->message()->width($this->columns[0]+$this->columns[1],'^')->writeln();
        }
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
        $this->writeFinishedTest($e->getTest());
        $this->message('Fail')->style('error')->writeln();
    }

    public function testError(FailEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('ERROR')->center(' ')->style('error')->append("\n")->writeln();
            return;
        }
        $this->writeFinishedTest($e->getTest());
        $this->message('Error')->style('error')->writeln();
    }

    public function testSkipped(FailEvent $e)
    {
        if (!$this->printedTest) {
            return;
        }
        $this->writeFinishedTest($e->getTest());
        $message = $this->message('Skipped');
        if ($this->isDetailed($e->getTest())) {
            $message->apply('strtoupper')->append("\n");
        }
        $message->writeln();
    }

    public function testIncomplete(FailEvent $e)
    {
        $this->writeFinishedTest($e->getTest());
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
        $feature = $failedTest->getFeature();
        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);
        $failMessage = $this->message($failedTest->getSignature())
            ->style('bold')
            ->append(' (')
            ->append($failedTest->getFileName())
            ->append(')');

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

        $class = $e instanceof \PHPUnit_Framework_ExceptionWrapper
            ? $e->getClassname()
            : get_class($e);

        $this->message("[%s] %s")->with($class, $e->getMessage())->block('error')->writeln(
            $e instanceof \PHPUnit_Framework_AssertionFailedError
                ? OutputInterface::VERBOSITY_DEBUG
                : OutputInterface::VERBOSITY_VERBOSE
        );

        if ($this->rawStackTrace) {
            $this->message($e->getTraceAsString())->writeln();
            return;
        }
        
        $trace = \PHPUnit_Util_Filter::getFilteredStacktrace($e, false);

        $i = 0;
        foreach ($trace as $step) {
            if ($i >= $limit) {
                break;
            }
            $i++;

            $message = $this->message($i)->prepend('#')->width(4);

            if (!isset($step['file'])) {
                foreach (['class','type','function'] as $info) {
                    if (!isset($step[$info])) {
                        continue;
                    }
                    $message->append($step[$info]);
                }
                $message->writeln();
                continue;
            }
            $message->append($step['file'] . ':' . $step['line']);
            $message->writeln();
        }

        $prev = $e->getPrevious();
        if ($prev) {
            $this->printException($prev);
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
            $this->output->writeln("Unexpectedly managed to $action:\n$failToString");
        } elseif (strpos($action, 'am ') === 0) {
            $action = substr($action, 3);
            $this->output->writeln("Can't be $action:\n$failToString");
        } else {
            $this->output->writeln("Couldn't $action:\n$failToString");
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
        $cols = $this->columns[0];
        if ((strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
            and (php_sapi_name() == "cli")
            and (getenv('TERM'))
            and (getenv('TERM') != 'unknown')
        ) {
            $cols = intval(`command -v tput >> /dev/null 2>&1 && tput cols`);
        }
        if ($cols < $this->columns[0]) {
            $this->columns[0] = $cols-$this->columns[1];
        }
    }

    /**
     * @param \PHPUnit_Framework_TestCase $test
     * @param bool $inProgress
     * @return Message
     */
    protected function getTestMessage(\PHPUnit_Framework_TestCase $test, $inProgress = false)
    {
        if (!$test instanceof TestCase) {
            return $this->message = $this->message('%s::%s')
                ->with(get_class($test), $test->getName(true))
                ->apply(function ($str) { return str_replace('with data set', "|", $str); } )
                ->cut($inProgress ? $this->columns[0]+$this->columns[1] - 15 : $this->columns[0]-1)
                ->style('focus')
                ->prepend($inProgress ? 'Running ' : '');
        }
        $filename = $test->getSignature();
        $feature = $test->getFeature();

        if ($feature) {
            return $this->message = $this->message($inProgress ? $feature : ucfirst($feature))
                ->apply(function ($str) { return str_replace('with data set', "|", $str); } )
                ->cut($inProgress ? $this->columns[0]+$this->columns[1] - 17 - strlen($filename): $this->columns[0]- 4 - strlen($filename))
                ->style('focus')
                ->prepend($inProgress ? 'Trying to ' : '')
                ->append(" ($filename)");
        }
        return $this->message = $this->message("<focus>%s</focus> ")
            ->prepend($inProgress ? 'Running ' : '')
            ->with($filename);
    }
    
    protected function writeCurrentTest(\PHPUnit_Framework_TestCase $test)
    {
        if (!$this->isDetailed($test) and $this->output->isInteractive()) {
            $this->getTestMessage($test, true)
                ->append('... ')
                ->write();
            return;
        }
        $this->getTestMessage($test)->write();
    }

    protected function writeFinishedTest(\PHPUnit_Framework_TestCase $test)
    {
        if ($this->isDetailed($test)) {
            return;
        }
        if ($this->output->isInteractive()) {
            $this->getTestMessage($test)->prepend("\x0D")->width($this->columns[0])->write();
            return;
        } 
        if ($this->message) {
            $this->message('')->width($this->columns[0] - $this->message->apply('strip_tags')->getLength())->write();
        }
    }

}
