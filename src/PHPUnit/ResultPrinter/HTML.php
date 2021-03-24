<?php
namespace Codeception\PHPUnit\ResultPrinter;

use Codeception\PHPUnit\Compatibility\PHPUnit9;
use Codeception\PHPUnit\ResultPrinter as CodeceptionResultPrinter;
use Codeception\Step;
use Codeception\Step\Meta;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
use Codeception\Util\PathResolver;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\BaseTestRunner;
use SebastianBergmann\Template\Template;

class HTML extends CodeceptionResultPrinter
{
    /**
     * @var boolean
     */
    protected $printsHTML = true;

    /**
     * @var integer
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $scenarios = '';

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @var int
     */
    protected $timeTaken = 0;

    protected $failures = [];

    /**
     * Constructor.
     *
     * @param  mixed $out
     * @throws InvalidArgumentException
     */
    public function __construct($out = null)
    {
        parent::__construct($out);

        $this->templatePath = sprintf(
            '%s%stemplate%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
    }

    /**
     * Handler for 'start class' event.
     *
     * @param  string $name
     */
    protected function startClass(string $name):void
    {
    }

    public function endTest(\PHPUnit\Framework\Test $test, float $time) : void
    {
        $steps = [];
        if (PHPUnit9::baseTestRunnerClassExists()) {
            $success = $this->testStatus == BaseTestRunner::STATUS_PASSED;
        } else {
            $success = $this->testStatus->isSuccess();
        }
        if ($success) {
            $this->successful++;
        }

        if ($test instanceof ScenarioDriven) {
            $steps = $test->getScenario()->getSteps();
        }
        $this->timeTaken += $time;

        if (PHPUnit9::baseTestRunnerClassExists()) {
            switch ($this->testStatus) {
                case BaseTestRunner::STATUS_ERROR:
                case BaseTestRunner::STATUS_FAILURE:
                    $scenarioStatus = 'scenarioFailed';
                    break;
                case BaseTestRunner::STATUS_SKIPPED:
                    $scenarioStatus = 'scenarioSkipped';
                    break;
                case BaseTestRunner::STATUS_INCOMPLETE:
                    $scenarioStatus = 'scenarioIncomplete';
                    break;
                default:
                    $scenarioStatus = 'scenarioSuccess';
            }
        } else {
            if ($this->testStatus->isSuccess()) {
                $scenarioStatus = 'scenarioSuccess';
            } else if ($this->testStatus->isFailure() || $this->testStatus->isError()) {
                $scenarioStatus = 'scenarioFailed';
            } else if ($this->testStatus->isSkipped()) {
                $scenarioStatus = 'scenarioSkipped';
            } else if ($this->testStatus->isIncomplete()) {
                $scenarioStatus = 'scenarioIncomplete';
            } else {
                $scenarioStatus = 'scenarioSuccess';
            }
        }

        $stepsBuffer = '';
        $subStepsRendered = [];

        foreach ($steps as $step) {
            if ($step->getMetaStep()) {
                $key = $step->getMetaStep()->getLine() . $step->getMetaStep()->getAction();
                $subStepsRendered[$key][] = $this->renderStep($step);
            }
        }

        foreach ($steps as $step) {
            if ($step->getMetaStep()) {
                $key = $step->getMetaStep()->getLine() . $step->getMetaStep()->getAction();
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

        $testString = htmlspecialchars(ucfirst(Descriptor::getTestAsString($test)));
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
                'html'            => $html,
                'time'           => round($time, 2)
            ]
        );

        $this->scenarios .= $scenarioTemplate->render();
    }

    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite) : void
    {
        $suiteTemplate = new Template(
            $this->templatePath . 'suite.html'
        );
        if (!$suite->getName()) {
            return;
        }

        $suiteTemplate->setVar(['suite' => ucfirst($suite->getName())]);

        $this->scenarios .= $suiteTemplate->render();
    }

    /**
     * Handler for 'end run' event.
     */
    protected function endRun():void
    {
        $scenarioHeaderTemplate = new Template(
            $this->templatePath . 'scenario_header.html'
        );

        $status = !$this->failed
            ? '<span style="color: green">OK</span>'
            : '<span style="color: #e74c3c">FAILED</span>';


        $scenarioHeaderTemplate->setVar(
            [
                'name'   => 'Codeception Results',
                'status' => $status,
                'time'   => round($this->timeTaken, 1)
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
                'successfulScenarios' => $this->successful,
                'failedScenarios'     => $this->failed,
                'skippedScenarios'    => $this->skipped,
                'incompleteScenarios' => $this->incomplete
            ]
        );

        $this->write($scenariosTemplate->render());
    }

    /**
     * An error occurred.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Exception $e
     * @param float $time
     */
    public function addError(\PHPUnit\Framework\Test $test, \Throwable $e, float $time) : void
    {
        $this->failures[Descriptor::getTestSignatureUnique($test)][] = $this->cleanMessage($e);
        parent::addError($test, $e, $time);
    }

    /**
     * A failure occurred.
     *
     * @param \PHPUnit\Framework\Test                 $test
     * @param \PHPUnit\Framework\AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, float $time) : void
    {
        $this->failures[Descriptor::getTestSignatureUnique($test)][] = $this->cleanMessage($e);
        parent::addFailure($test, $e, $time);
    }

    /**
     * Starts test
     *
     * @param \PHPUnit\Framework\Test $test
     */
    public function startTest(\PHPUnit\Framework\Test $test):void
    {
        $name = Descriptor::getTestSignatureUnique($test);
        if (isset($this->failures[$name])) {
            // test failed in before hook
            return;
        }

        // start test and mark initialize as passed
        parent::startTest($test);
    }


    /**
     * @param $step
     * @return string
     */
    protected function renderStep(Step $step)
    {
        $stepTemplate = new Template($this->templatePath . 'step.html');
        $stepTemplate->setVar(['action' => $step->getHtml(), 'error' => $step->hasFailed() ? 'failedStep' : '']);
        return $stepTemplate->render();
    }

    /**
     * @param $metaStep
     * @param $substepsBuffer
     * @return string
     */
    protected function renderSubsteps(Meta $metaStep, $substepsBuffer)
    {
        $metaTemplate = new Template($this->templatePath . 'substeps.html');
        $metaTemplate->setVar(['metaStep' => $metaStep->getHtml(), 'error' => $metaStep->hasFailed() ? 'failedStep' : '', 'steps' => $substepsBuffer, 'id' => uniqid()]);
        return $metaTemplate->render();
    }

    private function cleanMessage($exception)
    {
        $msg = $exception->getMessage();
        if ($exception instanceof \PHPUnit\Framework\ExpectationFailedException && $exception->getComparisonFailure()) {
            $msg .= $exception->getComparisonFailure()->getDiff();
        }
        $msg = str_replace(['<info>','</info>','<bold>','</bold>'], ['','','',''], $msg);
        return htmlentities($msg);
    }

    public function printResult(TestResult $result): void
    {
    }
}
