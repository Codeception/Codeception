<?php

declare(strict_types=1);

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
use Codeception\Step\ConditionalAssertion;
use Codeception\Step\Meta;
use Codeception\Suite;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Util\Filter as PHPUnitFilter;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function array_count_values;
use function array_map;
use function array_merge;
use function array_reverse;
use function array_shift;
use function codecept_output_dir;
use function codecept_relative_path;
use function count;
use function exec;
use function get_class;
use function getenv;
use function implode;
use function number_format;
use function preg_match;
use function preg_replace;
use function round;
use function sprintf;
use function strlen;
use function strpos;
use function strtoupper;
use function substr;
use function ucfirst;

class Console implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
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

    protected ?Meta $metaStep = null;

    protected ?Message $message = null;

    protected bool $steps = true;

    protected bool $debug = false;

    protected bool $ansi = true;

    protected bool $silent = false;

    protected bool $lastTestFailed = false;

    protected ?SelfDescribing $printedTest = null;

    protected bool $rawStackTrace = false;

    protected int $traceLength = 5;

    protected ?int $width = null;

    protected OutputInterface $output;

    /**
     * @var ConditionalAssertion[]
     */
    protected array $conditionalFails = [];

    /**
     * @var Step[]
     */
    protected array $failedStep = [];

    /**
     * @var string[]
     */
    protected array $reports = [];

    protected string $namespace = '';

    /**
     * @var array<string, string>
     */
    protected array $chars = ['success' => '+', 'fail' => 'x', 'of' => ':'];

    /**
     * @var array<string, int|bool|null>
     */
    protected array $options = [
        'debug'         => false,
        'ansi'          => false,
        'steps'         => true,
        'verbosity'     => 0,
        'xml'           => null,
        'phpunit-xml'   => null,
        'html'          => null,
        'no-artifacts'  => false,
    ];

    protected MessageFactory $messageFactory;

    public function __construct(array $options)
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

        foreach (['html', 'xml', 'phpunit-xml'] as $report) {
            if (!$this->options[$report]) {
                continue;
            }
            $path = $this->absolutePath((string)$this->options[$report]);
            $this->reports[] = sprintf(
                "- <bold>%s</bold> report generated in <comment>file://%s</comment>",
                strtoupper($report),
                $path
            );
        }
    }

    // triggered for scenario based tests: cept, cest
    public function beforeSuite(SuiteEvent $event): void
    {
        $this->namespace = "";
        $settings = $event->getSettings();
        if (isset($settings['namespace'])) {
            $this->namespace = $settings['namespace'];
        }
        $this->message("%s Tests (%d) ")
            ->with(ucfirst($event->getSuite()->getName()), $event->getSuite()->count())
            ->style('bold')
            ->width($this->width, '-')
            ->prepend("\n")
            ->writeln();

        if ($event->getSuite() instanceof Suite) {
            $message = $this->message(
                implode(
                    ', ',
                    array_map(
                        fn($module) => $module->_getName(),
                        $event->getSuite()->getModules()
                    )
                )
            );

            $message->style('info')
                ->prepend('Modules: ')
                ->writeln(OutputInterface::VERBOSITY_VERBOSE);
        }

        $this->message()->width($this->width, '-')->writeln(OutputInterface::VERBOSITY_VERBOSE);
    }

    // triggered for all tests
    public function startTest(TestEvent $event): void
    {
        $this->conditionalFails = [];
        /** @var SelfDescribing $test */
        $test = $event->getTest();
        $this->printedTest = $test;
        $this->message = null;

        if (!$this->output->isInteractive() && !$this->isDetailed($test)) {
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

    public function afterStep(StepEvent $event): void
    {
        $step = $event->getStep();
        if (!$step->hasFailed()) {
            return;
        }
        if ($step instanceof ConditionalAssertion) {
            $this->conditionalFails[] = $step;
            return;
        }
        $this->failedStep[] = $step;
    }

    public function afterResult(PrintResultEvent $event): void
    {
        $result = $event->getResult();
        if ($result->skippedCount() + $result->notImplementedCount() > 0 && $this->options['verbosity'] < OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln("run with `-v` to get more info about skipped or incomplete tests");
        }
        foreach ($this->reports as $message) {
            $this->output->writeln($message);
        }
    }

    private function absolutePath(string $path): string
    {
        if (strpos($path, '/') === 0 || strpos($path, ':') === 1) { // absolute path
            return $path;
        }

        return codecept_output_dir() . $path;
    }

    public function testSuccess(TestEvent $event): void
    {
        if ($this->isDetailed($event->getTest())) {
            $this->message('PASSED')->center(' ')->style('ok')->append("\n")->writeln();

            return;
        }
        $this->writelnFinishedTest($event, $this->message($this->chars['success'])->style('ok'));
    }

    public function endTest(TestEvent $event): void
    {
        $this->metaStep = null;
        $this->printedTest = null;
    }

    public function testWarning(TestEvent $event): void
    {
        if ($this->isDetailed($event->getTest())) {
            $this->message('WARNING')->center(' ')->style('pending')->append("\n")->writeln();

            return;
        }
        $this->writelnFinishedTest($event, $this->message('W')->style('pending'));
    }

    public function testFail(FailEvent $event): void
    {
        if ($this->isDetailed($event->getTest())) {
            $this->message('FAIL')->center(' ')->style('fail')->append("\n")->writeln();

            return;
        }
        $this->writelnFinishedTest($event, $this->message($this->chars['fail'])->style('fail'));
    }

    public function testError(FailEvent $event): void
    {
        if ($this->isDetailed($event->getTest())) {
            $this->message('ERROR')->center(' ')->style('fail')->append("\n")->writeln();

            return;
        }
        $this->writelnFinishedTest($event, $this->message('E')->style('fail'));
    }

    public function testSkipped(FailEvent $event): void
    {
        if ($this->isDetailed($event->getTest())) {
            $msg = $event->getFail()->getMessage();
            $this->message('SKIPPED')->append($msg !== '' ? ": {$msg}" : '')->center(' ')->style('pending')->writeln();

            return;
        }
        $this->writelnFinishedTest($event, $this->message('S')->style('pending'));
    }

    public function testIncomplete(FailEvent $event): void
    {
        if ($this->isDetailed($event->getTest())) {
            $msg = $event->getFail()->getMessage();
            $this->message('INCOMPLETE')->append($msg !== '' ? ": {$msg}" : '')->center(' ')->style('pending')->writeln();

            return;
        }
        $this->writelnFinishedTest($event, $this->message('I')->style('pending'));
    }

    protected function isDetailed($test): bool
    {
        if (!$test instanceof ScenarioDriven) {
            return false;
        }
        return $this->steps;
    }

    public function beforeStep(StepEvent $event): void
    {
        if (!$this->steps || !$event->getTest() instanceof ScenarioDriven) {
            return;
        }
        $metaStep = $event->getStep()->getMetaStep();
        if ($metaStep && $this->metaStep != $metaStep) {
            $this->message(' ' . $metaStep->getPrefix())
                ->style('bold')
                ->append($metaStep->__toString())
                ->writeln();
        }
        $this->metaStep = $metaStep;

        $this->printStep($event->getStep());
    }

    private function printStep(Step $step): void
    {
        if ($step instanceof Comment && $step->__toString() == '') {
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

    public function afterSuite(SuiteEvent $event): void
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

    public function printFail(FailEvent $event): void
    {
        /** @var SelfDescribing|TestInterface $failedTest */
        $failedTest = $event->getTest();
        $fail = $event->getFail();

        $this->output->write($event->getCount() . ") ");
        $this->writeCurrentTest($failedTest, false);
        $this->output->writeln('');
        $this->message("<error> Test </error> ")
            ->append(codecept_relative_path(Descriptor::getTestFullName($failedTest)))
            ->write();

        if ($failedTest instanceof ScenarioDriven) {
            $this->printScenarioFail($failedTest, $fail);
            $this->printReports($failedTest);
            return;
        }

        $this->printException($fail);
        $this->printExceptionTrace($fail);
    }

    public function printReports(TestInterface $failedTest): void
    {
        if ($this->options['no-artifacts']) {
            return;
        }
        $reports = $failedTest->getMetadata()->getReports();
        if (!empty($reports)) {
            $this->output->writeln('<comment>Artifacts:</comment>');
            $this->output->writeln('');
        }

        foreach ($reports as $type => $report) {
            $type = ucfirst($type);
            $this->output->writeln("{$type}: <debug>{$report}</debug>");
        }
    }

    public function printException($exception, string $cause = null): void
    {
        if ($exception instanceof SkippedTestError || $exception instanceof IncompleteTestError) {
            if ($exception->getMessage() !== '') {
                $this->message(OutputFormatter::escape($exception->getMessage()))->prepend("\n")->writeln();
            }

            return;
        }

        $class = $exception instanceof ExceptionWrapper
            ? $exception->getClassname()
            : get_class($exception);

        if (strpos($class, 'Codeception\Exception') === 0) {
            $class = substr($class, strlen('Codeception\Exception\\'));
        }

        $this->output->writeln('');
        $message = $this->message(OutputFormatter::escape($exception->getMessage()));

        if ($exception instanceof ExpectationFailedException) {
            $comparisonFailure = $exception->getComparisonFailure();
            if ($comparisonFailure !== null) {
                $message->append($this->messageFactory->prepareComparisonFailureMessage($comparisonFailure));
            }
        }

        $isFailure = $exception instanceof AssertionFailedError
            || $class === ExpectationFailedException::class
            || $class === AssertionFailedError::class;

        if (!$isFailure) {
            $message->prepend("[{$class}] ")->block('error');
        }

        if ($isFailure && $cause) {
            $cause = OutputFormatter::escape(ucfirst($cause));
            $message->prepend("<error> Step </error> {$cause}\n<error> Fail </error> ");
        }

        $message->writeln();
    }

    public function printScenarioFail(ScenarioDriven $failedTest, $fail): void
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
        if (!$fail instanceof AssertionFailedError) {
            $this->printExceptionTrace($fail);
        }
    }

    public function printExceptionTrace($exception): void
    {
        static $limit = 10;

        if ($exception instanceof SkippedTestError || $exception instanceof IncompleteTestError) {
            return;
        }

        if ($this->rawStackTrace) {
            $this->message(OutputFormatter::escape(PHPUnitFilter::getFilteredStacktrace($exception, true, false)))->writeln();

            return;
        }

        $trace = PHPUnitFilter::getFilteredStacktrace($exception, false);

        $i = 0;
        foreach ($trace as $step) {
            if ($i >= $limit) {
                break;
            }
            ++$i;

            $message = $this->message((string)$i)->prepend('#')->width(4);

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

        $prev = $exception->getPrevious();
        if ($prev) {
            $this->printExceptionTrace($prev);
        }
    }

    public function printScenarioTrace(ScenarioDriven $failedTest): void
    {
        $trace = array_reverse($failedTest->getScenario()->getSteps());
        $length = count($trace);
        $stepNumber = $length;

        if ($length === 0) {
            return;
        }

        $this->message("\nScenario Steps:\n")->style('comment')->writeln();

        foreach ($trace as $step) {
            /** @var Step $step */
            if (!$step->__toString()) {
                continue;
            }

            $message = $this
                ->message((string)$stepNumber)
                ->prepend(' ')
                ->width(strlen((string)$length))
                ->append(". ");
            $message->append(OutputFormatter::escape($step->getPhpCode($this->width - $message->getLength())));

            if ($step->hasFailed()) {
                $message->style('bold');
            }

            $line = $step->getLine();
            if ($line && !$step instanceof Comment) {
                $message->append(" at <info>{$line}</info>");
            }

            --$stepNumber;
            $message->writeln();
            if (($length - $stepNumber - 1) >= $this->traceLength) {
                break;
            }
        }
        $this->output->writeln("");
    }

    public function detectWidth(): int
    {
        $this->width = 60;
        if (!$this->isWin()
            && (PHP_SAPI === "cli")
            && (getenv('TERM'))
            && (getenv('TERM') != 'unknown')
        ) {
            // try to get terminal width from ENV variable (bash), see also https://github.com/Codeception/Codeception/issues/3788
            if (getenv('COLUMNS')) {
                $this->width = (int) getenv('COLUMNS');
            } else {
                $this->width = (int) (`command -v tput >> /dev/null 2>&1 && tput cols`) - 2;
            }
        } elseif ($this->isWin() && (PHP_SAPI === "cli")) {
            exec('mode con', $output);
            if (isset($output[4])) {
                preg_match('#^ +.* +(\d+)$#', $output[4], $matches);
                if (!empty($matches[1])) {
                    $this->width = (int) $matches[1];
                }
            }
        }
        return $this->width;
    }

    private function isWin(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    protected function writeCurrentTest(SelfDescribing $test, bool $inProgress = true): void
    {
        $prefix = ($this->output->isInteractive() && !$this->isDetailed($test) && $inProgress) ? '- ' : '';

        $testString = Descriptor::getTestAsString($test);
        $testString = preg_replace('#^([^:]+):\s#', "<focus>$1{$this->chars['of']}</focus> ", $testString);

        $this
            ->message($testString)
            ->prepend($prefix)
            ->write();
    }

    protected function writelnFinishedTest(TestEvent $event, Message $result): void
    {
        /** @var SelfDescribing $test */
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
        } elseif ($numFails !== 0) {
            $conditionalFailsMessage = "{$numFails}x[F]";
        }
        $conditionalFailsMessage = "<error>{$conditionalFailsMessage}</error> ";
        $this->message($conditionalFailsMessage)->write();
        $this->writeTimeInformation($event);
        $this->output->writeln('');
    }

    private function message(string $string = ''): Message
    {
        return $this->messageFactory->message($string);
    }

    protected function writeTimeInformation(TestEvent $event): void
    {
        $time = $event->getTime();
        if ($time !== 0.0) {
            $this
                ->message(number_format(round($time, 2), 2))
                ->prepend('(')
                ->append('s)')
                ->style('info')
                ->write();
        }
    }

    private function prepareOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
        $this->debug = $this->options['debug'] || $this->options['verbosity'] >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $this->steps = $this->debug || $this->options['steps'];
        $this->rawStackTrace = ($this->options['verbosity'] === OutputInterface::VERBOSITY_DEBUG);
    }
}
