<?php
namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Console\Output;
use Codeception\Lib\Notification;
use Codeception\Step;
use Codeception\Step\Comment;
use Codeception\Suite;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
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
    protected $width;
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
        $this->detectWidth();

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
        $this->message("%s Tests (%d) ")
            ->with(ucfirst($e->getSuite()->getName()), count($e->getSuite()->tests()))
            ->style('bold')
            ->width($this->width, '-')
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

        $this->message('')->width($this->width, '-')->writeln(OutputInterface::VERBOSITY_VERBOSE);
    }

    // triggered for all tests
    public function startTest(TestEvent $e)
    {
        $this->fails = [];
        $test = $e->getTest();
        $this->printedTest = $test;
        $this->message = null;

        if (!$this->output->isInteractive() and !$this->isDetailed($test)) {
            return;
        }
        $this->writeCurrentTest($test);
        if ($this->steps && $this->isDetailed($test)) {
            $this->message('Scenario --')->style('comment')->prepend("\n")->writeln();
            $this->output->waitForDebugOutput = false;
        }
    }

    public function afterStep(StepEvent $e)
    {
        $step = $e->getStep();
        if ($step->hasFailed() and $step instanceof Step\ConditionalAssertion) {
            $this->fails[] = $step;
        }
    }

    /**
     * @param PrintResultEvent $event
     */
    public function afterResult(PrintResultEvent $event)
    {
        $result = $event->getResult();
        if ($result->skippedCount() + $result->notImplementedCount() > 0 and $this->options['verbosity'] < OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln("run with `-v` to get more info about skipped or incomplete tests");
        }
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
        $this->writelnFinishedTest($e, $this->message($this->isWin() ? "+" : '✔')->style('ok'));
    }

    public function endTest(TestEvent $e)
    {
        $this->metaStep = null;
        $this->printedTest = null;
    }

    public function testFail(FailEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('FAIL')->center(' ')->style('fail')->append("\n")->writeln();
            return;
        }
        $this->writelnFinishedTest($e, $this->message($this->isWin() ? "x" : '✖')->style('fail'));
    }

    public function testError(FailEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('ERROR')->center(' ')->style('fail')->append("\n")->writeln();
            return;
        }
        $this->writelnFinishedTest($e, $this->message('E')->style('fail'));
    }

    public function testSkipped(FailEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $msg = $e->getFail()->getMessage();
            $this->message('SKIPPED')->append($msg ? ": $msg" : '')->center(' ')->style('pending')->writeln();
            return;
        }
        $this->writelnFinishedTest($e, $this->message('S')->style('pending'));
    }

    public function testIncomplete(FailEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $msg = $e->getFail()->getMessage();
            $this->message('INCOMPLETE')->append($msg ? ": $msg" : '')->center(' ')->style('pending')->writeln();
            return;
        }
        $this->writelnFinishedTest($e, $this->message('I')->style('pending'));
    }

    protected function isDetailed($test)
    {
        if ($test instanceof ScenarioDriven && $this->steps) {
            return !$test->getMetadata()->isBlocked();
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
            $this->message(' ' . $metaStep->getPrefix())
                ->style('bold')
                ->append($metaStep->__toString())
                ->writeln();
        }
        $this->metaStep = $metaStep;

        $this->printStep($e->getStep());
    }

    private function printStep(Step $step)
    {
        if ($step instanceof Comment and $step->__toString() == '') {
            return; // don't print empty comments
        }
        $msg = $this->message(' ');
        if ($this->metaStep) {
            $msg->append('  ');
        }
        $msg->append($step->getPrefix());
        $prefixLength = $msg->getLength();
        if (!$this->metaStep) {
            $msg->style('bold');
        }
        $maxLength = $this->width - $prefixLength;
        $msg->append($step->toString($maxLength));
        if ($this->metaStep) {
            $msg->style('info');
        }
        $msg->writeln();
    }

    public function afterSuite(SuiteEvent $e)
    {
        $this->message()->width($this->width, '-')->writeln();
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

        $this->writeCurrentTest($failedTest, false);
        if ($failedTest instanceof ScenarioDriven) {
            $this->printScenarioFail($failedTest, $fail);
            return;
        }

        $this->printException($fail);
        $this->printExceptionTrace($fail);
    }

    protected function printException($e, $cause = null)
    {
        if ($e instanceof \PHPUnit_Framework_SkippedTestError or $e instanceof \PHPUnit_Framework_IncompleteTestError) {
            if ($e->getMessage()) {
                $this->message($e->getMessage())->prepend("\n")->writeln();
            }
            return;
        }

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
        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);

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

        if ($e instanceof \PHPUnit_Framework_SkippedTestError or $e instanceof \PHPUnit_Framework_IncompleteTestError) {
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
     */
    public function printScenarioTrace(ScenarioDriven $failedTest)
    {
        $trace = array_reverse($failedTest->getScenario()->getSteps());
        $length = $stepNumber = count($trace);

        if (!$length) {
            return;
        }

        $this->message("\nScenario Steps:\n")->style('comment')->writeln();

        foreach ($trace as $step) {
            /**
             * @var $step Step
             */
            if (!$step->__toString()) {
                continue;
            }

            $message = $this
                ->message($stepNumber)
                ->prepend(' ')
                ->width(strlen($length))
                ->append(". " );
            $message->append($step->getPhpCode($this->width - $message->getLength()));

            if ($step->hasFailed()) {
                $message->style('bold');
            }

            $line = $step->getLine();
            if ($line and (!$step instanceof Comment)) {
                $message->append(" at <info>$line</info>");
            }

            $stepNumber--;
            $message->writeln();
            if (($length - $stepNumber - 1) >= $this->traceLength) {
                break;
            }
        }
        $this->output->writeln("");
    }

    protected function detectWidth()
    {
        $this->width = 60;
        if (!$this->isWin()
            && (php_sapi_name() == "cli")
            && (getenv('TERM'))
            && (getenv('TERM') != 'unknown')
        ) {
            $this->width = (int)(`command -v tput >> /dev/null 2>&1 && tput cols`)-2;
        }
        return $this->width;
    }

    private function isWin()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * @param \PHPUnit_Framework_SelfDescribing $test
     * @param bool $inProgress
     */
    protected function writeCurrentTest(\PHPUnit_Framework_SelfDescribing $test, $inProgress = true)
    {
        $prefix = ($this->output->isInteractive() and !$this->isDetailed($test) and $inProgress) ? '- ' : '';
        $atMessage = $this->message(' ');

        $filename = basename(Descriptor::getTestFileName($test));
        if ($filename) {
            $atMessage = $atMessage
                ->append($this->options['colors'] ? '' : 'at ')
                ->append($filename);
        }

        $stripDataSet = function ($str) {
            return str_replace('with data set', "|", $str);
        };

        if (!$test instanceof Descriptive) {
            $title = $this->message(str_replace('::', ':', $test->toString()))->apply($stripDataSet);
            $atMessage->cut($this->width - 4 - mb_strlen($title))->style('info');
            $this
                ->message($title)
                ->append($atMessage)
                ->prepend($prefix)
                ->write();
            return;
        }

        $feature = $test->getName(true);
        if ($test instanceof TestInterface and $test->getMetadata()->getFeature()) {
            $feature = $test->getMetadata()->getFeature();
        }
        $title = $this->message(ucfirst($feature))->apply($stripDataSet);
        $atMessage->cut($this->width - 4 - mb_strlen($title))->style('info');

        $this->message($title)
            ->prepend($prefix)
            ->append($atMessage)
            ->write();
    }

    protected function writelnFinishedTest(TestEvent $event, Message $result)
    {
        $test = $event->getTest();
        if ($this->isDetailed($test)) {
            return;
        }

        if ($this->output->isInteractive()) {
            $this->output->write("\x0D");
        }
        $result->append(' ')->write();
        $this->writeCurrentTest($test, false);

        $conditionalFails = "";
        $numFails  = count($this->fails);
        if ($numFails == 1) {
            $conditionalFails = "[F]";
        } elseif ($numFails) {
            $conditionalFails = "{$numFails}x[F]";
        }
        $conditionalFails = "<error>$conditionalFails</error> ";
        $this->message($conditionalFails)->write();

        $time = $event->getTime();

        if ($time) {
            $seconds = (int)($milliseconds = (int)($time * 100)) / 100;
            $time = ($seconds % 60) . '.' . $milliseconds;

            $this->message($time)
                ->prepend('(')
                ->append('s)')
                ->style('info')
                ->write();
        }
        $this->output->writeln('');
    }
}
