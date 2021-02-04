<?php
namespace Codeception\Test\Interfaces;

use Codeception\Scenario;

interface ScenarioDriven
{
    public function getFeature();

    public function getScenario(): Scenario;

    public function getScenarioText(string $format = 'text');

    public function preload();

    public function getSourceCode();
}
