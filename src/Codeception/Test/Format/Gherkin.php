<?php
namespace Codeception\Test\Format;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Codeception\Lib\Di;
use Codeception\Scenario;
use Codeception\Step\Meta;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Metadata;
use Codeception\Test\Test;

class Gherkin extends Test implements ScenarioDriven
{
    protected $steps = [];

    /**
     * @var FeatureNode
     */
    protected $featureNode;

    /**
     * @var ScenarioNode
     */
    protected $scenarioNode;

    /**
     * @var Scenario
     */
    protected $scenario;

    public function __construct(FeatureNode $featureNode, ScenarioNode $scenarioNode, $steps = [])
    {
        $this->featureNode = $featureNode;
        $this->scenarioNode = $scenarioNode;
        $this->scenario = new Scenario($this);
        $this->setMetadata(new Metadata());
        $this->getMetadata()->setName($featureNode->getTitle());
        $this->getMetadata()->setFilename($featureNode->getFile());

    }

    public function preload()
    {
        $this->getMetadata()->setGroups($this->featureNode->getTags());
        $this->getMetadata()->setGroups($this->scenarioNode->getTags());
        $this->scenario->setMetaStep(null);
    }


    public function getSignature()
    {
        return $this->scenarioNode->getTitle();
    }

    public function test()
    {
        $this->makeContexts();

        if ($background = $this->featureNode->getBackground()) {
            foreach ($background->getSteps() as $step) {
                $this->runStep($step);
            }
        }

        foreach ($this->scenarioNode->getSteps() as $step) {
            $this->runStep($step);
        }
    }

    protected function runStep(StepNode $step)
    {
        $type = $step->getKeywordType();
        if (!isset($this->steps[$type])) {
            return;
        }
        $this->scenario->setMetaStep(new Meta($step->getText(), [])); // enable metastep

        $stepText = $step->getText();
        $executed = false;
        foreach ($this->steps[$type] as $pattern => $context) {
            $matches = [];
            if (!preg_match($pattern, $stepText, $matches)) {
                continue;
            }
            array_shift($matches);
            call_user_func_array($context, $matches); // execute the step
            $executed = true;
        }
        $this->scenario->setMetaStep(null); // disable metastep
        if (!$executed) {
            throw new \PHPUnit_Framework_IncompleteTestError("Step definition for `$stepText` not found in contexts", $this);
        }
    }

    protected function makeContexts()
    {
        /** @var $di Di  **/
        $di = $this->getMetadata()->getService('di');
        $di->set($this->scenario);
        $className = '\\' . $this->getMetadata()->getCurrent('actor');
        $di->set(new $className($this->scenario));

        foreach ($this->steps as $stepType => $steps) {
            foreach ($steps as $pattern => $step) {
                if (empty($step)) {
                    continue;
                }
                $this->steps[$stepType][$pattern][0] = $di->get($step[0]);
            }
        }
    }

    public function toString()
    {
        // TODO: Implement toString() method.
    }

    public function getFeature()
    {
        // TODO: Implement getFeature() method.
    }

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        // TODO: Implement getScenario() method.
    }

    public function getScenarioText($format = 'text')
    {
        return file_get_contents($this->getFileName());
    }

    public function getRawBody()
    {
        // TODO: Implement getRawBody() method.
    }
}