<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\UselessTestException;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Console\MessageFactory;
use Codeception\Lib\Console\Output;
use Codeception\ResultAggregator;
use Codeception\Step;
use Codeception\Step\Comment;
use Codeception\Step\ConditionalAssertion;
use Codeception\Step\Meta;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Codeception\Suite;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use Codeception\Util\StackTraceFilter;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTest;
use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_map;
use function array_merge;
use function array_reverse;
use function array_shift;
use function codecept_relative_path;
use function count;
use function exec;
use function getenv;
use function implode;
use function number_format;
use function preg_match;
use function preg_replace;
use function round;
use function sprintf;
use function strlen;
use function strtoupper;
use function substr;
use function ucfirst;

class Console implements EventSubscriberInterface
{
    use StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_BEFORE       => 'beforeSuite',
        Events::SUITE_AFTER        => 'afterSuite',
        Events::TEST_START         => 'startTest',
        Events::TEST_END           => 'endTest',
        Events::STEP_BEFORE        => 'beforeStep',
        Events::TEST_SUCCESS       => 'testSuccess',
        Events::TEST_FAIL          => 'testFail',
        Events::TEST_ERROR         => 'testError',
        Events::TEST_INCOMPLETE    => 'testIncomplete',
        Events::TEST_SKIPPED       => 'testSkipped',
        Events::TEST_WARNING       => 'testWarning',
        Events::TEST_USELESS       => 'testUseless',
        Events::TEST_FAIL_PRINT    => 'printFail',
        Events::RESULT_PRINT_AFTER => 'afterResult',
    ];

    protected ?Meta $metaStep = null;

    protected ?Message $message = null;

    protected bool $steps = true;

    protected bool $debug = false;

    protected bool $ansi = true;

    protected bool $silent = false;

    protected ?SelfDescribing $printedTest = null;

    protected bool $rawStackTrace = false;

    protected int $traceLength = 5;

    protected ?int $width = null;

    protected Output $output;

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

    private Timer $timer;

    private bool $firstDefectType = true;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->timer = new Timer();
        $this->timer->start();
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
            ->with(ucfirst($event->getSuite()->getBaseName()), $event->getSuite()->getTestCount())
            ->style('bold')
            ->width($this->width, '-')
            ->prepend("\n")
            ->writeln();

        if ($event->getSuite() instanceof Suite) {
            $message = $this->message(
                implode(
                    ', ',
                    array_map(
                        fn ($module) => $module->_getName(),
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

    public function afterResult(PrintResultEvent $event): void
    {
        $result = $event->getResult();
        $this->printHeader($result);
        $verbose = $this->options['verbosity'] >= OutputInterface::VERBOSITY_VERBOSE;

        $outputFormatter = $this->output->getFormatter();
        $outputFormatter->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
        $outputFormatter->setStyle('success', new OutputFormatterStyle('black', 'green'));

        $this->printDefects($result->errors(), 'error');
        $this->printDefects($result->failures(), 'failure');
        $this->printDefects($result->useless(), 'useless test');
        if ($verbose) {
            $this->printDefects($result->incomplete(), 'incomplete test');
            $this->printDefects($result->skipped(), 'skipped test');
        }
        $this->printFooter($event);

        if ($result->skippedCount() + $result->incompleteCount() > 0 && !$verbose) {
            $this->output->writeln("run with `-v` to get more info about skipped or incomplete tests");
        }
    }

    protected function printHeader(ResultAggregator $result): void
    {
        if ($result->testCount() > 0) {
            $this->printResourceUsage($this->timer->stop());
        }
    }

    private function printResourceUsage(Duration $duration): void
    {
        $formatter = new ResourceUsageFormatter();
        $this->message($formatter->resourceUsage($duration))->writeln();
    }

    /**
     * @param FailEvent[] $defects
     * @param string $type
     */
    private function printDefects(array $defects, string $type): void
    {
        $count = count($defects);

        if ($count == 0) {
            return;
        }

        if ($this->firstDefectType) {
            $this->firstDefectType = false;
        } else {
            $this->message("\n---------")->writeln();
        }

        $this->message('')->writeln();

        $this->message(
            sprintf(
                "There %s %d %s%s:",
                ($count == 1) ? 'was' : 'were',
                $count,
                $type,
                ($count == 1) ? '' : 's'
            )
        )->writeln();

        $i = 1;

        foreach ($defects as $defect) {
            $this->printFail($defect, $i++);
        }
    }

    protected function printFooter(PrintResultEvent $event): void
    {
        $result = $event->getResult();
        $testCount = $result->testCount();
        $assertionCount = $result->assertionCount();

        $this->message('')->writeln();

        if ($testCount === 0) {
            $this->message('No tests executed!')->style('warning')->writeln();
            return;
        }

        if ($result->wasSuccessfulAndNoTestIsUselessOrSkippedOrIncomplete()) {
            $message = sprintf(
                'OK (%d test%s, %d assertion%s)',
                $testCount,
                $testCount === 1 ? '' : 's',
                $assertionCount,
                $assertionCount === 1 ? '' : 's'
            );
            $this->message($message)->style('success')->writeln();
            return;
        }

        $style = 'error';
        if ($result->wasSuccessful()) {
            $style = 'warning';
            $this->message('OK, but incomplete, skipped, or useless tests!')->style($style)->writeln();
        } elseif ($result->errorCount()) {
            $this->message('ERRORS!')->style($style)->writeln();
        } elseif ($result->failureCount()) {
            $this->message('FAILURES!')->style($style)->writeln();
        } elseif ($result->warningCount()) {
            $style = 'warning';
            $this->message('WARNINGS!')->style($style)->writeln();
        }

        $counts = [
            sprintf("Tests: %s", $testCount),
            sprintf("Assertions: %s", $assertionCount),
        ];
        if ($result->errorCount() > 0) {
            $counts [] = sprintf("Errors: %s", $result->errorCount());
        }
        if ($result->failureCount() > 0) {
            $counts [] = sprintf("Failures: %s", $result->failureCount());
        }
        if ($result->warningCount() > 0) {
            $counts [] = sprintf("Warnings: %s", $result->warningCount());
        }
        if ($result->skippedCount() > 0) {
            $counts [] = sprintf("Skipped: %s", $result->skippedCount());
        }
        if ($result->incompleteCount() > 0) {
            $counts [] = sprintf("Incomplete: %s", $result->incompleteCount());
        }
        if ($result->uselessCount() > 0) {
            $counts [] = sprintf("Useless: %s", $result->uselessCount());
        }

        $this->message(implode(', ', $counts) . '.')->style($style)->writeln();
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

    public function testUseless(FailEvent $event): void
    {
        $this->writelnFinishedTest($event, $this->message('U')->style('pending'));
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
    }

    public function printFail(FailEvent $event, int $eventNumber): void
    {
        $failedTest = $event->getTest();
        $fail = $event->getFail();

        $this->output->write($eventNumber . ") ");
        $this->writeCurrentTest($failedTest, false);
        $this->output->writeln('');

        // Clickable `editor_url`:
        if (isset($this->options['editor_url']) && is_string($this->options['editor_url'])) {
            $filePath = $failedTest->getFilename();
            $line = 1;
            foreach ($fail->getTrace() as $trace) {
                if (isset($trace['file']) && $filePath === $trace['file'] && isset($trace['line'])) {
                    $line = $trace['line'];
                }
            }
            $message = str_replace(['%%file%%', '%%line%%'], [$filePath, $line], $this->options['editor_url']);
        } else {
            $message = Descriptor::getTestFullName($failedTest);
        }
        $testStyle = 'error';
        if (
            $fail instanceof SkippedTest
            || $fail instanceof IncompleteTestError
            || $fail instanceof UselessTestException
        ) {
            $testStyle = 'warning';
        }

        $this->message(' Test  ')->style($testStyle)
            ->append($message)
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
        if ($exception instanceof SkippedTest || $exception instanceof IncompleteTestError) {
            if ($exception->getMessage() !== '') {
                $this->message(OutputFormatter::escape($exception->getMessage()))->prepend("\n")->writeln();
            }

            return;
        }

        $class = $exception::class;

        if (str_starts_with($class, 'Codeception\Exception')) {
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
        $failedStep = (string)$failedTest->getScenario()->getMetaStep();
        if ($failedStep === '') {
            foreach (array_reverse($failedTest->getScenario()->getSteps()) as $step) {
                if ($step->hasFailed()) {
                    $failedStep = (string)$step;
                    break;
                }
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

        if (
            $exception instanceof SkippedTest
            || $exception instanceof IncompleteTestError
            || $exception instanceof UselessTestException
        ) {
            return;
        }

        if ($this->rawStackTrace) {
            $this->message(OutputFormatter::escape(StackTraceFilter::getFilteredStacktrace($exception, true, false)))->writeln();

            return;
        }

        $trace = StackTraceFilter::getFilteredStacktrace($exception, false);

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

            // Clickable `editor_url`:
            if (isset($this->options['editor_url']) && is_string($this->options['editor_url'])) {
                $lineString = str_replace(['%%file%%', '%%line%%'], [$step['file'], $step['line']], $this->options['editor_url']);
            } else {
                $lineString = $step['file'] . ':' . $step['line'];
            }
            $message->append($lineString);
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

            if (!$step instanceof Comment) {
                $filePath = $step->getFilePath();
                if ($filePath) {
                    // Clickable `editor_url`:
                    if (isset($this->options['editor_url']) && is_string($this->options['editor_url'])) {
                        $lineString = str_replace(['%%file%%', '%%line%%'], [codecept_absolute_path($step->getFilePath()), $step->getLineNumber()], $this->options['editor_url']);
                    } else {
                        $lineString = $step->getFilePath() . ':' . $step->getLineNumber();
                    }
                    $message->append(" at <info>$lineString</info>");
                }
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
        if (
            !$this->isWin()
            && (PHP_SAPI === "cli")
            && (getenv('TERM'))
            && (getenv('TERM') != 'unknown')
        ) {
            // try to get terminal width from ENV variable (bash), see also https://github.com/Codeception/Codeception/issues/3788
            if (getenv('COLUMNS')) {
                $this->width = (int)getenv('COLUMNS');
            } else {
                $this->width = (int)(`command -v tput >> /dev/null 2>&1 && tput cols`) - 2;
            }
        } elseif ($this->isWin() && (PHP_SAPI === "cli")) {
            exec('mode con', $output);
            if (isset($output[4])) {
                preg_match('#^ +.* +(\d+)$#', $output[4], $matches);
                if (!empty($matches[1])) {
                    $this->width = (int)$matches[1];
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

        if (method_exists($test, 'getScenario')) {
            $numFails = count(
                array_filter(
                    $test->getScenario()?->getSteps() ?? [],
                    function (Step $step) {
                        return $step->hasFailed() && $step instanceof ConditionalAssertion;
                    }
                )
            );

            $conditionalFailsMessage = "";
            if ($numFails == 1) {
                $conditionalFailsMessage = "[F]";
            } elseif ($numFails !== 0) {
                $conditionalFailsMessage = "{$numFails}x[F]";
            }
            if ($conditionalFailsMessage !== '') {
                $conditionalFailsMessage = " <error>{$conditionalFailsMessage}</error> ";
                $this->message($conditionalFailsMessage)->write();
            }
        }
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
