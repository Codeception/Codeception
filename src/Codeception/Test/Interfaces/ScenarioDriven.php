<?php

namespace Codeception\Test\Interfaces;

use Codeception\Scenario;

interface ScenarioDriven
{
    public function getFeature(): ?string;

    public function getScenario(): Scenario;

    public function getScenarioText(string $format = 'text'): string;

    public function preload(): void;

    public function getSourceCode(): string;
}
