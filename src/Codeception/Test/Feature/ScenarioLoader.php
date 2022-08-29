<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Lib\Parser;
use Codeception\Scenario;
use Codeception\Test\Metadata;

trait ScenarioLoader
{
    private Scenario $scenario;

    abstract public function getMetadata(): Metadata;

    protected function createScenario(): void
    {
        $this->scenario = new Scenario($this);
    }

    public function getScenario(): Scenario
    {
        return $this->scenario;
    }

    public function getFeature(): string
    {
        return $this->getScenario()->getFeature();
    }

    public function getScenarioText(string $format = 'text'): string
    {
        $code = $this->getSourceCode();
        $this->getParser()->parseFeature($code);
        $this->getParser()->parseSteps($code);
        if ($format == 'html') {
            return $this->getScenario()->getHtml();
        }
        return $this->getScenario()->getText();
    }

    abstract protected function getParser(): Parser;

    abstract public function getSourceCode(): string;
}
