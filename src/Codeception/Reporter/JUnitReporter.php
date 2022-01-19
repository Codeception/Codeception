<?php

namespace Codeception\Reporter;

use Codeception\Configuration;
use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Codeception\Test\Interfaces\Reported;
use Codeception\Util\StackTraceFilter;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Util\Xml;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function method_exists;
use function sprintf;

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
        Events::TEST_WARNING       => 'testWarning',
        Events::RESULT_PRINT_AFTER => 'afterResult',
    ];

    protected bool $isStrict = false;

    protected array $strictAttributes = ['file', 'name', 'class'];

    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var DOMElement
     */
    protected $root;

    /**
     * @var DOMElement[]
     */
    protected $testSuites = [];

    /**
     * @var int[]
     */
    protected $testSuiteTests = [0];

    /**
     * @var int[]
     */
    protected $testSuiteAssertions = [0];

    /**
     * @var int[]
     */
    protected $testSuiteErrors = [0];

    /**
     * @var int[]
     */
    protected $testSuiteFailures = [0];

    /**
     * @var int[]
     */
    protected $testSuiteSkipped = [0];

    /**
     * @var int[]
     */
    protected $testSuiteTimes = [0];

    /**
     * @var int
     */
    protected $testSuiteLevel = 0;

    /**
     * @var DOMElement
     */
    protected $currentTestCase;

    private string $reportFile;

    public function __construct(string $reportFile)
    {
        $this->reportFile = $reportFile;

        $this->isStrict = Configuration::config()['settings']['strict_xml'];

        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $this->root = $this->document->createElement('testsuites');
        $this->document->appendChild($this->root);
    }

    public function afterResult(PrintResultEvent $event): void
    {
        file_put_contents($this->reportFile, $this->document->saveXML());
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
        $this->testSuiteTimes[$this->testSuiteLevel]      = 0;
    }

    public function afterSuite(SuiteEvent $event): void
    {
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'tests',
            (string) $this->testSuiteTests[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'assertions',
            (string) $this->testSuiteAssertions[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'errors',
            (string) $this->testSuiteErrors[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'failures',
            (string) $this->testSuiteFailures[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'skipped',
            (string) $this->testSuiteSkipped[$this->testSuiteLevel]
        );

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
            $this->testSuiteTimes[$this->testSuiteLevel - 1] += $this->testSuiteTimes[$this->testSuiteLevel];
        }

        $this->testSuiteLevel--;
    }

    public function startTest(TestEvent $event): void
    {
        $test = $event->getTest();

        if ($test instanceof Reported) {
            $this->currentTestCase = $this->document->createElement('testcase');

            foreach ($test->getReportFields() as $attr => $value) {
                if ($this->isStrict and !in_array($attr, $this->strictAttributes)) {
                    continue;
                }
                $this->currentTestCase->setAttribute($attr, $value);
            }
            return;
        }

        $usesDataprovider = false;

        if (method_exists($test, 'usesDataProvider')) {
            $usesDataprovider = $test->usesDataProvider();
        }

        $testCase = $this->document->createElement('testcase');
        $testCase->setAttribute('name', $test->getName());

        $class      = new ReflectionClass($test);
        $methodName = $test->getName(!$usesDataprovider);

        if ($class->hasMethod($methodName)) {
            $method = $class->getMethod($methodName);

            $testCase->setAttribute('class', $class->getName());
            $testCase->setAttribute('classname', \str_replace('\\', '.', $class->getName()));
            $testCase->setAttribute('file', $class->getFileName());
            $testCase->setAttribute('line', (string) $method->getStartLine());
        }

        $this->currentTestCase = $testCase;
    }

    public function endTest(TestEvent $event): void
    {
        $test = $event->getTest();
        $time = $event->getTime();

        $this->currentTestCase->setAttribute(
            'time',
            sprintf('%F', $time)
        );
        $numAssertions = 0;

        if (method_exists($test, 'getNumAssertions')) {
            $numAssertions = $test->getNumAssertions();
        } else if (method_exists($test, 'numberOfAssertionsPerformed')) {
            $numAssertions = $test->numberOfAssertionsPerformed();
        }

        $this->testSuiteAssertions[$this->testSuiteLevel] += $numAssertions;

        $this->currentTestCase->setAttribute(
            'assertions',
            (string)$numAssertions
        );

        $testOutput = '';

        if (method_exists($test, 'hasOutput') && method_exists($test, 'getActualOutput')) {
            $testOutput = $test->hasOutput() ? $test->getActualOutput() : '';
        }

        if (!empty($testOutput)) {
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

    /**
     * Method which generalizes addError() and addFailure()
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function doAddFault(Test $test, \Throwable $t, string $type): void
    {
        if ($this->currentTestCase === null) {
            return;
        }

        if ($test instanceof SelfDescribing) {
            $buffer = $test->toString() . "\n";
        } else {
            $buffer = '';
        }

        $buffer .= TestFailure::exceptionToString($t) . "\n" .
            StackTraceFilter::getFilteredStacktrace($t);

        $fault = $this->document->createElement(
            $type,
            Xml::prepareString($buffer)
        );

        if ($t instanceof ExceptionWrapper) {
            $fault->setAttribute('type', $t->getClassName());
        } else {
            $fault->setAttribute('type', \get_class($t));
        }

        $this->currentTestCase->appendChild($fault);
    }
}
