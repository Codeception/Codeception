<?php
namespace Codeception\Test\Format;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Codeception\Lib\Di;
use Codeception\Scenario;
use Codeception\Step\Comment;
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
        $this->steps = $steps;
        $this->setMetadata(new Metadata());
        $this->scenario = new Scenario($this);
        $this->getMetadata()->setName($featureNode->getTitle());
        $this->getMetadata()->setFeature($scenarioNode->getTitle());
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
        return codecept_relative_path($this->getFileName());
    }

    public function test()
    {
        $this->makeContexts();
        $description = explode("\n", $this->featureNode->getDescription());
        foreach ($description as $line) {
            $this->getScenario()->runStep(new Comment($line));
        }

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
        $meta = new Meta($step->getText(), []);
        $meta->setPrefix($step->getKeyword());
        $this->scenario->setMetaStep($meta); // enable metastep

        $stepText = $step->getText();
        $executed = false;
        foreach ($this->steps as $pattern => $context) {
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
            throw new \PHPUnit_Framework_IncompleteTestError("Step definition for `$stepText` not found in contexts");
        }
    }

    protected function makeContexts()
    {
        /** @var $di Di  **/
        $di = $this->getMetadata()->getService('di');
        $di->set($this->scenario);

        foreach ($this->steps as $pattern => $step) {
            $di->instantiate($step[0]);
            $this->steps[$pattern][0] = $di->get($step[0]);
        }
    }

    public function toString()
    {
        return $this->getFeature() . ' ' . $this->getFileName();
    }

    public function getFeature()
    {
        return $this->metadata->getFeature();
    }

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
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