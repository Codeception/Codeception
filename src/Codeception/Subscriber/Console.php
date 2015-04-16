<?php
namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Console\Output;
use Codeception\Lib\Suite;
use Codeception\TestCase;
use Codeception\TestCase\Interfaces\ScenarioDriven;
use Codeception\Util\Debug;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
    protected $columns = [40, 5];


    public function __construct($options)
    {
        $this->debug = $options['debug'] || $options['verbosity'] >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $this->steps = $this->debug || $options['steps'];
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

        if ($e->getSuite() instanceof Suite) {
            $message = $this->message(
                implode(
                    ', ', array_map(
                        function ($module) {
                            return $module->_getName();
                        }, $e->getSuite()->getModules()
                    )
                )
            );

            $message->style('info')
                ->prepend('Modules: ')
                ->writeln(OutputInterface::VERBOSITY_VERBOSE);
        }

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
            $this->message()->width($this->columns[0] + $this->columns[1], '^')->writeln();
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
        $this->output->writeln("* " . $e->getStep()->__toString());
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

        $this->printException($fail);
        $this->printExceptionTrace($fail);
    }

    protected function printException($e, $cause = null)
    {
        $class = $e instanceof \PHPUnit_Framework_ExceptionWrapper
            ? $e->getClassname()
            : get_class($e);

        if (strpos($class, 'Codeception\Exception') === 0) {
            $class = substr($class, strlen('Codeception\Exception\\'));
        }

        $this->output->writeln('');
        $message = $this->message("%s")->with($e->getMessage());
        $isFailure = $e instanceof \PHPUnit_Framework_AssertionFailedError
            || $class == 'PHPUnit_Framework_ExpectationFailedException'
            || $class == 'PHPUnit_Framework_AssertionFailedError';
        if (!$isFailure) {
            $message->prepend("[$class] ")->block("error");
        }
        if ($isFailure and $cause) {
            $message->prepend("<error>  $cause  </error>: ");
        }
        $message->writeln();

    }

    protected function printScenarioFail(ScenarioDriven $failedTest, $fail)
    {
        $feature = $failedTest->getFeature();
        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);

        $failMessage = $this->message($failedTest->getSignature())
            ->style('bold')
            ->append(' (')
            ->append(codecept_relative_path($failedTest->getFileName()))
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

        $failedStep = "";
        foreach ($failedTest->getScenario()->getSteps() as $step) {
            if ($step->hasFailed()) {
                $failedStep = (string)$step;
                break;
            }
        }
        $this->printException($fail,$failedStep);

        $this->printScenarioTrace($failedTest, $failToString);
        if ($this->output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
            $this->printExceptionTrace($fail);
            return;
        }
        if (!$fail instanceof \PHPUnit_Framework_AssertionFailedError) {
            $this->printExceptionTrace($fail);
            return;
        }
    }

    public function printExceptionTrace(\Exception $e)
    {

        static $limit = 10;

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
                foreach (['class', 'type', 'function'] as $info) {
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
            $this->printExceptionTrace($prev);
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
     * @param $failedTest
     * @param $fail
     */
    public function printScenarioTrace($failedTest)
    {
        $trace = array_reverse($failedTest->getTrace());
        $length = $i = count($trace);

        if (!$length) return;

        $this->output->writeln("\nScenario Steps:\n");

        foreach ($trace as $step) {
            $message = $this->message($i)->prepend(' ')->width(strlen($length))->append(". ".$step->getPhpCode());

            if ($step->hasFailed()) {
                $message->append(' ')->style('bold');
            }

            $line = $step->getLineNumber();
            if ($line) {
                $message->append(' <info>'.codecept_relative_path($failedTest->getFileName().":$line</info>"));
            }

            $i--;
            $message->writeln();
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
        $this->columns = [40, 5];
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
                    15 + $output_length
                );
                continue;
            }
            $this->columns[0] = max($this->columns[0], 10 + strlen($test->toString()));
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
            return $this->message = $this->message($test->toString())
                ->style('focus')
                ->prepend($inProgress ? 'Running ' : '');
        }
        $filename = $test->getSignature();

        if ($test->getFeature()) {
            return $this->message = $this->message("<focus>%s</focus> (%s) ")
                ->prepend($inProgress ? 'Trying to ' : '')
                ->with($inProgress ? $test->getFeature() : ucfirst($test->getFeature()), $filename);
        }
        return $this->message = $this->message("<focus>%s</focus> ")
            ->prepend($inProgress ? 'Running ' : '')
            ->with($filename);
    }

    protected function writeCurrentTest(\PHPUnit_Framework_TestCase $test)
    {
        if (!$this->isDetailed($test) and $this->output->isInteractive()) {
            $this->getTestMessage($test, true)->write();
            $this->message('... ')->append("\x0D")->write();
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
