<?php
namespace Codeception\TestCase\Interfaces;

interface ScenarioDriven {

    public function getFeature();

    public function getScenario();

    public function getScenarioText();
}