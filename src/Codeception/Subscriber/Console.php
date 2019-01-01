<?php
namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Console\MessageFactory;
use Codeception\Lib\Console\Output;
use Codeception\Lib\Notification;
use Codeception\Step;
use Codeception\Step\Comment;
use Codeception\Suite;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Util\Debug;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Console implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    /**
     * @var string[]
     */
    public static $events = [
        Events::SUITE_BEFORE       => 'beforeSuite',
        Events::SUITE_AFTER        => 'afterSuite',
        Events::TEST_START         => 'startTest',
        Events::TEST_END           => 'endTest',
        Events::STEP_BEFORE        => 'beforeStep',
        Events::STEP_AFTER         => 'afterStep',
        Events::TEST_SUCCESS       => 'testSuccess',
        Events::TEST_FAIL          => 'testFail',
        Events::TEST_ERROR         => 'testError',
        Events::TEST_INCOMPLETE    => 'testIncomplete',
        Events::TEST_SKIPPED       => 'testSkipped',
        Events::TEST_WARNING       => 'testWarning',
        Events::TEST_FAIL_PRINT    => 'printFail',
        Events::RESULT_PRINT_AFTER => 'afterResult',
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
    protected $ansi = true;
    protected $silent = false;
    protected $lastTestFailed = false;
    protected $printedTest = null;
    protected $rawStackTrace = false;
    protected $traceLength = 5;
    protected $width;

    /**
     * @var OutputInterface
     */
    protected $output;
    protected $conditionalFails = [];
    protected $failedStep = [];
    protected $reports = [];
    protected $namespace = '';
    protected $chars = ['success' => '+', 'fail' => 'x', 'of' => ':'];

    protected $options = [
        'debug'       => false,
        'ansi'        => false,
        'steps'       => true,
        'verbosity'   => 0,
        'xml'         => null,
        'phpunit-xml' => null,
        'html'        => null,
        'tap'         => null,
        'json'        => null,
    ];

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    public function __construct($options)
    {
        $this->prepareOptions($options);
        $this->output = new Output($options);
        $this->messageFactory = new MessageFactory($this->output);
        if ($this->debug) {
            Debug::setOutput($this->output);
        }
        $this->detectWidth();

        if ($this->options['ansi'] && !$this->isWin()) {
            $this->chars['success'] = '✔';
            $this->chars['fail'] = '✖';
        }

        foreach (['html', 'xml', 'phpunit-xml', 'tap', 'json'] as $report) {
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
            ->with(ucfirst($e->getSuite()->getName()), $e->getSuite()->count())
            ->style('bold')
            ->width($this->width, '-')
            ->prepend("\n")
            ->writeln();

        if ($e->getSuite() instanceof Suite) {
            $message = $this->message(
                implode(
                    ', ',
                    array_map(
                        function ($module) {
                            return $module->_getName();
                        },
                        $e->getSuite()->getModules()
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
        $this->conditionalFails = [];
        $test = $e->getTest();
        $this->printedTest = $test;
        $this->message = null;

        if (!$this->output->isInteractive() and !$this->isDetailed($test)) {
            return;
        }
        $this->writeCurrentTest($test);
        if ($this->isDetailed($test)) {
            $this->output->writeln('');
            $this->message(Descriptor::getTestSignature($test))
                ->style('info')
                ->prepend('Signature: ')
                ->writeln();

            $this->message(codecept_relative_path(Descriptor::getTestFullName($test)))
                ->style('info')
                ->prepend('Test: ')
                ->writeln();

            if ($this->steps) {
                $this->message('Scenario --')->style('comment')->writeln();
                $this->output->waitForDebugOutput = false;
            }
        }
    }

    public function afterStep(StepEvent $e)
    {
        $step = $e->getStep();
        if (!$step->hasFailed()) {
            return;
        }
        if ($step instanceof Step\ConditionalAssertion) {
            $this->conditionalFails[] = $step;
            return;
        }
        $this->failedStep[] = $step;
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
        $this->writelnFinishedTest($e, $this->message($this->chars['success'])->style('ok'));
    }

    public function endTest(TestEvent $e)
    {
        $this->metaStep = null;
        $this->printedTest = null;
    }

    public function testWarning(TestEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('WARNING')->center(' ')->style('pending')->append("\n")->writeln();

            return;
        }
        $this->writelnFinishedTest($e, $this->message('W')->style('pending'));
    }

    public function testFail(FailEvent $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('FAIL')->center(' ')->style('fail')->append("\n")->writeln();

            return;
        }
        $this->writelnFinishedTest($e, $this->message($this->chars['fail'])->style('fail'));
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
            return true;
        }

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
        $msg->append(OutputFormatter::escape($step->toString($maxLength)));
        if ($this->metaStep) {
            $msg->style('info');
        }
        $msg->writeln();
    }

    public function afterSuite(SuiteEvent $e)
    {
        $this->message()->width($this->width, '-')->writeln();
        $messages = Notification::all();
        foreach (array_count_values($messages) as $message => $count) {
            if ($count > 1) {
                $message = $count . 'x ' . $message;
            }
            $this->output->notification($message);
        }
    }

    public function printFail(FailEvent $e)
    {
        $failedTest = $e->getTest();
        $fail = $e->getFail();

        $this->output->write($e->getCount() . ") ");
        $this->writeCurrentTest($failedTest, false);
        $this->output->writeln('');
        $this->message("<error> Test </error> ")
            ->append(codecept_relative_path(Descriptor::getTestFullName($failedTest)))
            ->write();

        if ($failedTest instanceof ScenarioDriven) {
            $this->printScenarioFail($failedTest, $fail);

            return;
        }

        $this->printException($fail);
        $this->printExceptionTrace($fail);
    }

    public function printException($e, $cause = null)
    {
        if ($e instanceof \PHPUnit\Framework\SkippedTestError or $e instanceof \PHPUnit\Framework_IncompleteTestError) {
            if ($e->getMessage()) {
                $this->message(OutputFormatter::escape($e->getMessage()))->prepend("\n")->writeln();
            }

            return;
        }

        $class = $e instanceof \PHPUnit\Framework\ExceptionWrapper
            ? $e->getClassname()
            : get_class($e);

        if (strpos($class, 'Codeception\Exception') === 0) {
            $class = substr($class, strlen('Codeception\Exception\\'));
        }

        $this->output->writeln('');
        $message = $this->message(OutputFormatter::escape($e->getMessage()));

        if ($e instanceof \PHPUnit\Framework\ExpectationFailedException) {
            $comparisonFailure = $e->getComparisonFailure();
            if ($comparisonFailure) {
                $message->append($this->messageFactory->prepareComparisonFailureMessage($comparisonFailure));
            }
        }

        $isFailure = $e instanceof \PHPUnit\Framework\AssertionFailedError
            || $class === 'PHPUnit\Framework\ExpectationFailedException'
            || $class === 'PHPUnit\Framework\AssertionFailedError';

        if (!$isFailure) {
            $message->prepend("[$class] ")->block('error');
        }

        if ($isFailure && $cause) {
            $cause = OutputFormatter::escape(ucfirst($cause));
            $message->prepend("<error> Step </error> $cause\n<error> Fail </error> ");
        }

        $message->writeln();
    }

    public function printScenarioFail(ScenarioDriven $failedTest, $fail)
    {
        if ($this->conditionalFails) {
            $failedStep = (string) array_shift($this->conditionalFails);
        } else {
            $failedStep = (string) $failedTest->getScenario()->getMetaStep();
            if ($failedStep === '') {
                $failedStep = (string) array_shift($this->failedStep);
            }
        }

        $this->printException($fail, $failedStep);

        $this->printScenarioTrace($failedTest);
        if ($this->output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
            $this->printExceptionTrace($fail);

            return;
        }
        if (!$fail instanceof \PHPUnit\Framework\AssertionFailedError) {
            $this->printExceptionTrace($fail);

            return;
        }
    }

    public function printExceptionTrace($e)
    {
        static $limit = 10;

        if ($e instanceof \PHPUnit\Framework\SkippedTestError or $e instanceof \PHPUnit\Framework_IncompleteTestError) {
            return;
        }

        if ($this->rawStackTrace) {
            $this->message(OutputFormatter::escape(\PHPUnit\Util\Filter::getFilteredStacktrace($e, true, false)))->writeln();

            return;
        }

        $trace = \PHPUnit\Util\Filter::getFilteredStacktrace($e, false);

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
                ->append(". ");
            $message->append(OutputFormatter::escape($step->getPhpCode($this->width - $message->getLength())));

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

    public function detectWidth()
    {
        $this->width = 60;
        if (!$this->isWin()
            && (php_sapi_name() === "cli")
            && (getenv('TERM'))
            && (getenv('TERM') != 'unknown')
        ) {
            // try to get terminal width from ENV variable (bash), see also https://github.com/Codeception/Codeception/issues/3788
            if (getenv('COLUMNS')) {
                $this->width = getenv('COLUMNS');
            } else {
                $this->width = (int) (`command -v tput >> /dev/null 2>&1 && tput cols`) - 2;
            }
        } elseif ($this->isWin() && (php_sapi_name() === "cli")) {
            exec('mode con', $output);
            if (isset($output[4])) {
                preg_match('/^ +.* +(\d+)$/', $output[4], $matches);
                if (!empty($matches[1])) {
                    $this->width = (int) $matches[1];
                }
            }
        }
        return $this->width;
    }

    private function isWin()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * @param \PHPUnit\Framework\SelfDescribing $test
     * @param bool                              $inProgress
     */
    protected function writeCurrentTest(\PHPUnit\Framework\SelfDescribing $test, $inProgress = true)
    {
        $prefix = ($this->output->isInteractive() and !$this->isDetailed($test) and $inProgress) ? '- ' : '';

        $testString = Descriptor::getTestAsString($test);
        $testString = preg_replace('~^([^:]+):\s~', "<focus>$1{$this->chars['of']}</focus> ", $testString);

        $this
            ->message($testString)
            ->prepend($prefix)
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

        $conditionalFailsMessage = "";
        $numFails = count($this->conditionalFails);
        if ($numFails == 1) {
            $conditionalFailsMessage = "[F]";
        } elseif ($numFails) {
            $conditionalFailsMessage = "{$numFails}x[F]";
        }
        $conditionalFailsMessage = "<error>$conditionalFailsMessage</error> ";
        $this->message($conditionalFailsMessage)->write();
        $this->writeTimeInformation($event);
        $this->output->writeln('');
    }

    /**
     * @param $string
     * @return Message
     */
    private function message($string = '')
    {
        return $this->messageFactory->message($string);
    }

    /**
     * @param TestEvent $event
     */
    protected function writeTimeInformation(TestEvent $event)
    {
        $time = $event->getTime();
        if ($time) {
            $this
                ->message(number_format(round($time, 2), 2))
                ->prepend('(')
                ->append('s)')
                ->style('info')
                ->write();
        }
    }

    /**
     * @param $options
     */
    private function prepareOptions($options)
    {
        $this->options = array_merge($this->options, $options);
        $this->debug = $this->options['debug'] || $this->options['verbosity'] >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $this->steps = $this->debug || $this->options['steps'];
        $this->rawStackTrace = ($this->options['verbosity'] === OutputInterface::VERBOSITY_DEBUG);
    }
}
