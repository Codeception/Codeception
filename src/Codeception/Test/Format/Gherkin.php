<?php
namespace Codeception\Test\Format;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Metadata;
use Codeception\Test\Test;

class Gherkin extends Test implements ScenarioDriven
{
    /**
     * @var FeatureNode
     */
    protected $featureNode;

    public function __construct(FeatureNode $featureNode, ScenarioNode $scenarioNode)
    {
        $this->featureNode = $featureNode;
        $this->setMetadata(new Metadata());
        $this->getMetadata()->setName($featureNode->getTitle());
        $this->getMetadata()->setFilename($featureNode->getFile());

    }

    public function preload()
    {
    }


    public function getSignature()
    {
        // TODO: Implement getSignature() method.
    }

    public function test()
    {
        //
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