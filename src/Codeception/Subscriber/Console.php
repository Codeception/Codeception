<?php
namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Console\Output;
use Codeception\Lib\Notification;
use Codeception\Lib\Suite;
use Codeception\Step;
use Codeception\Step\Comment;
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
        Events::RESULT_PRINT_AFTER => 'afterResult'
    ];

    /**
     * @var Step
     */
    protected $metaStep;

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
    protected $output;
    protected $options;
    protected $fails = [];
    protected $reports = [];
    protected $namespace = '';

    public function __construct($options)
    {
        $this->options = $options;
        $this->debug = $options['debug'] || $options['verbosity'] >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $this->steps = $this->debug || $options['steps'];
        $this->rawStackTrace = ($options['verbosity'] === OutputInterface::VERBOSITY_DEBUG);
        $this->output = new Output($options);
        if ($this->debug) {
            Debug::setOutput($this->output);
        }

        foreach (['html', 'xml', 'tap', 'json'] as $report) {
            if (!$this->options[$report]) {
                continue;
            }
            $path = $this->absolutePath($this->options[$report]);
            $this->reports[] = sprintf(
                "- <bold>%s</bold> report generated in <comment>file://%s</comment>",
                strtoupper($report),
                $path
            );
        }
    }

    // triggered for scenario based tests: cept, cest
    public function beforeSuite(SuiteEvent $e)
    {
        $this->namespace = "";
        $settings = $e->getSettings();
        if (isset($settings['namespace'])) {
            $this->namespace = $settings['namespace'];
        }
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
        $this->fails = [];
        $test = $e->getTest();
        $this->printedTest = $test;
        $this->message = null;
        $this->output->waitForDebugOutput = true;

        $this->writeCurrentTest($test);
        if ($this->steps && $this->isDetailed($test)) {
            $this->output->writeln("\nScenario:");
        }
    }

    public function afterStep(StepEvent $e)
    {
        $step = $e->getStep();
        if ($step->hasFailed() and $step instanceof Step\ConditionalAssertion) {
            $this->fails[] = $step;
        }
    }

    public function afterResult()
    {
        foreach ($this->reports as $message) {
            $this->output->writeln($message);
        }
    }

    private function absolutePath($path)
    {
        if ((strpos($path, '/') === 0) or (strpos($path, ':') === 1)) { // absolute path
            return $path;
        }
        return codecept_output_dir() . $path;
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
        $this->metaStep = null;
        $this->printedTest = null;
    }

    public function testFail(FailEvent $e)
    {
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
        $metaStep = $e->getStep()->getMetaStep();
        if ($metaStep and $this->metaStep != $metaStep) {
            $this->output->writeln("* $metaStep");
        }
        $this->metaStep = $metaStep;
        $msg = $this->message($e->getStep()->__toString());
        $this->metaStep ? $msg->prepend('  ')->style('comment') : $msg->prepend('* ');
        $msg->writeln();
    }

    public function afterSuite(SuiteEvent $e)
    {
        $this->message()->width(array_sum($this->columns), '-')->writeln();
        $deprecationMessages = Notification::all();
        foreach ($deprecationMessages as $message) {
            $this->output->notification($message);
        }
    }

    public function printFail(FailEvent $e)
    {
        $failedTest = $e->getTest();
        $fail = $e->getFail();
        
        $this->output->write($e->getCount() . ") ");

        if ($failedTest instanceof ScenarioDriven) {
            $this->printScenarioFail($failedTest, $fail);
            return;
        }
        $this->getTestMessage($failedTest)->write();

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
        if ($isFailure && $cause) {
            $message->prepend("<error> Step </error> $cause\n<error> Fail </error> ");
        }
        if ($e instanceof \PHPUnit_Framework_ExpectationFailedException) {
            if ($e->getComparisonFailure()) {
                $message->append(trim($e->getComparisonFailure()->getDiff()));
            }
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
            || $fail instanceof \PHPUnit_Framework_IncompleteTest
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

        if ($e instanceof \PHPUnit_Framework_SkippedTestError) {
            return;
        }
        if ($e instanceof \PHPUnit_Framework_IncompleteTestError) {
            return;
        }

        if ($this->rawStackTrace) {
            $this->message(\PHPUnit_Util_Filter::getFilteredStacktrace($e, true, false))->writeln();
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
    public function printScenarioTrace(ScenarioDriven $failedTest)
    {
        $trace = array_reverse($failedTest->getScenario()->getSteps());
        $length = $i = count($trace);

        if (!$length) return;

        $this->message("\nScenario Steps:\n")->style('comment')->writeln();

        foreach ($trace as $step) {

            $message = $this
                ->message($i)
                ->prepend(' ')
                ->width(strlen($length))
                ->append(". " . $step->getPhpCode());

            if ($step->hasFailed()) {
                $message->append('')->style('bold');
            }

            $line = $step->getLine();
            if ($line and (!$step instanceof Comment)) {
                $message->append(" at <info>$line</info>");
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
                $output_length = $test instanceof TestCase
                    ? strlen($test->getFeature()) + strlen($test->getFileName())
                    : strlen($test->toString());

                $this->columns[0] = max(
                    $this->columns[0],
                    15 + $output_length
                );
                continue;
            }
            $this->columns[0] = max($this->columns[0], 10 + strlen($test->toString()));
        }
        $cols = $this->columns[0];
        if ((strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
            && (php_sapi_name() == "cli")
            && (getenv('TERM'))
            && (getenv('TERM') != 'unknown')
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
            $this->message = $this
                ->message('%s::%s')
                ->with($this->cutNamespace(get_class($test)), $test->getName(true))
                ->apply(function ($str) { return str_replace('with data set', "|", $str); } )
                ->cut($inProgress ? $this->columns[0] + $this->columns[1] - 16 : $this->columns[0] - 2)
                ->style('focus')
                ->prepend($inProgress ? 'Running ' : '');
            return $this->message;
        }
        $filename = $this->cutNamespace($test->getSignature());
        $feature = $test->getFeature();

        if ($feature) {
            $this->message = $this
                ->message($inProgress ? $feature : ucfirst($feature))
                ->apply(function ($str) { return str_replace('with data set', "|", $str); } )
                ->cut($inProgress ? $this->columns[0] + $this->columns[1] - 18 - strlen($filename) : $this->columns[0] - 5 - strlen($filename))
                ->style('focus')
                ->prepend($inProgress ? 'Trying to ' : '')
                ->append(" ($filename)");
            return $this->message;
        }
        
        $this->message = $this
            ->message("<focus>%s</focus> ")
            ->prepend($inProgress ? 'Running ' : '')
            ->with($filename);
        return $this->message;
    }

    private function cutNamespace($className)
    {
        if (!$this->namespace) {
            return $className;
        }
        if (strpos($className, $this->namespace) === 0) {
            return substr($className, strlen($this->namespace)+1);
        }
        return $className;
    }

    protected function writeCurrentTest(\PHPUnit_Framework_TestCase $test)
    {
        if (!$this->isDetailed($test) && $this->output->isInteractive()) {
            $this
                ->getTestMessage($test, true)
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

        $conditionalFails = "";
        $numFails  = count($this->fails);
        if ($numFails == 1) {
            $conditionalFails = "[F]";
        } elseif ($numFails) {
            $conditionalFails = "{$numFails}x[F]";
        }
        $conditionalLen = strlen($conditionalFails)+1;
        $conditionalFails = "<error>$conditionalFails</error> ";

        if ($this->output->isInteractive()) {
            $msg = $this->getTestMessage($test)->prepend("\x0D");
            $msg->width($this->columns[0] - $conditionalLen)->append($conditionalFails)->write();
            return;
        }
        if ($this->message) {
            $this->message('')
                ->width($this->columns[0] - $this->message->apply('strip_tags')->getLength() - $conditionalLen)
                ->append($conditionalFails)
                ->write();
        }
    }
}
