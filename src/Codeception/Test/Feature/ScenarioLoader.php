<?php
namespace Codeception\Test\Feature;

use Codeception\Lib\Parser;
use Codeception\Scenario;
use Codeception\Test\Metadata;

trait ScenarioLoader
{
    /**
     * @var Scenario
     */
    private $scenario;

    /**
     * @return Metadata
     */
    abstract public function getMetadata();

    protected function createScenario()
    {
        $this->scenario = new Scenario($this);
    }

    /**
     * @return Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    public function getFeature()
    {
        return $this->getScenario()->getFeature();
    }

    public function getScenarioText($format = 'text')
    {
        $code = $this->getSourceCode();
        $this->getParser()->parseFeature($code);
        $this->getParser()->parseSteps($code);
        if ($format == 'html') {
            return $this->getScenario()->getHtml();
        }
        return $this->getScenario()->getText();
    }

    /**
     * @return Parser
     */
    abstract protected function getParser();
    abstract public function getSourceCode();
}