<?php
namespace Codeception\Test\Feature;

use Codeception\Scenario;

trait ScenarioLoader
{
    public function getScenarioText($format = 'text')
    {
        $code = $this->getRawBody();
        $this->getParser()->parseFeature($code);
        $this->getParser()->parseSteps($code);
        if ($format == 'html') {
            return $this->getScenario()->getHtml();
        }
        return $this->getScenario()->getText();
    }

    abstract protected function getParser();

    /**
     * @return Scenario
     */
    abstract public function getScenario();
    abstract public function getRawBody();
}