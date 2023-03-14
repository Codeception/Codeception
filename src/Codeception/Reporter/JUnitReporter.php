<?php

namespace Codeception\Reporter;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Console\Output;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Codeception\Test\Test;
use Codeception\Test\TestCaseWrapper;
use Codeception\Util\StackTraceFilter;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\Util\ThrowableToStringMapper;
use PHPUnit\Util\Xml;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

use function sprintf;
use function str_replace;

class JUnitReporter implements EventSubscriberInterface
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
        Events::TEST_FAIL          => 'testFailure',
        Events::TEST_ERROR         => 'testError',
        Events::TEST_INCOMPLETE    => 'testSkipped',
        Events::TEST_SKIPPED       => 'testSkipped',
        Events::TEST_USELESS       => 'testUseless',
        Events::TEST_WARNING       => 'testWarning',
        Events::RESULT_PRINT_AFTER => 'afterResult',
    ];

    protected string $reportFileParam = 'xml';

    protected string $reportName = 'JUNIT XML';

    protected bool $isStrict = false;

    /**
     * @var string[]
     */
    protected array $strictAttributes = ['file', 'name', 'class'];

    protected DOMDocument $document;

    protected DOMElement $root;

    /**
     * @var DOMElement[]
     */
    protected array $testSuites = [];

    /**
     * @var int[]
     */
    protected array $testSuiteTests = [0];

    /**
     * @var int[]
     */
    protected array $testSuiteAssertions = [0];

    /**
     * @var int[]
     */
    protected array $testSuiteErrors = [0];

    /**
     * @var int[]
     */
    protected array $testSuiteFailures = [0];

    /**
     * @var int[]
     */
    protected array $testSuiteSkipped = [0];

    /**
     * @var int[]
     */
    protected array $testSuiteUseless = [0];

    /**
     * @var int[]
     */
    protected array $testSuiteTimes = [0];

    protected int $testSuiteLevel = 0;

    protected ?DOMElement $currentTestCase = null;

    private string $reportFile;

    public function __construct(array $options, private Output $output)
    {
        $this->reportFile = $options[$this->reportFileParam];
        if (!codecept_is_path_absolute($this->reportFile)) {
            $this->reportFile = codecept_output_dir($this->reportFile);
        }
        codecept_debug(sprintf("Printing %s report to %s", $this->reportName, $this->reportFile));

        $this->isStrict = $options['strict_xml'];

        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $this->root = $this->document->createElement('testsuites');
        $this->document->appendChild($this->root);
    }

    public function afterResult(PrintResultEvent $event): void
    {
        file_put_contents($this->reportFile, $this->document->saveXML());
        $this->output->message(
            "- <bold>%s</bold> report generated in <comment>file://%s</comment>",
            $this->reportName,
            $this->reportFile
        )->writeln();
    }

    public function beforeSuite(SuiteEvent $event): void
    {
        $suite = $event->getSuite();
        $testSuite = $this->document->createElement('testsuite');
        $testSuite->setAttribute('name', $suite->getName());

        if ($this->testSuiteLevel > 0) {
            $this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
        } else {
            $this->root->appendChild($testSuite);
        }

        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel]          = $testSuite;
        $this->testSuiteTests[$this->testSuiteLevel]      = 0;
        $this->testSuiteAssertions[$this->testSuiteLevel] = 0;
        $this->testSuiteErrors[$this->testSuiteLevel]     = 0;
        $this->testSuiteFailures[$this->testSuiteLevel]   = 0;
        $this->testSuiteSkipped[$this->testSuiteLevel]    = 0;
        $this->testSuiteUseless[$this->testSuiteLevel]    = 0;
        $this->testSuiteTimes[$this->testSuiteLevel]      = 0;
    }

    public function afterSuite(SuiteEvent $event): void
    {
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'tests',
            (string)$this->testSuiteTests[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'assertions',
            (string)$this->testSuiteAssertions[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'errors',
            (string)$this->testSuiteErrors[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'failures',
            (string)$this->testSuiteFailures[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'skipped',
            (string)$this->testSuiteSkipped[$this->testSuiteLevel]
        );

        if (!$this->isStrict) {
            $this->testSuites[$this->testSuiteLevel]->setAttribute(
                'useless',
                (string)$this->testSuiteUseless[$this->testSuiteLevel]
            );
        }

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'time',
            sprintf('%F', $this->testSuiteTimes[$this->testSuiteLevel])
        );

        if ($this->testSuiteLevel > 1) {
            $this->testSuiteTests[$this->testSuiteLevel - 1] += $this->testSuiteTests[$this->testSuiteLevel];
            $this->testSuiteAssertions[$this->testSuiteLevel - 1] += $this->testSuiteAssertions[$this->testSuiteLevel];
            $this->testSuiteErrors[$this->testSuiteLevel - 1] += $this->testSuiteErrors[$this->testSuiteLevel];
            $this->testSuiteFailures[$this->testSuiteLevel - 1] += $this->testSuiteFailures[$this->testSuiteLevel];
            $this->testSuiteSkipped[$this->testSuiteLevel - 1] += $this->testSuiteSkipped[$this->testSuiteLevel];
            $this->testSuiteUseless[$this->testSuiteLevel - 1] += $this->testSuiteUseless[$this->testSuiteLevel];
            $this->testSuiteTimes[$this->testSuiteLevel - 1] += $this->testSuiteTimes[$this->testSuiteLevel];
        }

        $this->testSuiteLevel--;
    }

    public function startTest(TestEvent $event): void
    {
        $test = $event->getTest();

        $this->currentTestCase = $this->document->createElement('testcase');

        foreach ($test->getReportFields() as $attr => $value) {
            if ($this->isStrict and !in_array($attr, $this->strictAttributes)) {
                continue;
            }
            $this->currentTestCase->setAttribute($attr, $value);
        }
    }

    public function endTest(TestEvent $event): void
    {
        $test = $event->getTest();
        $time = $event->getTime();

        $this->currentTestCase->setAttribute(
            'time',
            sprintf('%F', $time)
        );
        $numAssertions = $test->numberOfAssertionsPerformed();

        $this->testSuiteAssertions[$this->testSuiteLevel] += $numAssertions;

        $this->currentTestCase->setAttribute(
            'assertions',
            (string)$numAssertions
        );

        $testOutput = '';

        if ($test instanceof TestCaseWrapper) {
            $testCase = $test->getTestCase();
            if (!$testCase->hasExpectationOnOutput()) {
                $testOutput = $testCase->getActualOutputForAssertion();
            }
        }

        if ($testOutput !== '') {
            $systemOut = $this->document->createElement(
                'system-out',
                Xml::prepareString($testOutput)
            );

            $this->currentTestCase->appendChild($systemOut);
        }

        $this->testSuites[$this->testSuiteLevel]->appendChild(
            $this->currentTestCase
        );

        $this->testSuiteTests[$this->testSuiteLevel]++;
        $this->testSuiteTimes[$this->testSuiteLevel] += $time;
        $this->currentTestCase = null;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testError(FailEvent $event): void
    {
        $this->doAddFault($event->getTest(), $event->getFail(), 'error');
        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testWarning(FailEvent $event): void
    {
        $this->doAddFault($event->getTest(), $event->getFail(), 'warning');
        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testFailure(FailEvent $event): void
    {
        $this->doAddFault($event->getTest(), $event->getFail(), 'failure');
        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }

    public function testSkipped(FailEvent $event): void
    {
        if ($this->currentTestCase === null) {
            return;
        }

        $skipped = $this->document->createElement('skipped');
        $this->currentTestCase->appendChild($skipped);

        $this->testSuiteSkipped[$this->testSuiteLevel]++;
    }

    public function testUseless(FailEvent $event): void
    {
        if ($this->currentTestCase === null) {
            return;
        }

        $error = $this->document->createElement('error', 'Useless Test');
        $this->currentTestCase->appendChild($error);

        $this->testSuiteUseless[$this->testSuiteLevel]++;
    }

    /**
     * Method which generalizes addError() and addFailure()
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function doAddFault(Test $test, Throwable $t, string $type): void
    {
        if ($this->currentTestCase === null) {
            return;
        }

        if ($test instanceof TestCaseWrapper) {
            $buffer = str_replace(': ', '::test', $test->toString()) . "\n";
        } elseif ($test instanceof SelfDescribing) {
            $buffer = $test->toString() . "\n";
        } else {
            $buffer = '';
        }

        if (PHPUnitVersion::series() < 10) {
            $exceptionString = TestFailure::exceptionToString($t);
        } else {
            $exceptionString = ThrowableToStringMapper::map($t);
        }

        $buffer .= $exceptionString . "\n" . StackTraceFilter::getFilteredStacktrace($t);

        $fault = $this->document->createElement(
            $type,
            Xml::prepareString($buffer)
        );

        $fault->setAttribute('type', $t::class);

        $this->currentTestCase->appendChild($fault);
    }
}
