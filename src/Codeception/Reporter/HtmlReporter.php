<?php

namespace Codeception\Reporter;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Console\Output;
use Codeception\Step;
use Codeception\Step\Meta;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Test;
use Codeception\TestInterface;
use Codeception\Util\PathResolver;
use SebastianBergmann\Template\Template;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HtmlReporter implements EventSubscriberInterface
{
    use StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_BEFORE       => 'beforeSuite',
        Events::RESULT_PRINT_AFTER => 'afterResult',
        Events::TEST_SUCCESS       => 'testSuccess',
        Events::TEST_FAIL          => 'testFailure',
        Events::TEST_ERROR         => 'testError',
        Events::TEST_INCOMPLETE    => 'testIncomplete',
        Events::TEST_SKIPPED       => 'testSkipped',
        Events::TEST_USELESS       => 'testUseless',
        Events::TEST_WARNING       => 'testWarning',
    ];

    protected int $id = 0;

    protected string $scenarios = '';

    protected string $templatePath;

    protected array $failures = [];

    private string $reportFile;

    private Timer $timer;

    public function __construct(array $options, private Output $output)
    {
        $this->reportFile = $options['html'];
        if (!codecept_is_path_absolute($this->reportFile)) {
            $this->reportFile = codecept_output_dir($this->reportFile);
        }
        codecept_debug(sprintf("Printing HTML report to %s", $this->reportFile));

        $this->templatePath = sprintf(
            '%s%stemplate%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $this->timer = new Timer();
        $this->timer->start();
    }

    public function beforeSuite(SuiteEvent $event): void
    {
        $suite = $event->getSuite();
        if (!$suite->getName()) {
            return;
        }

        $suiteTemplate = new Template(
            $this->templatePath . 'suite.html'
        );

        $suiteTemplate->setVar(['suite' => ucfirst($suite->getName())]);

        $this->scenarios .= $suiteTemplate->render();
    }

    public function testSuccess(TestEvent $event): void
    {
        $this->printTestResult($event->getTest(), $event->getTime(), 'scenarioSuccess');
    }

    public function testError(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), $event->getTime(), 'scenarioFailed');
    }

    public function testFailure(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), $event->getTime(), 'scenarioFailed');
    }

    public function testWarning(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), $event->getTime(), 'scenarioSuccess');
    }

    public function testSkipped(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), $event->getTime(), 'scenarioSkipped');
    }

    public function testIncomplete(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), $event->getTime(), 'scenarioIncomplete');
    }

    public function testUseless(FailEvent $event): void
    {
        $this->printTestResult($event->getTest(), $event->getTime(), 'scenarioUseless');
    }

    public function printTestResult(Test $test, float $time, string $scenarioStatus): void
    {
        $steps = [];

        if ($test instanceof ScenarioDriven) {
            $steps = $test->getScenario()->getSteps();
        }

        $stepsBuffer = '';
        $subStepsRendered = [];

        foreach ($steps as $step) {
            $metaStep = $step->getMetaStep();
            if ($metaStep) {
                $key                      = $this->getMetaStepKey($metaStep);
                $subStepsRendered[$key][] = $this->renderStep($step);
            }
        }

        foreach ($steps as $step) {
            $metaStep = $step->getMetaStep();
            if ($metaStep) {
                $key = $this->getMetaStepKey($metaStep);
                if (! empty($subStepsRendered[$key])) {
                    $subStepsBuffer = implode('', $subStepsRendered[$key]);
                    unset($subStepsRendered[$key]);
                    $stepsBuffer .= $this->renderSubsteps($step->getMetaStep(), $subStepsBuffer);
                }
            } else {
                $stepsBuffer .= $this->renderStep($step);
            }
        }

        $scenarioTemplate = new Template(
            $this->templatePath . 'scenario.html'
        );

        $failures = '';
        $name = Descriptor::getTestSignatureUnique($test);
        if (isset($this->failures[$name])) {
            $failTemplate = new Template(
                $this->templatePath . 'fail.html'
            );
            foreach ($this->failures[$name] as $failure) {
                $failTemplate->setVar(['fail' => nl2br($failure)]);
                $failures .= $failTemplate->render() . PHP_EOL;
            }
            $this->failures[$name] = [];
        }

        $png = '';
        $html = '';
        if ($test instanceof TestInterface) {
            $reports = $test->getMetadata()->getReports();
            if (isset($reports['png'])) {
                $localPath = PathResolver::getRelativeDir($reports['png'], codecept_output_dir());
                $png = "<tr><td class='error'><div class='screenshot'><img src='$localPath' alt='failure screenshot'></div></td></tr>";
            }
            if (isset($reports['html'])) {
                $localPath = PathResolver::getRelativeDir($reports['html'], codecept_output_dir());
                $html = "<tr><td class='error'>See <a href='$localPath' target='_blank'>HTML snapshot</a> of a failed page</td></tr>";
            }
        }

        $toggle = $stepsBuffer ? '<span class="toggle">+</span>' : '';

        $testString = htmlspecialchars(ucfirst(Descriptor::getTestAsString($test)), ENT_QUOTES | ENT_SUBSTITUTE);
        $testString = preg_replace('~^([\s\w\\\]+):\s~', '<span class="quiet">$1 &raquo;</span> ', $testString);

        $scenarioTemplate->setVar(
            [
                'id'             => ++$this->id,
                'name'           => $testString,
                'scenarioStatus' => $scenarioStatus,
                'steps'          => $stepsBuffer,
                'toggle'         => $toggle,
                'failure'        => $failures,
                'png'            => $png,
                'html'           => $html,
                'time'           => round($time, 2)
            ]
        );

        $this->scenarios .= $scenarioTemplate->render();
    }

    private function getMetaStepKey(Meta $metaStep): string
    {
        $key = '';
        $filePath = $metaStep->getFilePath();
        if ($filePath !== null) {
            $key = $filePath;
            $lineNumber = $metaStep->getLineNumber();
            if ($lineNumber !== null) {
                $key .= ':' . $lineNumber;
            }
        }
        return $key . $metaStep->getAction();
    }

    protected function renderStep(Step $step): string
    {
        $stepTemplate = new Template($this->templatePath . 'step.html');
        $stepTemplate->setVar(['action' => $step->getHtml(), 'error' => $step->hasFailed() ? 'failedStep' : '']);
        return $stepTemplate->render();
    }

    protected function renderSubsteps(Meta $metaStep, string $substepsBuffer): string
    {
        $metaTemplate = new Template($this->templatePath . 'substeps.html');
        $metaTemplate->setVar(['metaStep' => $metaStep->getHtml(), 'error' => $metaStep->hasFailed() ? 'failedStep' : '', 'steps' => $substepsBuffer, 'id' => uniqid()]);
        return $metaTemplate->render();
    }

    public function afterResult(PrintResultEvent $event): void
    {
        $timeTaken = $this->timer->stop()->asString();
        $result = $event->getResult();

        $scenarioHeaderTemplate = new Template(
            $this->templatePath . 'scenario_header.html'
        );

        $status = $result->wasSuccessfulIgnoringWarnings()
            ? '<span style="color: green">OK</span>'
            : '<span style="color: #e74c3c">FAILED</span>';

        $scenarioHeaderTemplate->setVar(
            [
                'name'   => 'Codeception Results',
                'status' => $status,
                'time'   => $timeTaken
            ]
        );

        $header = $scenarioHeaderTemplate->render();

        $scenariosTemplate = new Template(
            $this->templatePath . 'scenarios.html'
        );

        $scenariosTemplate->setVar(
            [
                'header'              => $header,
                'scenarios'           => $this->scenarios,
                'successfulScenarios' => $result->successfulCount(),
                'failedScenarios'     => $result->failureCount(),
                'skippedScenarios'    => $result->skippedCount(),
                'incompleteScenarios' => $result->incompleteCount(),
                'uselessScenarios'    => $result->uselessCount(),
            ]
        );

        file_put_contents($this->reportFile, $scenariosTemplate->render());
        $this->output->message(
            "- <bold>HTML</bold> report generated in <comment>file://%s</comment>",
            $this->reportFile
        )->writeln();
    }
}
