<?php
namespace Codeception\TestCase\Interfaces;

interface ScenarioDriven
{
    public function getFeature();

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario();

    public function getScenarioText($format = 'text');

    public function preload();

    public function getRawBody();
}
