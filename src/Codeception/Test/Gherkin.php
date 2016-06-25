<?php
namespace Codeception\Test;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Codeception\Lib\Di;
use Codeception\Scenario;
use Codeception\Step\Comment;
use Codeception\Step\Meta;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\ScenarioDriven;

class Gherkin extends Test implements ScenarioDriven, Reported
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

    public function __construct(FeatureNode $featureNode, ScenarioInterface $scenarioNode, $steps = [])
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

        if ($background = $this->featureNode->getBackground()) {
            foreach ($background->getSteps() as $step) {
                $this->validateStep($step);
            }
        }

        foreach ($this->scenarioNode->getSteps() as $step) {
            $this->validateStep($step);
        }
        if ($this->getMetadata()->getIncomplete()) {
            $this->getMetadata()->setIncomplete($this->getMetadata()->getIncomplete() . "\nRun gherkin:snippets to define missing steps");
        }
    }
    
    public function getSignature()
    {
        return basename($this->getFileName(), '.feature') . ':' . $this->getFeature();
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

    protected function validateStep(StepNode $stepNode)
    {
        $stepText = $stepNode->getText();
        foreach ($this->steps as $pattern => $context) {
            if (!preg_match($pattern, $stepText)) {
                continue;
            }
            return;
        }
        $incomplete = $this->getMetadata()->getIncomplete();
        $this->getMetadata()->setIncomplete("$incomplete\nStep definition for `$stepText` not found in contexts");
    }

    protected function runStep(StepNode $stepNode)
    {
        $params = [];
        if ($stepNode->hasArguments()) {
            $args = $stepNode->getArguments();
            $table = $args[0];
            if ($table instanceof TableNode) {
                $params = [$table->getTableAsString()];
            }
        }
        $meta = new Meta($stepNode->getText(), $params);
        $meta->setPrefix($stepNode->getKeyword());
        $this->scenario->setMetaStep($meta); // enable metastep
        $stepText = $stepNode->getText();
        $this->getScenario()->comment(null); // make metastep to be printed even if no steps in it
        foreach ($this->steps as $pattern => $context) {
            $matches = [];
            if (!preg_match($pattern, $stepText, $matches)) {
                continue;
            }
            array_shift($matches);
            if ($stepNode->hasArguments()) {
                $matches = array_merge($matches, $stepNode->getArguments());
            }
            call_user_func_array($context, $matches); // execute the step
            break;
        }
        $this->scenario->setMetaStep(null); // disable metastep
    }

    protected function makeContexts()
    {
        /** @var $di Di  **/
        $di = $this->getMetadata()->getService('di');
        $di->set($this->getScenario());

        $actorClass = $this->getMetadata()->getCurrent('actor');
        if ($actorClass) {
            $di->set(new $actorClass($this->getScenario()));
        }

        foreach ($this->steps as $pattern => $step) {
            $di->instantiate($step[0]);
            $this->steps[$pattern][0] = $di->get($step[0]);
        }
    }

    public function toString()
    {
        return $this->featureNode->getTitle() . ': ' . $this->getFeature();
    }

    public function getFeature()
    {
        return $this->getMetadata()->getFeature();
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

    public function getSourceCode()
    {
    }

    /**
     * @return ScenarioNode
     */
    public function getScenarioNode()
    {
        return $this->scenarioNode;
    }

    /**
     * @return FeatureNode
     */
    public function getFeatureNode()
    {
        return $this->featureNode;
    }

    /**
     * Field values for XML/JSON/TAP reports
     *
     * @return array
     */
    public function getReportFields()
    {
        return [
            'file'    => $this->getFileName(),
            'name'    => $this->toString(),
            'feature' => $this->getFeature()
        ];
    }
}
