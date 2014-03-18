<?php
namespace Codeception\TestCase\Shared;

trait ScenarioPrint
{
    public function getScenarioText($format = 'text')
    {
        $code = $this->getRawBody();
        $this->parser->parseFeature($code);
        $this->parser->parseSteps($code);
        if ($format == 'html') {
            return $this->scenario->getHtml();
        }
        return $this->scenario->getText();
    }

    abstract function getRawBody();

} 