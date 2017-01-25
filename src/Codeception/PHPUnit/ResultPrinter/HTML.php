<?php
namespace Codeception\PHPUnit\ResultPrinter;

use Codeception\PHPUnit\ResultPrinter as CodeceptionResultPrinter;
use Codeception\Step;
use Codeception\Step\Meta;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
use Codeception\Util\PathResolver;

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
    protected function startClass($name)
    {
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $steps = [];
        $success = ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED);
        if ($success) {
            $this->successful++;
        }

        if ($test instanceof ScenarioDriven) {
            $steps = $test->getScenario()->getSteps();
        }
        $this->timeTaken += $time;

        switch ($this->testStatus) {
            case \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE:
                $scenarioStatus = 'scenarioFailed';
                break;
            case \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED:
                $scenarioStatus = 'scenarioSkipped';
                break;
            case \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE:
                $scenarioStatus = 'scenarioIncomplete';
                break;
            case \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR:
                $scenarioStatus = 'scenarioFailed';
                break;
            default:
                $scenarioStatus = 'scenarioSuccess';
        }

        $stepsBuffer = '';
        $subStepsBuffer = '';
        $subStepsRendered = [];

        foreach ($steps as $step) {
            if ($step->getMetaStep()) {
                $subStepsRendered[$step->getMetaStep()->getAction()][] = $this->renderStep($step);
            }
        }

        foreach ($steps as $step) {
            if ($step->getMetaStep()) {

                if (! empty($subStepsRendered[$step->getMetaStep()->getAction()])) {
                    $subStepsBuffer = implode('', $subStepsRendered[$step->getMetaStep()->getAction()]);
                    unset($subStepsRendered[$step->getMetaStep()->getAction()]);

                    $stepsBuffer .= $this->renderSubsteps($step->getMetaStep(), $subStepsBuffer);
                }

            } else {
                $stepsBuffer .= $this->renderStep($step);
            }
        }

        $scenarioTemplate = new \Text_Template(
            $this->templatePath . 'scenario.html'
        );

        $failures = '';
        $name = Descriptor::getTestSignature($test);
        if (isset($this->failures[$name])) {
            $failTemplate = new \Text_Template(
                $this->templatePath . 'fail.html'
            );
            foreach ($this->failures[$name] as $failure) {
                $failTemplate->setVar(['fail' => nl2br($failure)]);
                $failures .= $failTemplate->render() . PHP_EOL;
            }
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

        $testString = preg_replace('~^([\s\w\\\]+):\s~', '<span class="quiet">$1 &raquo;</span> ', ucfirst(Descriptor::getTestAsString($test)));

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

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $suiteTemplate = new \Text_Template(
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
    protected function endRun()
    {
        $scenarioHeaderTemplate = new \Text_Template(
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

        $scenariosTemplate = new \Text_Template(
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
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception $e
     * @param float $time
     */
    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->failures[Descriptor::getTestSignature($test)][] = $this->cleanMessage($e);
        parent::addError($test, $e, $time);
    }

    /**
     * A failure occurred.
     *
     * @param \PHPUnit_Framework_Test                 $test
     * @param \PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->failures[Descriptor::getTestSignature($test)][] = $this->cleanMessage($e);
        parent::addFailure($test, $e, $time);
    }


    /**
     * @param $step
     * @return string
     */
    protected function renderStep(Step $step)
    {
        $stepTemplate = new \Text_Template($this->templatePath . 'step.html');
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
        $metaTemplate = new \Text_Template($this->templatePath . 'substeps.html');
        $metaTemplate->setVar(['metaStep' => $metaStep, 'error' => $metaStep->hasFailed() ? 'failedStep' : '', 'steps' => $substepsBuffer, 'id' => uniqid()]);
        return $metaTemplate->render();
    }

    private function cleanMessage($exception)
    {
        $msg = $exception->getMessage();
        $msg = str_replace(['<info>','</info>','<bold>','</bold>'], ['','','',''], $msg);
        return htmlentities($msg);
    }
}
